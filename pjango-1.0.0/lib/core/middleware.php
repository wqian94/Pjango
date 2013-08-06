<?php
/*
** middleware.php
** 
** Default middleware
** 
** Author: Luo Qian
*/

class AutoTrimMiddleware extends AbstractMiddleware{
	public static function render($text){
		return preg_replace('/[\r\n\t]/mi','',$text);
	}
}

class CsrfMiddleware extends AbstractMiddleware{
	public static function preprocess(){
		global $__DEBUG;
		if(!empty($_POST)||!empty($_FILES)){
			$pcsrf=sha1("csrftoken");
			$key=null;
			foreach($_POST as $k=>$v)
				if(strlen($k)==strlen($pcsrf)+15){
					$key=substr($k,-15);
					$pcsrf=$k;
					break;
				}
			$scsrf=md5("csrftoken$key");
			if(!isset($_SESSION[$scsrf])||!isset($_POST[$pcsrf])||$_SESSION[$scsrf]!=$_POST[$pcsrf]) //csrf mismatch
				new INTERNAL_SERVER_ERROR($__DEBUG?"CSRF token nonexistent or invalid.":null);
		}
	}
}
?>
