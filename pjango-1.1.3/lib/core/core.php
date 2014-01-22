<?php
/*
** core.php
** 
** Core library for loading views.
** 
** Contains classes View, Token, AbstractComponent and its
** child classes, as well as abstract classes for tags,
** filters, and middleware.
** 
** Author: Luo Qian
*/

__SITE::set();

class View{
	private $text=null;
	private $root=null;
	function __construct($input=null,$isFile=true){ //loads as file by default
		if($input!=null){
			if($isFile)
				$this->load_file($input);
			else
				$this->load_text($input);
		}
	}
	function getText(){
		return $this->text;
	}
	function load($input){ //intelligently looks for file before loading as text
		global $__VIEWS;
		$fname=null;
		foreach($__VIEWS as $v){
			if(strlen($v)<1)
				continue;
			if(substr($v,-1)!="/")
				$v="$v/";
			if(file_exists("$v$file"))
				$fname="$v$file";
		}
		
		if($fname!=null)
			$this->load_file($input);
		else
			$this->load_text($input);
	}
	function load_text($text){
		$this->text=$text;
		$this->root=null;
	}
	function load_file($file){
		global $__VIEWS;
		$fname=null;
		foreach($__VIEWS as $v){
			if(strlen($v)<1)
				continue;
			if(substr($v,-1)!="/")
				$v="$v/";
			if(file_exists("$v$file"))
				$fname="$v$file";
		}
		if($fname==null) //view file not found in any view directories
			new INTERNAL_SERVER_ERROR(sprintf("
				No view named \"%s\" was found.
				<br /><br />
				Searched through subdirectories:
				<br /><br />
				<div style=\"font-family:monospace;\">%s</div>",
				$file,
				join("<br />",$__VIEWS)));
		else
			$this->text=file_get_contents($fname);
		$ths->root=null;
	}
	function createComponents(){
		global $__DEBUG;
		
		if($this->root!=null)
			return $this->root;
		
		$contents=$this->text;
		preg_match_all('/({%((?!%})(.|\n))*%}|({{((?!}})(.|\n))*}})|{#((?!#})(.|\n))*#}|(((?!{(%|{|#))(.|\n))*))/mi',$contents,$matches);
		$matches=$matches[0]; //matches to the whole regex
		
		//process matched tokens
		$token_queue=array();
		foreach($matches as $tok)
			array_push($token_queue,new Token($tok));
		
		//process generated tokens
		$root=new RootComponent();
		$component_stack=array($root);
		$stack_exclude=__SITE::getUnclosedTagNames(); //tags that can be excluded from requiring a closing tag
		$stack_smear=__SITE::getUnclosedContentTagNames(); //tags that have no "closing tag" but still have content, e.g. are within a block
		$verbatims=0;
		foreach($token_queue as $tok){
			$next=null;
			switch($tok->getType()){
				case Token::TAG:
					$next=new TagComponent($tok);
					break;
				case Token::VAL:
					$next=new ValComponent($tok);
					break;
				case Token::TXT:
					$next=new TxtComponent($tok);
					break;
				default:
					new INTERNAL_SERVER_ERROR($__DEBUG?"Token of type ".$tok->getType()." not expected.":"");
			}
			if(!count($component_stack))
				new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Token mismatch discovered before token \"<code>%s</code>\".",mysql_escape_string($next->getToken()->getContent())):null);
			
			//do some stacking stuff
			$last=$component_stack[count($component_stack)-1];
			if($next->getToken()->getType()==Token::TAG&&preg_match('/end.*/mi',$next->getName())&&!in_array($next->getName(),__SITE::getTagNames())){ //if an ending tag
				$target=substr($next->getName(),3);
				if(in_array($target,$stack_exclude)||in_array($target,$stack_smear))
					new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Tag <code>%s</code> cannot be ended; excessive tag <code>%s</code> found.",$last->getToken()->getContent(),$next->getToken()->getContent()):null);
				while($last!=$root&&$target!=$last->getName()){
					if(!$verbatims&&!in_array($last->getName(),$stack_exclude)&&!in_array($last->getName(),$stack_smear)) //override some unended tags
						new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("End tag <code>%s</code> does not match open tag <code>%s</code>.",$next->getToken()->getContent(),$last->getToken()->getContent()):null);
					else{
						array_pop($component_stack);
						$last=$component_stack[count($component_stack)-1];
					}
				}
				array_pop($component_stack);
				continue;
			}
			else{
				array_push($last->components,$next);
				if($next instanceof TagComponent&&!in_array($next->getName(),$stack_exclude)){ //if an opening tag
					if(!$verbatims)
						array_push($component_stack,$next);
					continue;
				}
				else if($next instanceof TagComponent&&$next->getName()=="verbatim"){
					$verbatims++;
					array_push($component_stack,$next);
					continue;
				}
				else if($next instanceof TagComponent&&$next->getName()=="endverbatim"){
					$verbatims--;
					array_push($component_stack,$next);
					continue;
				}
			}
		}
		
		foreach($component_stack as $comp)
			if($comp!=$root&&!in_array($comp->getName(),$stack_exclude))
				new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("End tag missing for open tag <code>%s</code>.",$comp->getName()):null);
		
		$this->root=$root;
		return $root;
	}	
	function render($args=array()){
		global $__DEBUG;
		$root=$this->createComponents();

		$args=array_merge(__SITE::get(),$args);
		$root->render($args); //recursively render each component
		
		return __SITE::execMiddleware($root->getRendered());
	}
	function display($args=array()){
		echo $this->render($args);
	}
}

