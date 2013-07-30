<?php
/*
** Sample JavaScript server file
*/

header("Content-Type: application/javascript");
$view=new View("js/default.js");
$view->display();
?>
