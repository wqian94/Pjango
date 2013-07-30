<?php
/*
** documentationCleaner.php
** 
** This PHP script is designed to clean the documentation for
** the Pjango project. The choice to use PHP instead of C or a
** compiled language to write this is simple: execution of any
** programs in those languages may be forbidden by the server
** administrator. However, we are guaranteed that PHP works, as
** we are writing this for no other language. As such, the
** choice of language for this script is clear and obvious, and
** no attempts to change this should be made, except to include
** additional parameters for the documentation grammar.
** 
** This cleaning script simply removes all .htm and .txt files
** from the location provided.
*/

define("ROOT",getcwd()."/");
if(isset($_SERVER["SERVER_NAME"])){
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	define("WEBROOT",substr($_SERVER["SCRIPT_NAME"],0,-strlen("documentationCompiler.php")));
	define("EOL","<br />");
	header("Content-Encoding","chunked");
	header("Transfer-Encoding","chunked");
	header("Content-Type","text/html");
	header("Connection","keep-alive");
	ob_flush();
	flush();
	echo str_repeat(" ",1024);
	ob_flush();
	flush();
	$loc=".";
}
else{
	define("WEBROOT",ROOT);
	define("EOL","\n");
	$loc=$argv[1];
}

if(!preg_match('/\/$/',$loc))
	$loc.="/";
if($loc=="./")
	$loc="";

function console($str){
	if(isset($_SERVER["SERVER_NAME"])){
		printf("%s\r\n",$str);
		ob_flush();
		flush();
	}
	else
		echo $str;
}

$rel="$loc";
$numFiles=0;
do{
	$files=explode("\n",trim(str_replace("\r","",`ls $rel*.htm $rel*.txt $rel*.css 2> /dev/null`)));
	foreach(array_reverse($files) as $f){
		if($f=="")
			continue;
		$numFiles++;
		console(sprintf("Discovered file %d: $f.",$numFiles).EOL);
		`rm $f`;
	}
	$rel.="*/";
}while(`ls $rel 2> /dev/null`!="");
console(sprintf("Discovered and deleted %d files.",$numFiles).EOL);
