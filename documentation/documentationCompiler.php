<?php
/*
** documentationCompiler.php
** 
** This PHP script is designed to compile the documentation for
** the Pjango project. The choice to use PHP instead of C or a
** compiled language to write this is simple: execution of any
** programs in those languages may be forbidden by the server
** administrator. However, we are guaranteed that PHP works, as
** we are writing this for no other language. As such, the
** choice of language for this script is clear and obvious, and
** no attempts to change this should be made, except to include
** additional parameters for the documentation grammar.
** 
** In order to add to the capabilities for the current grammar
** used in the documentation, modify the HEREDOCs in the Doc
** class's writeTXT and writeHTM functions appropriately.
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
$numFiles=0;
$fileIndex=0;

class Doc{
	public $args,$file="";
	function __construct($arr){
		$this->args=$arr;
		if(isset($this->args["CLASS"])){ //switch-identifier to label class
			$this->args["TITLE"]="class ".$this->args["CLASS"];
			if(substr($this->args["CLASS"],0,min(strlen($this->args["CLASS"]),strlen("abstract ")))=="abstract ")
				$this->args["TITLE"]="abstract class ".substr($this->args["CLASS"],strlen("abstract "));
			else if(substr($this->args["CLASS"],0,min(strlen($this->args["CLASS"]),strlen("interface ")))=="interface ")
				$this->args["TITLE"]="interface ".substr($this->args["CLASS"],strlen("interface "));
			/*if(isset($this->args["EXTENDS"]))
				$this->args["TITLE"].=" extends ".$this->args["EXTENDS"];
			if(isset($this->args["IMPLEMENTS"]))
				$this->args["TITLE"].=" implements ".$this->args["IMPLEMENTS"];*/
			$this->args["CONTENT"]=
				(isset($this->args["EXTENDS"])?"<h2>extends ".$this->args["EXTENDS"]."</h2>\n \n":"").
				(isset($this->args["IMPLEMENTS"])?"<h2>implements ".$this->args["IMPLEMENTS"]."</h2>\n \n":"").
				$this->args["CONTENT"].
				str_replace("<var>","<span class=\"boldVariable\">",str_replace("</var>","</span>",
					(isset($this->args["STATIC_VARIABLES"])?"\n \n<h3>Static Variables</h3>\n \n".$this->args["STATIC_VARIABLES"]:"").
					(isset($this->args["VARIABLES"])?"\n \n<h3>Instance Variables</h3>\n \n".$this->args["VARIABLES"]:"").
					(isset($this->args["FUNCTIONS"])?"\n \n<h3>Functions</h3>\n \n".$this->args["FUNCTIONS"]:""
				)));
		}
	}
	function write($dir,$name,$tree){
		global $fileIndex,$numFiles;
		$fileIndex++;
		console((WEBROOT==ROOT?str_repeat(" ",6):str_repeat("&nbsp;",12)).sprintf("Compiling documentation page %d of %d.",$fileIndex,$numFiles).EOL);
		if(strlen($dir)&&substr($dir,-1)!="/")
			$dir="$dir/";
		$this->writeTXT($dir,$name,$tree);
		$this->writeHTM($dir,$name,$tree);
	}
	private function writeTXT($dir,$name,$tree){
		$args=$this->args;
		$titleBorder=str_repeat("=",strlen($args["TITLE"]));
		$content=$args["CONTENT"];
		$content=str_replace("<example>","\nExample:",$content);
		$content=preg_replace('/<h2>(((?!<\/h2>).)*)<\/h2>/mi',"\n$1\n",$content);
		$content=preg_replace('/<[^>]*>/',"", //weed out all tags; must all fit on one line, multiline tags are considered to be "escaped"
			preg_replace('/<[^>]*hr[^>]*>\n/mi',str_repeat("=",63)."\n",
			preg_replace('/<\/?code[^>]*>/mi','"',
				$content
			)));
		$content=str_replace("&vellip;","...",$content); //should actually be vertical, but...
		$content=str_replace("&lt;","<",$content);
		$content=str_replace("&gt;",">",$content);
		$content=str_replace("&amp;","&",$content);
		$nav=$this->generateNavigationTXT("",$tree,0,"$dir$name");
		$contents=<<<TXT
$args[TITLE]
$titleBorder

$content

==========
NAVIGATION
==========
$nav
TXT;
		if(file_put_contents("$dir$name.txt",$contents)===false)
			console(EOL);
		system("chmod 777 -R $dir$name.txt 2> /dev/null");
	}
	private function generateNavigationTXT($dir,$tree,$level=0,$path=null,$latest=null){
		if($tree instanceof Doc){
			$index=$tree;
			$isdir=false;
		}
		else{
			$index=$tree["index"];
			unset($tree["index"]);
			$isdir=true;
		}
		if(strlen($dir)&&substr($dir,-1)!="/"&&$isdir)
			$dir="$dir/";
		$nav="";
		if($path!==null&&($isdir&&$path=="index"||!$isdir&&($tree instanceof Doc)&&$latest==$path))
			$nav.=str_repeat("=",$level*2).">";
		else
			$nav.=str_repeat("-",$level*2)."-";
		$nav.="[".$index->args["SHORT"]."] ".$index->args["TITLE"]." (".$index->file.")\n";
		$nav=wordwrap($nav,64,"\n".str_repeat(" ",$level*2+3));
		if($tree instanceof Doc)
			$tree=array();
		if(count($tree)){
			uasort($tree,"Doc::sortingFunction");
			foreach($tree as $k=>$obj)
				$nav.=$this->generateNavigationTXT("$dir$k",$obj,$level+1,($path===null||strpos($path,"/")===false&&$path=="index"||substr($path,0,strlen($k))!=$k?null:substr($path,strpos($path,"/")===false?0:(strpos($path,"/")+1))),$path);
		}
		return $nav;
	}
	
	private function writeHTM($dir,$name,$tree){
		$args=$this->args;
		$dirs=explode("/",$dir);
		if($dirs[count($dirs)-1]=="")
			$dirs=array_slice($dirs,0,-1);
		$title_tree=$tree;
		$title=$title_tree["index"]->args["TITLE"];
		for($i=0;$i<count($dirs);$i++){
			$title_tree=$title_tree[$dirs[$i]];
			$title.=" > ".$title_tree["index"]->args["TITLE"];
		}
		if($name!="index")
			$title.=" > ".$title_tree[$name]->args["TITLE"];
		$nav=$this->generateNavigationHTM("",$tree,"$dir$name",null,str_repeat("../",count($dirs)));
		$csslink=WEBROOT.($GLOBALS["loc"]!="."?$GLOBALS["loc"]:"").(strlen($GLOBALS["loc"])&&$GLOBALS["loc"]!="."&&substr($GLOBALS["loc"],-1)!="/"?"/":"")."style.css";
		$content=str_replace("\n\n","<br />\n<br />\n","$args[CONTENT]");
		$content=str_replace("<example>","<div class=\"example\"><h4 class=\"struct\">Example:</h4>",str_replace("</example>","</div>",$content));
		$content=str_replace("<var>","<span class=\"variable\">",str_replace("</var>","</span>",$content));
		$content=preg_replace('/<iftxt>[^<]*<\/iftxt>/mi',"",$content);
		preg_match_all('/<spaces>(((?!<\/spaces>).)*)<\/spaces>/mi',$content,$matches);
		foreach($matches[1] as $m)
			$content=str_ireplace("<spaces>$m</spaces>",str_replace(" ","&nbsp;",$m),$content);
		$contents=<<<HTML
<!DOCTYPE html>
<html>
<head>
<title>$title</title>
<link rel="stylesheet" type="text/css" href="$csslink" />
</head>
<body>
<div style="overflow:hidden;width:0;height:0;">&nbsp;</div>
<div class="nav" style="position:fixed;z-index:100;width:251px;height:100%;overflow:auto;border-right:5px ridge black;">
<div class="desc" style="padding:1em 0 0 1em;">
$nav
</div>
</div>
<div style="margin-left:256px;padding-left:5px;padding-right:1em;overflow:auto;">
<div>
<h1>$args[TITLE]</h1>
<hr />
$content
</div>
</div>
</body>
</html>
HTML;
		if(file_put_contents("$dir$name.htm",preg_replace('/[ ]+/mi',' ',preg_replace('/[\n\r\t]/mi',' ',$contents)))===false)
			console(EOL);
		system("chmod 777 -R $dir$name.htm 2> /dev/null");
	}
	private function generateNavigationHTM($dir,$tree,$path=null,$latest=null,$root=""){
		if($tree instanceof Doc){
			$index=$tree;
			$isdir=false;
		}
		else{
			$index=$tree["index"];
			unset($tree["index"]);
			$isdir=true;
		}
		if(strlen($dir)&&substr($dir,-1)!="/"&&$isdir)
			$dir="$dir/";
		$nav="<a href=\"".$root.$dir.($isdir?"index":"").".htm\" class=\"nav".($path!==null&&($isdir&&$path=="index"||!$isdir&&($tree instanceof Doc)&&$latest==$path)?" highlight\"":"")."\" title=\"".$index->args["TITLE"]."\">".$index->args["SHORT"]."</a>";
		if($tree instanceof Doc){
			if(isset($tree->args["ANCHORS"])&&($path!==null&&($isdir&&$path=="index"||!$isdir&&($tree instanceof Doc)&&$latest==$path))){
				$anchors=explode("\n",$tree->args["ANCHORS"]);
				$nav.="<ul style=\"list-style-type:disc;\">";
				for($i=0;$i+1<count($anchors);$i+=2)
					$nav.=sprintf("<li><a href=\"%s%s%s.htm#%s\" class=\"nav\" title=\"%s\">%s</a></li>",$root,$dir,$isdir?"index":"",$anchors[$i+1],$anchors[$i],$anchors[$i]);
				$nav.="</ul>";
			}
			$tree=array();
		}
		if(count($tree)&&$path!==null){
			uasort($tree,"Doc::sortingFunction");
			$nav.="<ul>";
			foreach($tree as $k=>$obj)
				$nav.="<li>".$this->generateNavigationHTM("$dir$k",$obj,($path===null||strpos($path,"/")===false&&$path=="index"||substr($path,0,strlen($k))!=$k?null:substr($path,strpos($path,"/")===false?0:(strpos($path,"/")+1))),$path,$root)."</li>";
			$nav.="</ul>";
		}
		return $nav;
	}
	
	private static function sortingFunction($a,$b){
		//files before directories
		if(($a instanceof Doc)!=($b instanceof Doc))
			return ($a instanceof Doc)?-1:1;
		
		//for files
		if($a instanceof Doc){
			$title_a=isset($a->args["SHORT"])?$a->args["SHORT"]:$a->args["TITLE"];
			$title_b=isset($b->args["SHORT"])?$b->args["SHORT"]:$b->args["TITLE"];
			return $title_a==$title_b?($a->args["TITLE"]==$b->args["TITLE"]?0:($a->args["TITLE"]<$b->args["TITLE"]?-1:1)):($title_a<$title_b?-1:1);
		}
		
		//for directories
		else
			return $a["index"]->args["SHORT"]==$b["index"]->args["SHORT"]?0:($a["index"]->args["SHORT"]<$b["index"]->args["SHORT"]?-1:1);
	}
}

