<?php
/*
** index.php
** 
** Default, built-in index.php for the site. Acts as a switchboard for views.
** 
** Author: Luo Qian
*/

require_once("lib/libinclude.php");

if(!preg_match(sprintf("/^%s([?].*)?$/",str_replace("/",'\/',"$__PATH[HTTP]")),$_SERVER["REQUEST_URI"])){
	$get="";
	foreach($_GET as $k=>$v)
		$get.=sprintf("&%s",urlencode($k),$v==""?"":sprintf("=%s",urlencode($v)));
	$uri=substr($_SERVER["REQUEST_URI"],strlen("$__PATH[HTTP]"));
	while(substr($uri,0,1)=="/")
		$uri=substr($uri,1);
	header(sprintf("Location:$__PATH[HTTP]?%s%s",substr($uri,0,strpos($uri,".")),$get));
	die();
}

$page="index";
if(count($_GET)>0){
	$keys=array_keys($_GET);
	if(strlen($keys[0])>0&&substr($keys[0],-1)=="?"){
		$page=substr($keys[0],0,-1);
		unset($_GET[$keys[0]]);
		unset($_REQUEST[$keys[0]]);
		while(substr($page,-1)=="/")
			$page=substr($page,0,-1);
	}
}

include_once("$__PATH[SYSTEM]views.php");
view($page);
?>
