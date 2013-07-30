<?php
/*
** Includes library files.
** 
** Using rules found in each subdirectory's INCLUDE file,
** selectively includes PHP library files.
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');
//import error reporting script
require_once("internal_server_error.php");

//import project settings
require_once("settings.php"); //include settings and __SITE object
require_once("core/core.php"); //include core objects

//generate starting queue
$queue=array();
foreach(scandir("$__PATH[SYSTEM]lib/") as $dir){
	$inc_dir="$__PATH[SYSTEM]lib/$dir";
	if(!in_array($dir,array(".",".."))&&is_dir($inc_dir)) //only includes subdirectories, not files located in the library root
		array_push($queue,$inc_dir);
}

//parse through header files
$headers=array();
$missingFiles=array();
while(count($queue)){
	$item=array_shift($queue);
	
	//weed out . and .. directories, just in case
	if(in_array($item,array(".","..")))
		continue;
	
	//set up PHP files for inclusion or error reporting
	else if(!is_dir($item)&&strlen($item)>3&&substr($item,-3)=="php"){
		if(!file_exists($item))
			array_push($missingFiles,$item);
		else
			array_push($headers,$item);
	}
	
	//look for INCLUDE files
	else if(is_dir($item)&&file_exists("$item/INCLUDE")){
		$f=fopen("$item/INCLUDE","r");
		while(!feof($f)){ //one entry per line
			$fname=str_replace("\r","",str_replace("\n","",fgets($f)));
			if($fname=="*"){ //keyword for including everything that can be found
				foreach(scandir("$item") as $dir)
					if(!in_array($dir,array(".","..")))
						array_push($queue,"$item/$dir");
			}
			else if($fname!="")
				array_push($queue,"$item/$fname");
		}
		fclose($f);
	}
}

//check for missing header files
if(count($missingFiles)>0){
	new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("
		<h2>The following header file%s cannot be found:</h2>
		%s
		<div style=\"margin-top:1em;font-weight:bold;\">You are seeing this detailed error report because <span style=\"font-family:monospace;font-weight:normal;\">\$DEBUG=true</span>. Set <span style=\"font-family:monospace;font-weight:normal;\">\$DEBUG=false</span> to remove detail.</div>
		",count($missingFiles)==1?"":"s",join("<br />",$missingFiles))
		:null);
}

//all-clear -- include the headers
foreach($headers as $item)
	include_once($item);

//configure databases
foreach($SQL_DATABASES as $sql){SQL::connect($sql["host"],$sql["user"],$sql["passwd"],$sql["db"]);}
unset($SQL_DATABASES,$sql);

//configure site
__SITE::preprocess();
?>