function prepareDocumentation($loc,&$tree){
	preg_match('/^(([^\/]*\/)*)(.*)$/mi',substr($loc,strlen(ROOT)),$paths);
	$dir=$paths[1];
	$file=$paths[3];
	if($file=="."){
		$file="";
		$loc=substr($loc,0,-1);
	}
	if(is_file($loc)){
		$tree=parseFile($loc);
		preg_match('/(\/|^)([^\/]*)\/$/mi',$dir,$dirname);
		if(count($dirname)>1)
			$dirname=$dirname[2];
		else
			$dirname="";
		$tree->file=substr($file,1,-5)=="index"?"$dirname/":substr($file,1,-5);
	}
	if(is_dir($loc)){
		$dir.="$file";
		if(strlen($dir)&&substr($dir,-1)!="/")
			$dir="$dir/";
		$file="";
		foreach(scandir(ROOT.$dir) as $f)
			if($f!="."&&$f!=".."&&(is_dir(ROOT."$dir$f")||is_file(ROOT."$dir$f")&&preg_match('/^\.(.+)\.pdml$/mi',$f,$name))){
				if(is_file(ROOT."$dir$f")){
					$name=$name[1];
					$tree[$name]=null;
				}
				else{
					$name=$f;
					$tree[$name]=array();
				}
				prepareDocumentation(ROOT."$dir$f",$tree[$name]);
			}
	}
}
function parseFile($loc){
	$lines=explode("\n",file_get_contents($loc));
	$mode=null;
	$doc=array();
	foreach($lines as $ln){
		if($mode==null){
			if(preg_match('/^<<<([\w]+)$/mi',$ln,$name)){
				$name=$name[1];
				$mode=$name;
				$doc[$name]="";
			}
		}
		else{
			if(preg_match('/^([\w]+)>>>$/mi',$ln,$name))
				$name=$name[1];
				if($name==$mode){
					$mode=null;
					continue;
				}
			$doc[$mode].=(strlen($doc[$mode])?"\n":"").$ln;
		}
	}
	$GLOBALS["numFiles"]++;
	return new Doc($doc);
}

