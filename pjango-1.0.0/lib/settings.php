<?php
/*
** settings.php
** 
** Settings for the site, using class __SITE, included from
** core/site.php.
** 
** All variables are prepended with __ globally, but without
** when accessing from get()
** 
** Reset variables by __SITE::set()
** 
** Author: Luo Qian
*/

require_once("core/site.php");

__SITE::addVars(array(
	"PATH"=>array(
		"HTTP"=>"/", //WEB ROOT
		"SYSTEM"=>"/", //SYSTEM ROOT
		),
	"DEBUG"=>true,
	"CLR"=>array( //color scheme variables
		),
	"VIEWS"=>array( //diretories to search for views, relative to PATH[SYSTEM]
		#"views/alpha", //production
		"views/beta", //development
		),
	));

//list in reverse order of application after rendering
__SITE::addMiddleware(array(
	"AutoTrimMiddleware",
	"CsrfMiddleware",
	));

$SQL_DATABASES=array(
	array(
		"host"=>"hostname",
		"user"=>"username",
		"passwd"=>"password",
		"db"=>"database_name",
		),
	);

__SITE::refactor();
__SITE::set();
session_start();
?>
