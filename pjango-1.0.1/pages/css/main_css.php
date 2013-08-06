<?php
/*
** main_css.php
** 
** Sample CSS server file
** 
** Author: Luo Qian
*/

header("Content-Type: text/css");
$view=new View("css/main.css");
$view->display();
?>
