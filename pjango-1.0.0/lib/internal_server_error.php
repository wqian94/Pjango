<?php
/*
** Contains the INTERNAL_SERVER_ERROR class and its child
** classes.
**
** INTERNAL_SERVER_ERROR classes kill the script upon
** construction, with a customized error message.
*/

class INTERNAL_SERVER_ERROR{
	function __construct($ERROR_MESSAGE=null){
		if($ERROR_MESSAGE==null)
			$ERROR_MESSAGE="Please contact the administrators of this website for more information.";
		echo str_replace("\r","",str_replace("\n","",str_replace("\t","",<<<HTML
<!DOCTYPE html>
<html>
<body style="background:#EEEE66;">
<div style="margin:10px;padding:15px;border:2px dashed #EE6666;">
<h1 style="margin-top:0;font-size:3em;">Internal Server Error</h1>
$ERROR_MESSAGE
</div>
</body>
</html>
HTML
		)));
		
		//now kill script
		exit(0);
	}
}

class TAG_ARGUMENT_ERROR{
	function __construct($component){
		new INTERNAL_SERVER_ERROR($component==null?null:sprintf("Tag '%s' called improperly: %s",$component->getName(),$component->getToken()->getContent()));
	}
}
?>
