<?php
/*
** site.php
** 
** The __SITE class
** 
** Author: Luo Qian
*/

class __SITE{
	protected static $vars=array(
		"not"=>"!",
		"TIME_FORMAT"=>"H:i:s",
		),$middleware=array(),$tags=array(),$unclosed_tags=array(),$unclosed_content_tags=array(),$filters=array();
	
	final static function addVars($v){
		foreach($v as $key=>$var)
			self::$vars[$key]=$var;
	}
	final static function addMiddleware($m){
		foreach($m as $key=>$mw)
			self::$middleware[$key]=$mw;
	}
	
	final static function refactor(){ //refactors preset data to clean value
		foreach(self::$vars["PATH"] as $k=>$v)
			if(strlen($v)<1||substr($v,-1)!='/')
				self::$vars["PATH"][$k].="/";
		foreach(self::$vars["VIEWS"] as $k=>$v)
			if(strlen($v)<strlen(self::$vars["PATH"]["SYSTEM"])||substr($v,0,strlen(self::$vars["PATH"]["SYSTEM"]))!=strlen(self::$vars["PATH"]["SYSTEM"]))
				self::$vars["VIEWS"][$k]=self::$vars["PATH"]["SYSTEM"].$v; //absolute paths if not already
	}
	final static function get(){ //returns a copy of the internally stored system globals, without prepended __'s
		$export=array();
		foreach(self::$vars as $k=>$v)
			$export[$k]=$v;
		return $export;
	}
	final static function set(){ //reset all system globals to their original values
		foreach(self::$vars as $k=>$v)
			$GLOBALS["__$k"]=$v; //all variables prepended with __
	}
	
	final static function addTag($name,$obj,$unclosed=false,$content=false){
		self::$tags[$name]=get_class($obj);
		if($unclosed){
			if($content)
				array_push(self::$unclosed_content_tags,$name);
			else
				array_push(self::$unclosed_tags,$name);
		}
	}
	final static function getTagNames(){
		return array_keys(self::$tags);
	}
	final static function getUnclosedTagNames(){
		return self::$unclosed_tags;
	}
	final static function getUnclosedContentTagNames(){
		return self::$unclosed_content_tags;
	}
	final static function execTag($name,$args,$comp,&$vars){
		$abstractTag="AbstractTag";
		if(!in_array($name,array_keys(self::$tags)))
			new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"No tag '$name' registered.":null);
		else if(!class_exists(self::$tags[$name]))
			new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Tag class '".self::$tags[$name]."' not found.":null);
		else if(!((new self::$tags[$name]) instanceof $abstractTag))
			new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Tag class '$name' does not extend from '$abstractTag'.":null);
		return call_user_func_array(array(self::$tags[$name],"call_render"),array(new AbstractTagContainer($args,$comp,$vars)));
	}
	
	final static function addFilter($name,$obj){
		self::$filters[$name]=get_class($obj);
	}
	final static function execFilter($name,$args,$str,&$vars){
		$abstractFilter="AbstractFilter";
		if(!in_array($name,array_keys(self::$filters)))
			new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"No filter '$name' registered.":null);
		else if(!class_exists(self::$filters[$name]))
			new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Filter class '".self::$filters[$name]."' not found.":null);
		else if(!((new self::$filters[$name]) instanceof $abstractFilter))
			new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Filter class '$name' does not extend from '$abstractFilter'.":null);
		return call_user_func_array(array(self::$filters[$name],"call_render"),array(new AbstractFilterContainer($args,$str,$vars)));
	}
	final static function execMiddleware($rendered){
		$keys=array_keys(self::$middleware);
		rsort($keys);
		foreach($keys as $k){
			$m=self::$middleware[$k];
			$abstractMiddleware="AbstractMiddleware";
			if(!class_exists($m))
				new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Middleware class '$m' not found.":null);
			else if(!((new $m) instanceof $abstractMiddleware))
				new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Middleware class '$m' does not extend from '$abstractMiddleware'.":null);
			$rendered=call_user_func(array($m,"render"),$rendered);
		}
		return $rendered;
	}
	final static function preprocess(){
		$keys=array_keys(self::$middleware);
		sort($keys);
		foreach($keys as $k){
			$m=self::$middleware[$k];
			$abstractMiddleware="AbstractMiddleware";
			if(!class_exists($m))
				new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Middleware class '$m' not found.":null);
			else if(!((new $m) instanceof $abstractMiddleware))
				new INTERNAL_SERVER_ERROR(self::$vars["DEBUG"]?"Middleware class '$m' does not extend from '$abstractMiddleware'.":null);
			call_user_func(array($m,"preprocess"));
		}
	}
}
?>