class Token{
	private $content,$type;
	const TAG="tag",VAL="val",TXT="txt";
	function __construct($str){
		$this->content=$str;
		if(strlen($str)>1&&substr($str,0,2)=="{%") //tag
			$this->type=self::TAG;
		else if(strlen($str)>1&&substr($str,0,2)=="{{") //value
			$this->type=self::VAL;
		else if(strlen($str)>1&&substr($str,0,2)=="{#") //shorthand for open comment tag
			$this->type=self::VAL;
		else
			$this->type=self::TXT;
	}
	function getContent(){
		return $this->content;
	}
	function getType(){
		return $this->type;
	}
}

//AbstractComponent class is the superclass for containing each bit of information
abstract class AbstractComponent{
	protected $token;
	public $data=array();
	const TAG="TAG",VAL="VAL",TXT="TXT"; //must distinguish between tokens and components
	abstract function __construct($tok); //token
	function getToken(){
		return $this->token;
	}
	function setToken($tok){
		$this->token=$tok;
	}
}
class TagComponent extends AbstractComponent{
	protected $name,$args;
	public $components,$vars,$parent=null;
	const TYPE=self::TAG;
	function __construct($tok){
		global $__DEBUG;
		if(!($tok instanceof Token)||$tok->getType()!=Token::TAG)
			new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Expected type Token::TAG, but found: %s",$tok instanceof Token?$tok->getType():$tok):null);
		$this->token=$tok;
		preg_match_all('/([^ "\']*"(\\\\"|[^"])*")|([^ "\']*\'(\\\\\'|[^\'])*\')|([^ "\']+)/mi',substr($tok->getContent(),2,-2),$args);
		$args=$args[0]; //overall match, only
		$this->name=$args[0];
		$arg_components=array();
		foreach(array_slice($args,1) as $v)
			$arg_components[]=new ValComponent(new Token("{{ $v }}"));
		$this->args=$arg_components; //each argument can be filtered like a variable
		$this->components=array();
		$this->vars=array();
	}
	function getName(){
		return $this->name;
	}
	function getArgs(){
		return $this->args;
	}
	function render(&$args){
		if($this->token==null)
			return "";
		$targs=array_merge($args,$this->vars);
		$ret=__SITE::execTag($this->name,$this->args,$this,$targs);
		foreach($targs as $k=>$v)
			if(!isset($this->vars[$k]))
				$args[$k]=$v;
		return $ret;
	}
	function renderComponents(&$args){
		$str="";
		foreach($this->components as $c){
			$targs=array_merge($args,$this->vars);
			if($c instanceof TagComponent)
				$c->parent=$this;
			$str.=$c->render($targs);
			foreach($targs as $k=>$v)
				if(!isset($this->vars[$k]))
					$args[$k]=$v;
		}
		return $str;
	}
}
class ValComponent extends AbstractComponent{
	protected $name,$filters;
	public $components;
	const TYPE=self::VAL;
	function __construct($tok){
		global $__DEBUG;
		if(!($tok instanceof Token)||$tok->getType()!=Token::VAL)
			new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Expected type Token::VAL, but found: %s",$tok instanceof Token?$tok->getType():$tok):null);
		$this->token=$tok;
		$regex_var='([^|:"\' ]+)';
		$regex_str='("(\\\\"|[^"])*")|(\'(\\\\\'|[^\'])*\')';
		$regex_clause="($regex_var|$regex_str)";
		$regex_filter="$regex_var(:?[ ]*$regex_clause([ ]+$regex_clause)*)?";
		preg_match("/^[ ]*{$regex_clause}[ ]*\|?/mi",substr($tok->getContent(),2,-2),$name);
		if(!count($name)) //empty string
			$name=array("","");
		preg_match_all("/$regex_filter/mi",substr($tok->getContent(),2+strlen($name[0]),-2),$filters);
		$filters=$filters[0]; //overall match, only
		$this->name=trim($name[1]);
		$this->filters=array();
		$lastFilters=array();
		foreach($filters as $f){ //safer to push rather than copy
			preg_match_all("/$regex_clause/mi",trim($f),$args);
			if($args[0]=="escape")
				array_push($lastFilters,$args[0]);
			else
				array_push($this->filters,$args[0]);
		}
		foreach($lastFilters as $f)
			array_push($this->filters,$f);
		$this->components=array();
	}
	function getName(){
		return $this->name;
	}
	function getFilters(){
		return $this->filters;
	}
	function render($args){
		global $__DEBUG;
		if($this->token==null)
			return "";
		if(substr($this->token->getContent(),0,2)=="{#")
			return "";
		preg_match_all('/("(\\\\"|[^"])*")|(\'(\\\\\'|[^\'])*\')|([\d]*[.][\d]+)|([\d]+)|([^.]+)/mi',$this->name,$name);
		$name=$name[0];
		if(!count($name))
			$name=array("\"\"","");
		if(preg_match('/^("((\\\\"|[^"])*)")|(\'((\\\\\'|[^\'])*)\')$/mi',$name[0]))
			eval("\$var=$name[0];");
		else if(preg_match('/^([\d]*)$/mi',$name[0],$value))
			$var=$value[0];
		else if(isset($args[$name[0]])){
			$var=$args[$name[0]];
			for($i=1;$i<count($name);$i++)
				if($name[$i]=="iteritems"&&is_array($args[$var])) //prevent unraveling of iteritems for arrays
					break;
				else{
					if(!is_array($var))
						$var=str_split($var);
					$var=$var[$name[$i]];
				}
		}
		else
			$var=null;
			# some applications may want to uncomment the next line and recomment the above line to enable runtime error-throwing for nonexistent variables
			#new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Variable '<code>%s</code>' not defined.",$name[0]):null);
		
		$str=$var;
		$safe=false;
		foreach($this->components as $c)
			$str.=$c->render($args);
		foreach($this->filters as $f){
			$str=__SITE::execFilter($f[0],array_slice($f,1),$str,$args);
			if(in_array($f[0],array("escape","safe")))
				$safe=true;
		}
		if(isset($args["__TAG__AUTOESCAPE__"])&&$args["__TAG__AUTOESCAPE__"]&&!$safe)
			return
				str_replace("<","&lt;",
				str_replace(">","&gt;",
				str_replace("'","&#39;",
				str_replace("\"","&quot;",
				str_replace("&","&amp;",
					$str
				)))));
		return $str;
	}
}
class TxtComponent extends AbstractComponent{
	const TYPE=self::TXT;
	function __construct($tok){
		global $__DEBUG;
		if(!($tok instanceof Token)||$tok->getType()!=Token::TXT)
			new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Expected type Token::TXT, but found: %s",$tok instanceof Token?$tok->getType():$tok):null);
		$this->token=$tok;
	}
	function render($args){
		return $this->token->getContent();
	}
}
class RootComponent extends AbstractComponent{ //purely for the purpose of tying up the entire component structure neatly
	public $components;
	private $rendered;
	function __construct(){
		$this->components=array();
		$rendered=null;
	}
	function render($args){
		$rendered="";
		foreach($this->components as $c){
			$c->parent=$this;
			$rendered.=$c->render($args);
		}
		$this->rendered=$rendered;
	}
	function getRendered(){
		return $this->rendered;
	}
}

abstract class AbstractTag{
	final public static function call_render($container){
		return static::render($container->args,$container->c,$container->vars);
	}
	abstract public static function render($args,$c,&$vars);
}
final class AbstractTagContainer{
	public $args,$c,$vars;
	function __construct($args,$c,&$vars){
		$this->args=$args;
		$this->c=$c;
		$this->vars=&$vars;
	}
}
abstract class AbstractFilter{
	final public static function call_render($container){
		return static::render($container->args,$container->var,$container->vars);
	}
	abstract public static function render($args,$var,&$vars);
}
final class AbstractFilterContainer{
	public $args,$var,$vars;
	function __construct($args,$var,&$vars){
		$this->args=$args;
		$this->var=$var;
		$this->vars=&$vars;
	}
}
abstract class AbstractMiddleware{
	public static function preprocess(){}
	public static function render($text){return $text;}
}
?>
