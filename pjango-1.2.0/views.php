<?php
/*
** views.php
** 
** Used for handling views. Do not change the signature of this function!
** 
** Author: Luo Qian
*/

function view($page){
	//DO NOT EDIT: line of code below imports all __SITE variables into function
	foreach(__SITE::get() as $var=>$val)$$var=$val;unset($var,$val);
	
	//edit view function below
	if(!(@include("pages/$page.php"))){
		$default_view=new View("$page.html");
		$default_view->display();
	}
}
?>