function generateDocumentation($loc,$tree,$subtree=array()){
	preg_match('/^(([^\/]*\/)*)(.*)$/mi',substr($loc,strlen(ROOT)),$paths);
	$dir=$paths[1];
	$file=$paths[3];
	if($file=="."){
		$file="";
		$loc=substr($loc,0,-1);
	}
	if(strlen($dir)&&substr($dir,-1)!="/")
		$dir="$dir/";
	$dirs=strlen($dir)?array_slice(explode("/",$dir),0,-1):array();
	if($tree instanceof Doc){
		console((WEBROOT==ROOT?str_repeat(" ",4):str_repeat("&nbsp;",8))."Generating documentation for ".substr($loc,strlen(ROOT)).EOL);
		$tree->write($dir,$file,$subtree);
	}
	else{
		console((WEBROOT==ROOT?str_repeat(" ",2):str_repeat("&nbsp;",4)).sprintf("Entering subtree \"%s\"",substr($loc,strlen(ROOT))).EOL);
		if(strlen($file)&&substr($file,-1)!="/")
			$file="$file/";
		if(strlen($file)){
			$dirs[]=substr($file,0,-1);
		}
		$sub=&$subtree;
		foreach($dirs as $d)
			$sub=&$sub[$d];
		foreach($tree as $k=>$obj)
			$sub[$k]=$obj instanceof Doc?$obj:array("index"=>$obj["index"]);
		foreach($tree as $k=>$obj)
			generateDocumentation(ROOT."$dir$file$k",$obj,$subtree);
	}
}

