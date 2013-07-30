<?php
/*
** Sample CSS server file
*/

header("Content-Type: text/css");
$view=new View("css/main.css");
$view->display();
?>
