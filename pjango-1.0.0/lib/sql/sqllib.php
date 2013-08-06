<?php
/*
** sqllib.php
** 
** Built-in SQL library
** 
** Simplifies basic SQL operations by providing a simplified
** API for use. Selects between mysql_* and mysqli_* functions
** based on PHP version.
** 
** Author: Luo Qian
*/

class SQL{
	private static $handles=array();
	private static $latest=null;
	private static $escape=true;
	private static $result=null;
	
	public static function connect($host,$user,$passwd,$db){
		if(PHP_VERSION<"5.0.0"){
			$h=mysql_connect($host,$user,$passwd);
			mysql_select_db($db,$h);
			array_push(self::$handles,$h);
		}
		else{
			array_push(self::$handles,mysqli_connect($host,$user,$passwd,$db));
		}
		self::$latest=count(self::$handles)-1;
		return self::$handles[self::$latest];
	}
	
	public static function select($id){
		global $__DEBUG;
		if($id<count(self::$handles)||$id>=count(self::$handles))
			new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("SQL handle identifier out of bounds: %d. Only %d handles registered.",$id,count(self::$handles)):null);
		if($id<0)
			$id=count(self::$handles)-$id;
		self::$latest=$id;
	}
	
	public static function index(){
		return self::$latest;
	}
	
	public static function handle(){
		return self::$handles[self::$latest];
	}
	
	public static function escape($escape=true){
		self::$escape=$escape;
	}
	
	public static function query($query,$args=array(),$escape=null){
		global $__DEBUG;
		if($escape==null)
			$escape=self::$escape;
		if(self::$latest==null)
			new INTERNAL_SERVER_ERROR($__DEBUG?"No SQL handles registered!":null);
		$handle=self::$handles[self::$latest];
		if($escape){
			foreach($args as $k=>$v)
				$args[$k]=PHP_VERSION<"5.0.0"?mysql_real_escape_string($v):mysqli_real_escape_string($v);
		}
		if(PHP_VERSION<"5.0.0"){
			return self::$result=mysql_query(count($args)?vsprintf($query,$args):$query,$handle);
		}
		else{
			return self::$result=mysqli_query($handle,count($args)?vsprintf($query,$args):$query);
		}
	}
	
	public static function result(){
		return self::$result;
	}
	
	public static function fetch($sqlresult=null){
		global $__DEBUG;
		$result=$sqlresult==null?self::$result:$sqresult;
		if($result==null)
			return INTERNAL_SERVER_ERROR($__DEBUG?"SQL fetch failed: no query to fetch from.":null);
		if(PHP_VERSION<"5.0.0"){
			return mysql_fetch_array($result);
		}
		else{
			return mysqli_fetch_array($result);
		}
	}
}
?>
