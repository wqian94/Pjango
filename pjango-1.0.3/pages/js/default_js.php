<?php
/*
** default_js.php
** 
** Sample JavaScript server file
** 
** Author: Luo Qian
*/

header("Content-Type: application/javascript");
$view=new View("js/default.js");
$view->display();
?>