function generateAuxilliaryFiles($loc){
	if(strlen($loc)&&substr($loc,-1)!="/")
		$loc="$loc/";
	console((WEBROOT==ROOT?str_repeat(" ",2):str_repeat("&nbsp;",4))."Generating Cascading Style Sheets".EOL);
	console((WEBROOT==ROOT?str_repeat(" ",4):str_repeat("&nbsp;",8))."Generating style.css".EOL);
	file_put_contents($loc."style.css",
		preg_replace('/[ ]+/mi'," ",
		preg_replace('/[\t\r\n]/mi',"",
			<<<CSS
html,body,div{
	border:0;
	margin:0;
	padding:0;
}
html,body{
	font-family:serif;
}
ul{
	margin:0;
	padding-left:1em;
}
h1,h2,h3,h4,h5,h6{
	margin:.5em 0;
	padding:0;
}
a{
	color:blue;
	text-decoration:none;
}
a:hover{
	text-decoration:underline;
}
span.variable,div.variable{
	font-family:monospace;
}
span.boldVariable,div.boldVariable{
	font-family:monospace;
	font-weight:bold;
}
code{
	white-space:nowrap;
}
code.block{
	display:block;
}
code.nowrap,span.nowrap{
	white-space:nowrap;
}
code.wrap,span.wrap{
	white-space:normal;
}
code.bordered{
	border:1px dotted black;
}
div.example{
	margin:1em 0;
	padding:5px;
	border:1px dashed green;
	border-radius:1px;
	background:#66FF66;
}
div.nav{
	background:#9999CC;
}
a.nav{
	text-shadow:0 0 2px white;
}
div.nav,a.nav{
	color:#006622;
}
.bold{
	font-weight:bold;
}
.iblock{
	display:inline-block;
}
.struct{
	margin:0;
	padding:0;
	border:0;
}
.nodisplay{
	display:none;
}
.superscript{
	vertical-align:super;
}
.highlight{
	background:yellow;
}
CSS
		)));
	@chmod($loc."style.css",0777);
}

function console($str){
	if(isset($_SERVER["SERVER_NAME"])){
		printf("%s\r\n",$str);
		ob_flush();
		flush();
	}
	else
		echo $str;
}

$tree=array();
console("Documentation compile request received at ".date("M d, Y H:i:s").EOL);
console("Preparing documentation...".EOL);
prepareDocumentation(ROOT.$loc,$tree);
console("Preparation complete.".EOL);
console(sprintf("Documentation page count: %d.",$numFiles).EOL);
console("Generating documentation...".EOL);
generateDocumentation(ROOT.$loc,$tree);
console("Generating auxilliary files...".EOL);
generateAuxilliaryFiles(ROOT.$loc);
console("Generation complete.".EOL);
console("Documentation is ready!".EOL);
if(isset($_SERVER["SERVER_NAME"])) //to end the chunked transfer encoding
	console("");
