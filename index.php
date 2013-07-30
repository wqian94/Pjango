<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

function prtln($str){
	echo preg_replace('/[\t\r\n]*/mi',"",$str);
}

if(isset($_POST["configure"])&&$_POST["configure"]>=time()-1*60*60&&$_POST["configure"]<=time()){ //if configuration request began at most 1 hour ago
	$dirs=explode(" ",`ls pjango-* -d`);
	$dir=preg_replace('/[\t\r\n]*/mi',"",$dirs[count($dirs)-1]);
	$settings=file_get_contents("$dir/lib/settings.php");
	$settings=
		preg_replace('/"HTTP"[ ]*=>[^,]*,/mi',"\"HTTP\"=>\"".$_POST["path"]["http"]."\",",
		preg_replace('/"SYSTEM"[ ]*=>[^,]*,/mi',"\"SYSTEM\"=>\"".$_POST["path"]["system"]."\",",
		preg_replace('/"DEBUG"[ ]*=>[^,]*,/mi',"\"DEBUG\"=>".(isset($_POST["debug"])?"true":"false").",",
		preg_replace('/"DEBUG"[ ]*=>[^,]*,/mi',"\"DEBUG\"=>".(isset($_POST["debug"])?"true":"false").",",
			$settings
		,1),1),1),1);
	if($_POST["sqluser"]!=""){
		$settings=
			preg_replace('/\$SQL_DATABASES[^;]*\;/mi',<<<SQL_DATABASES
\$SQL_DATABASES=array(
	array(
		"host"=>"$_POST[sqlhost]",
		"user"=>"$_POST[sqluser]",
		"passwd"=>"$_POST[sqlpasswd]",
		"db"=>"$_POST[sqldb]",
		),
	);
SQL_DATABASES
			,$settings,1);
	}
	file_put_contents("$dir/lib/settings.php",$settings);
	
	$htaccess=file_get_contents("$dir/.htaccess");
	$htaccess=preg_replace('/\(WEBROOT\)/mi',"http://".$_SERVER["HTTP_HOST"].$_POST["path"]["http"],$htaccess);
	file_put_contents("$dir/.htaccess",$htaccess);
}
else{
	prtln(<<<HTML
<!DOCTYPE html>
<html>
<head>
<title>Pjango Configuration</title>
<style type="text/css">
html,body,div,h1,h2,h3,h4,h5,h6,form{
	border:0;
	margin:0;
	padding:0;
}
body{
	background:#DDDDDD;
}
#main{
	width:800px;
	min-height:600px;
	height:100%;
	margin:auto;
	box-shadow:0 0 15px white;
}
.center{
	text-align:center;
}
.objcenter{
	margin:auto;
}
</style>
</head>
<body>
<div id="main">
HTML
	);
	if(isset($_POST["configure"]))
		echo "<div class=\"center\" style=\"color:red;\">Sorry, your configuration request timed out.</div>";
	$path=array(str_ireplace("index.php","",$_SERVER["SCRIPT_FILENAME"]),str_ireplace("index.php","",$_SERVER["REQUEST_URI"]));
	$time=time();
	prtln(<<<HTML
<h1 class="center">Configure Pjango</h1>
<form action="" method="post">
<input type="hidden" name="configure" value="$time" style="display:none;" />
<div class="objcenter center">
You will have an hour to complete your Pjango configuration.
</div>
<hr />
<div class="objcenter center">
<h3>System Configuration</h3>
<div>System path: <input type="text" name="path[system]" value="$path[0]" /></div>
<div>URL path: <input type="text" name="path[http]" value="$path[1]" /></div>
<div><label for="debug"><input id="debug" type="checkbox" name="debug" checked="checked" /> Activate Debug</label></div>
</div>
<hr />
<div class="objcenter center">
<h3>SQL Configuration</h3>
(Leave username blank add no databases)
<div>SQL Host: <input type="text" name="sqlhost" value="localhost" /></div>
<div>SQL Username: <input type="text" name="sqluser" value="" /></div>
<div>SQL Password: <input type="password" name="sqlpasswd" value="" /></div>
<div>SQL Database: <input type="text" name="sqldb" value="" /></div>
</div>
<hr />
<div class="objcenter center">
<input type="submit" value="Configure!" />
</div>
</form>
</body>
</html>
HTML
	);
}
?>
