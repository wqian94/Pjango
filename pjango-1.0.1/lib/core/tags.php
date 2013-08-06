<?php
/*
** tags.php
** 
** Default, built-in tags.
** 
** Author: Luo Qian
*/

//ajax tag
class __Tag_ajax extends AbstractTag{
	private static $included=false;
	public static function render($args,$c,&$vars){
		$ajax="";
		$inline=(count($args)&&$args[0]->render($vars)=="inline");
		if(!self::$included){
			$csrftoken=__Tag_csrf_token::getHash();
			$csrfkey=sha1("csrftoken").__Tag_csrf_token::getKey();
			$ajax=preg_replace('/[\t\r\n]/mi',"",($inline?"":"<script type=\"text/javascript\">/*<!--*/").<<<AJAX
function AJAX(async){
	if(navigator.appName=="Microsoft Internet Explorer"){
		this.dispatcher=new ActiveXObject("Microsoft.XMLHTTP");
	}
	else if(window.XMLHttpRequest!=null){
		this.dispatcher=new XMLHttpRequest();
	}
	else{
		this.dispatcher=new ActiveXObject("Microsoft.XMLHTTP");
	}
	this.create=function(){
			if(window.XMLHttpRequest){
				this.dispatcher=new XMLHttpRequest();
			}
			else{
				this.dispatcher=new ActiveXObject("Microsoft.XMLHTTP");
			}
		};
	this.ready=function(){
			return this.dispatcher.readyState==4;
		};
	this.buffer=function(){
			if(this.dispatcher.readyState!=4){
				return "";
			}
			else if(!this.async||this.dispatcher.status==200){
				return this.dispatcher.responseText;
			}
			else{
				return "Page not found.";
			}
		};
	this.async=async;
	this.csrfkey="$csrfkey";
	this.csrftoken="$csrftoken";
	this.get=function(url,args){
			/*args must be an associate array*/
			this.create();
			lastSlash=url.lastIndexOf("/");
			argstr=(url.indexOf("?",lastSlash<0?0:lastSlash))<0?"?":"";
			for(k in args){
				argstr=argstr.concat("&",k,"=",args[k]);
			}
			this.dispatcher.open("GET",url.concat(argstr),this.async);
			this.dispatcher.send();
			var readyfunc=function(){};
			if(arguments[2]!=null){
				readyfunc=arguments[2];
			}
			if(this.async){
				this.dispatcher.onreadystatechange=readyfunc;
			}
			else{
				return this.dispatcher.responseText;
			}
		};
	this.post=function(url,args){
			/*args must be an associate array*/
			this.create();
			argstr="".concat(this.csrfkey,"=",this.csrftoken);
			for(k in args){
				argstr=argstr.concat("&",k,"=",args[k]);
			}
			argstr=argstr.substr(1);
			this.dispatcher.open("POST",url,this.async);
			this.dispatcher.setRequestHeader("Content-type","application/x-www-form-urlencoded");
			this.dispatcher.send(argstr);
			var readyfunc=function(){};
			if(arguments[2]!=null){
				readyfunc=arguments[2];
			}
			if(this.async){
				this.dispatcher.onreadystatechange=readyfunc;
			}
			else{
				return this.dispatcher.responseText;
			}
		};
	this.swap=function(){
		this.async=!this.async;
	};
}
AJAX
			.($inline?"":"/*-->*/</script>"));
			$included=true;
		}
		return $ajax.$c->renderComponents($vars);
	}
}
__SITE::addTag("ajax",new __Tag_ajax,true);

//autoescape tag
class __Tag_autoescape extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		//render arguments
		$cargs=$c->getArgs();
		foreach($cargs as $k=>$v){
			$cvars=$vars;
			if(!in_array($v->getName(),array_keys($vars)))
				$cvars[$v->getName()]=$v->getName();
			$cargs[$k]=$v->render($cvars);
		}
		
		if(!in_array($cargs[0],array("on","off")))
			new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
		$vars["__TAG__AUTOESCAPE__"]=($cargs[0]=="on");
		return $c->renderComponents($vars);
	}
}
__SITE::addTag("autoescape",new __Tag_autoescape);

//block tag
class __Tag_block extends AbstractTag{
	public static function render($args,$c,&$vars){
		$str=$c->renderComponents($vars);
		return $str;
	}
}
__SITE::addTag("block",new __Tag_block);

//comment tag
class __Tag_comment extends AbstractTag{
	public static function render($args,$c,&$vars){
		return "";
	}
}
__SITE::addTag("comment",new __Tag_comment);

//csrf_token tag
class __Tag_csrf_token extends AbstractTag{
	private static $hash=null,$key=null;
	public static function getHash(){
		if(self::$hash==null){
			$s1=hash("sha256",time()*(time()/2+1));
			$s2=hash("sha256",time()*(time()/2-1));
			$buffer="";
			$i=0;
			$j=0;
			while($i<strlen($s1)&&$j<strlen($s2)){
				$choice=rand(0,1);
				if($choice==0){
					$buffer.=substr($s1,$i,1);
					$i++;
				}
				else{
					$buffer.=substr($s2,$j,1);
					$j++;
				}
			}
			
			//note that strlen($buffer) may be < strlen($s1) + strlen($s2)
			self::$hash=substr(str_shuffle(str_repeat($buffer,7)),0,strlen($buffer)/2); //shuffles everything around
			self::$key=sprintf("%15s",substr(md5(sha1(time())),0,15));
			$_SESSION[md5("csrftoken".self::$key)]=self::$hash;
		}
		return self::$hash;
	}
	public static function getKey(){
		return self::$key;
	}
	public static function render($args,$c,&$vars){
		$csrf=self::getHash();
		$key=self::getKey();
		return "<div style=\"display:none;\"><input type=\"hidden\" style=\"display:none;\" name=\"".sha1("csrftoken")."$key\" value=\"$csrf\" /></div>".$c->renderComponents($vars);
	}
}
__SITE::addTag("csrf_token",new __Tag_csrf_token,true);

//css tag
class __Tag_css extends AbstractTag{
	public static function render($args,$c,&$vars){
		return "<style type=\"text/css\">".$c->renderComponents($vars)."</style>";
	}
}
__SITE::addTag("css",new __Tag_css);

//css_ext tag
class __Tag_css_ext extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		if(count($args)<1)
			new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
		return "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$args[0]->render($vars)."\" />";
	}
}
__SITE::addTag("css_ext",new __Tag_css_ext,true);

//cycle tag
class __Tag_cycle extends AbstractTag{
	public static function render($args,$c,&$vars){
		$silent=false;
		$vars["ASDF"]=1;
		if(count($args)>2&&$args[count($args)-2]->getName()=="as"||count($args)>3&&$args[count($args)-3]->getName()=="as"&&$args[count($args)-1]->getName()=="silent"){
			$aargs=array_slice($args,$args[count($args)-2]->getName()=="as"?-1:-2);
			if(count($aargs)==2)
				$silent=true;
			$v=$aargs[0]->getName(); //variable name
			$vars["__Tag_cycle_$v"]=$c;
			$vars["__Tag_cycle_args_$v"]=array_slice($args,0,count($args)-count($aargs)-1);
		}
		else if((count($args)==1||count($args)==2&&$args[count($args)-1]->getName()=="silent")&&isset($vars[$args[0]->getName()])){
			$v=$args[0]->getName();
			$silent=$args[count($args)-1]->getName()=="silent";
		}
		else if(count($args)>1&&$args[count($args)-1]->getName()=="silent")
			$silent=true;
		if(isset($v)&&isset($vars["__Tag_cycle_$v"])){
			$c=$vars["__Tag_cycle_$v"];
			$args=$vars["__Tag_cycle_args_$v"];
		}
		if(!isset($c->data["cycle_counter"]))
			$c->data["cycle_counter"]=0;
		$counter=$c->data["cycle_counter"];
		$c->data["cycle_counter"]=($counter+1)%count($args);
		$next=$args[$counter]->render($vars);
		if(isset($v))
			$vars[$v]=$next;
		return $silent?"":$next;
	}
}
__SITE::addTag("cycle",new __Tag_cycle,true);

//extends tag
class __Tag_extends extends AbstractTag{
	public static function render($args,$c,&$vars){
		$view=null;
		preg_match('/^{{[ ]*(.*)[ ]*}}$/mi',$args[0]->getToken()->getContent(),$inc);
		$inc=trim($inc[1]);
		if(preg_match('/^"(.*)"$/mi',$inc,$fname)) //is a string; include file
			$view=new View($fname[1]);
		else
			$view=new View($args[0]->render($vars));
		
		$root=$view->createComponents();
		$blocks=array();
		$queue=$c->components;
		
		while(count($queue)){
			$comp=$queue[0];
			$queue=array_slice($queue,1);
			
			if($comp instanceof TagComponent){
				if($comp->getName()=="extends"){
					$tvars=array();
					foreach($vars as $k=>$v)
						$tvars[$k]=$v;
					$tvars["__TAG__INCLUDE__COMPONENTS__ONLY__"]=true;
					$inc_comps=$comp->render($tvars);
					foreach($inc_comps as $icomp)
						array_push($queue,$icomp);
				}
				else if($comp->getName()=="block"){
					$block_args=$comp->getArgs();
					$blocks[$block_args[0]->getName()]=$comp;
				}
				
				foreach($comp->components as $next)
					array_push($queue,$next);
			}
		}
		
		$queue=array();
		foreach($root->components as $k=>$v)
			array_push($queue,array($root,$k,$v)); //parent component, key, value
		
		while(count($queue)){
			$triple=$queue[0];
			$queue=array_slice($queue,1);
			
			$comp=$triple[2];
			if($comp instanceof TagComponent){
				$block_args=$comp->getArgs();
				if($comp->getName()=="block"&&isset($blocks[$block_args[0]->getName()])){
					$triple[0]->components[$triple[1]]=$blocks[$block_args[0]->getName()];
					continue;
				}
				foreach($comp->components as $k=>$next)
					array_push($queue,array($comp,$k,$next));
			}
		}
		
		if(isset($vars["__TAG__INCLUDE__COMPONENTS__ONLY__"])&&$vars["__TAG__INCLUDE__COMPONENTS__ONLY__"])
			return $root;
		
		$root->render($vars);
		return $root->getRendered();
	}
}
__SITE::addTag("extends",new __Tag_extends,true);

//filter tag
class __Tag_filter extends AbstractTag{
	public static function render($args,$c,&$vars){
		$val=new ValComponent(new Token(sprintf("{{%s}}",substr($c->getToken()->getContent(),2,-2))));
		$filters=$val->getFilters();
		$str=$c->renderComponents($vars);
		foreach($filters as $f)
			$str=__SITE::execFilter($f[0],array_slice($f,1),$str,$vars);
		if(isset($args["__TAG__AUTOESCAPE__"])&&$args["__TAG__AUTOESCAPE__"])
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
__SITE::addTag("filter",new __Tag_filter);

//firstof tag
class __Tag_firstof extends AbstractTag{
	public static function render($args,$c,&$vars){
		//render arguments
		$cargs=$c->getArgs();
		foreach($cargs as $k=>$v){
			$cvars=$vars;
			if(!in_array($v->getName(),array_keys($vars)))
				$cvars[$v->getName()]=$v->getName();
			$cargs[$k]=$v->render($cvars);
			if(preg_match('/^".*"$/mi',$args[$k]->getName())) //"fallback value"
				return $cargs[$k];
			else if($cargs[$k]!=false)
				return $cargs[$k];
		}
	}
}
__SITE::addTag("firstof",new __Tag_firstof,true);

//for-empty tags
class __Tag_for extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		if(count($args)!=3)
			new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
		
		//render arguments
		$cargs=$c->getArgs();
		foreach($cargs as $k=>$v){
			$cvars=$vars;
			if(!in_array($v->getName(),array_keys($vars)))
				$cvars[$v->getName()]=$v->getName();
			$cargs[$k]=$v->render($cvars);
		}
		
		//process arguments
		if($cargs[1]!="in")
			new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
		$case=null;
		$iterate=array();
		if(is_array($cargs[2])){ //direct array, iterating over keys
			$component_args=$c->getArgs();
			$ca=$component_args[2];
			if(is_string($ca)&&strlen($ca)>strlen(".items")&&substr($ca,-strlen(".items"))==".items"){ //array, iterating over both keys and values
				$case=1;
				$iterate=array();
				foreach($vars[$cargs[2]] as $k=>$v)
					$iterate[]=array($k,$v);
			}
			else{
				$case=0;
				$iterate=array_keys($cargs[2]); //default: iterating over keys of associative list
				$list=true;
				for($i=0;$i<count($iterate);$i++)
					if($iterate[$i]!=$i)
						$list=false;
				if($list) //iterating over the values of a list
					$iterate=array_values($cargs[2]);
			}
		}
		else{ //plain old string
			$case=2;
			$iterate=str_split($cargs[2]);
		}
		$cargv=explode(",",$cargs[0]); //get identifiers for iterating variables
		
		//render internally
		if($cargs[count($cargs)-1]=="reversed")
			$iterate=array_reverse($iterate);
		$str="";
		$i=0;
		for($i=0;$i<count($iterate);$i++){
			$ivar=array();
			if($case==1)
				$ivar=array($cargv[0]=>$iterate[$i][0],$cargv[1]=>$iterate[$i][1]);
			else if(is_array($iterate)&&count($cargv)>1){
				$ivar=array();
				for($j=0;$j<count($cargv);$j++)
					$ivar[$cargv[$j]]=isset($iterate[$i][$j])?$iterate[$i][$j]:null;
			}
			else
				$ivar=array($cargv[0]=>$iterate[$i]);
			$ivar["forloop"]=array(
				"counter"=>$i+1,
				"counter0"=>$i,
				"revcounter"=>count($iterate)-$i,
				"revcounter0"=>count($iterate)-$i-1,
				"first"=>$i==0,
				"last"=>$i==count($iterate)-1,
				"parentloop"=>null,
				);
			if(isset($vars["__TAG__FOR__parentloop"]))
				$ivar["forloop"]["parentloop"]=$vars["__TAG__FOR__parentloop"];
			$ivar["__TAG__FOR__parentloop"]=$ivar;
			
			$merger=array_merge($vars,$ivar);
			foreach($c->components as $cc)
				if($cc instanceof TagComponent&&$cc->getName()=="empty")
					break;
				else{
					$str.=$cc->render($merger);
					foreach($merger as $k=>$v)
						if(!isset($ivar[$k]))
							$vars[$k]=$v;
				}
		}
		
		if(!count($iterate)){
			foreach($c->components as $cc)
				if($cc instanceof TagComponent&&$cc->getName()=="empty")
					$str.=$cc->render($vars);
		}
		
		return $str;
	}
}
__SITE::addTag("for",new __Tag_for);

//empty tag
class __Tag_empty extends AbstractTag{
	public static function render($args,$c,&$vars){
		return $c->renderComponents($vars);
	}
}
__SITE::addTag("empty",new __Tag_empty,true,true);

//if-elif-else tags
class __Tag_if extends AbstractTag{
	protected static $ifStack=array();
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		
		//re-"contract" the if/elif/else statements
		$if=array(); //array of non-elif/else statements for this if
		$else=array(); //array of arrays of child statements for the elif/else's
		$ifStatement=true;
		foreach($c->components as $comp){
			if(get_class($comp)==get_class($c)&&in_array($comp->getName(),array("elif","else"))){
				$ifStatement=false;
				$else[]=array($comp);
			}
			else if($ifStatement)
				$if[]=$comp;
			else
				$else[count($else)-1][]=$comp;
		}
		
		//render arguments
		$cargs=$c->getArgs();
		$cvars=$vars;
		$supported_ops=array("==","!=","<",">","<=",">=","===","!==","not","and","or","in","(","!(",")");
		foreach($cargs as $k=>$v){
			$v=$cargs[$k];
			if(!in_array(substr($v->getName(),0,strpos($v->getName(),"|")===false?strlen($v->getName()):strpos($v->getName(),"|")-1),array_keys($vars)))
				$cvars[$v->getName()]=false;
			if(in_array($v->getName(),$supported_ops)||is_numeric($v->getName())){
				if($v->getName()=="in"){
					if($cargs[$k-1]=="!"){
						$cargs[$k-1]=$cargs[$k-2];
						$cargs[$k-2]="!";
					}
					$cargs[$k-1]="in_array(".$cargs[$k-1].",";
					$v=$cargs[$k+1];
					$cargs[$k+1]=new ValComponent(new Token("{{ ) }}"));
					if(!in_array(substr($v->getName(),0,strpos($v->getName(),"|")===false?strlen($v->getName()):strpos($v->getName(),"|")-1),array_keys($vars)))
						$cvars[$v->getName()]=false;
					
					$name=$v->getName();
					if(strpos($v->getName(),"|")){ //has filters
						$name=substr($v->getName(),0,strpos($v->getName(),"|")-1);
						$cvars[$name]=$v->render($vars);
					}
					$cargs[$k]="\$cvars[\"$name\"]";
				}
				else
					$cargs[$k]=$v->getName();
			}
			else{
				$name=$v->getName();
				if(strpos($v->getName(),"|")){ //has filters
					$name=substr($v->getName(),0,strpos($v->getName(),"|")-1);
					$cvars[$name]=$v->render($vars);
				}
				$cargs[$k]="\$cvars[\"$name\"]";
			}
			if($cargs[$k]=="not")
				$cargs[$k]="!";
		}
		
		for($i=0;$i<count($cargs)-1;$i++)
			if($cargs[$i]=="!"){
				if($cargs[$i+1]=="=")
					array_splice($cargs,$i,2,"!=");
				else if($cargs[$i+1]=="(")
					array_splice($cargs,$i,2,"!(");
				else
					array_splice($cargs,$i,2,"!".$cargs[$i+1]);
				$i--;
			}
		$truth=null;
		eval("\$truth=(".join(" ",$cargs).");");
		
		//render internally
		$str="";
		$ifStackKey=sha1(time());
		while(in_array($ifStackKey,array_keys(self::$ifStack)))
			$ifStackKey=sha1($ifStackKey);
		self::$ifStack[$ifStackKey]=false;
		if($truth){
			foreach($if as $ch)
				$str.=$ch->render($vars);
			self::$ifStack[$ifStackKey]=true;
		}
		else{
			foreach($else as $e){
				$str.=$e[0]->render($vars);
				if(self::$ifStack[$ifStackKey])
					break;
			}
		}
		return $str;
	}
}
__SITE::addTag("if",new __Tag_if);

//elif tag
class __Tag_elif extends __Tag_if{
	//this class's render function is identical to if's render function;
}
__SITE::addTag("elif",new __Tag_elif,true,true);

//else tag
class __Tag_else extends __Tag_if{
	public static function render($args,$c,&$vars){
		//render arguments
		$cargs=$c->getArgs();
		foreach($cargs as $k=>$v){
			$cvars=$vars;
			if(!in_array($v->getName(),array_keys($vars)))
				$cvars[$v->getName()]=$v->getName();
			$cargs[$k]=$v->render($cvars);
		}
		
		$str="";
		foreach($c->components as $ch)
			$str.=$ch->render($vars);
		return $str;
	}
}
__SITE::addTag("else",new __Tag_else,true,true);

//ifequal tag
class __Tag_ifequal extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		if(count($args)<2)
			new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Tag '%s' requires at least 2 arguments.",$c->getName()):null);
		
		//render arguments
		$cargs=$c->getArgs();
		foreach($cargs as $k=>$v){
			$cvars=$vars;
			if(!in_array($v->getName(),array_keys($vars)))
				$cvars[$v->getName()]=$v->getName();
			$cargs[$k]=$v->render($cvars);
		}
		
		$truth=true;
		for($i=1;$truth&&$i<count($cargs);$i++)
			$truth=$cargs[$i]==$cargs[$i-1];
		
		$str="";
		if($truth){
			foreach($c->components as $cc)
				if($cc instanceof TagComponent&&$cc->getName()=="else")
					break;
				else
					$str.=$cc->render($vars);
		}
		else{
			$else_found=false;
			foreach($c->components as $cc){
				if($cc instanceof TagComponent&&$cc->getName()=="else")
					$else_found=true;
				if($else_found)
					$str.=$cc->render($vars);
			}
		}
		
		return $str;
	}
}
__SITE::addTag("ifequal",new __Tag_ifequal);

//ifnotequal tag
class __Tag_ifnotequal extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		if(count($args)<2)
			new INTERNAL_SERVER_ERROR($__DEBUG?sprintf("Tag '%s' requires at least 2 arguments.",$c->getName()):null);
		
		//render arguments
		$cargs=$c->getArgs();
		foreach($cargs as $k=>$v){
			$cvars=$vars;
			if(!in_array($v->getName(),array_keys($vars)))
				$cvars[$v->getName()]=$v->getName();
			$cargs[$k]=$v->render($cvars);
		}
		
		$truth=true;
		for($i=1;$truth&&$i<count($cargs);$i++)
			for($j=0;$truth&&$j<$i;$j++)
				$truth=$cargs[$i]!=$cargs[$j];
		
		$str="";
		if($truth){
			foreach($c->components as $cc)
				if($cc instanceof TagComponent&&$cc->getName()=="else")
					break;
				else
					$str.=$cc->render($vars);
		}
		else{
			$else_found=false;
			foreach($c->components as $cc){
				if($cc instanceof TagComponent&&$cc->getName()=="else")
					$else_found=true;
				if($else_found)
					$str.=$cc->render($vars);
			}
		}
		
		return $str;
	}
}
__SITE::addTag("ifnotequal",new __Tag_ifnotequal);

//include tag
class __Tag_include extends AbstractTag{
	public static function render($args,$c,&$vars){
		$view=null;
		preg_match('/^{{[ ]*(.*)[ ]*}}$/mi',$args[0]->getToken()->getContent(),$inc);
		$inc=trim($inc[1]);
		if(preg_match('/^"(.*)"$/mi',$inc,$fname)) //is a string; include file
			$view=new View($fname[1]);
		else
			$view=new View($args[0]->render($vars));
		
		return $view->render($vars);
	}
}
__SITE::addTag("include",new __Tag_include,true);

//js tag
class __Tag_js extends AbstractTag{
	public static function render($args,$c,&$vars){
		return "<script type=\"text/javascript\">/*<!--*/".$c->renderComponents($vars)."/*-->*/<script>";
	}
}
__SITE::addTag("js",new __Tag_js);

//js_ext tag
class __Tag_js_ext extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		if(count($args)<1)
			new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
		return "<script src=\"".$args[0]->render($vars)."\"></script>";
	}
}
__SITE::addTag("js_ext",new __Tag_js_ext);

//now tag
class __Tag_now extends AbstractTag{
	public static function render($args,$c,&$vars){
		return date($args[0]->render($vars));
	}
}
__SITE::addTag("now",new __Tag_now,true);

//regroup tag
class __Tag_regroup extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		
		$arr=$args[0]->render($vars);
		if(count($args)<5||$args[1]->getName()!="by"||$args[3]->getName()!="as")
			new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
		
		$regrouped=array();
		foreach($arr as $e){
			$re_arr=$arr;
			unset($re_arr[$args[2]->getName()]);
			if(count($regrouped)&&$regrouped[count($regrouped)-1]["grouper"]==$e[$args[2]->getName()])
				array_push($regrouped[count($regrouped)-1]["list"],$re_arr);
			else
				array_push($regrouped,array("grouper"=>$args[2]->getName(),"list"=>array($re_arr)));
		}
		
		$tvars=$vars;
		$tvars[$args[4]->getName()]=$regrouped;
		if($c->parent!=null)
			$c->parent->vars[$args[4]->getName()]=$regrouped;
		return $c->renderComponents($tvars);
	}
}
__SITE::addTag("regroup",new __Tag_regroup,true);

//replace tag
class __Tag_replace extends AbstractTag{
	public static function render($args,$c,&$vars){
		eval("\$needle=\"".$args[0]->render($vars)."\";");
		eval("\$replace=\"".$args[1]->render($vars)."\";");
		return str_replace($needle,$replace,$c->renderComponents($vars));
	}
}
__SITE::addTag("replace",new __Tag_replace);

//spaceless tag
class __Tag_spaceless extends AbstractTag{
	public static function render($args,$c,&$vars){
		return preg_replace('/[\r\n\t]/mi','',$c->renderComponents($vars));
	}
}
__SITE::addTag("spaceless",new __Tag_spaceless);

//ssi tag
class __Tag_ssi extends AbstractTag{
	public static function render($args,$c,&$vars){
		if(count($args)>1&&$args[1]=="parsed"){
			$view=new View($args[0]->render($vars));
			return $view->render($vars).$c->renderComponents($vars);
		}
		return file_get_contents($args[0]->render($vars)).$c->renderComponents($vars);
	}
}
__SITE::addTag("ssi",new __Tag_ssi,true);

//templatetag tag
class __Tag_templatetag extends AbstractTag{
	public static function render($args,$c,&$vars){
		$tags=array(
			"openblock"=>"{%",
			"closeblock"=>"%}",
			"openvariable"=>"{{",
			"closevariable"=>"}}",
			"openbrace"=>"{",
			"closebrace"=>"}",
			"opencomment"=>"{#",
			"closecomment"=>"#}",
			);
		return $tags[$args[0]->getName()];
	}
}
__SITE::addTag("templatetag",new __Tag_templatetag,true);

//vertatim-endverbatim tags
class __Tag_verbatim extends AbstractTag{
	protected static $verbatimName=null,$verbatimActive=false;
	public static function render($args,$c,&$vars){
		self::$verbatimActive=true;
		if(count($args))
			self::$verbatimName=$args[0]->getName();
		else
			self::$verbatimName="";
		
		$begun=false;
		$queue=$c->parent->components;
		while(count($queue)){
			$cc=$queue[0];
			$queue=array_slice($queue,1);
			if($begun){
				array_push($c->components,$cc);
				if(isset($cc->components))
					array_splice($queue,0,0,$cc->components);
			}
			if($cc instanceof TagComponent&&$cc==$c)
				$begun=true;
			else if($cc instanceof TagComponent&&$cc->getName()=="endverbatim"){
				$cc->render($vars);
				if(!self::$verbatimActive)
					break;
			}
		}
		
		$str="";
		foreach($c->components as $i=>$cc){
			if($i!=count($c->components)-1)
				$str.=$cc->getToken()->getContent();
			if($cc instanceof TxtComponent)
				$cc->setToken(new Token(""));
			else
				$cc->setToken(null);
		}
		return $str;
	}
}
__SITE::addTag("verbatim",new __Tag_verbatim,true);

//endverbatim tag
class __Tag_endverbatim extends __Tag_verbatim{
	public static function render($args,$c,&$vars){
		$name="";
		if(count($args))
			$name=$args[0]->getName();
		if($name==self::$verbatimName){
			self::$verbatimActive=false;
		}
		return "";
	}
}
__SITE::addTag("endverbatim",new __Tag_endverbatim,true);

//widthratio tag
class __Tag_widthratio extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		if(count($args)!=3)
			new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
		return round(((float)$args[0]->render($vars))*((float)$args[2]->render($vars))/((float)$args[1]->render($vars)));
	}
}
__SITE::addTag("widthratio",new __Tag_widthratio,true);

//with tag
class __Tag_with extends AbstractTag{
	public static function render($args,$c,&$vars){
		global $__DEBUG;
		$tvars=$vars;
		$varnames=array();
		if(strpos($args[0]->getName(),"=")){ //new syntax: var0=val0 var1=val1 ...
			for($i=1;$i<count($args);$i+=2){
				$var=substr($args[$i-1]->getName(),0,strpos($args[$i-1]->getName(),"="));
				$val=$args[$i]->render($vars);
				$tvars[$var]=$val;
				array_push($varnames,$var);
			}
		}
		else{ //old syntax: val as var
			if(count($args)!=3)
				new TAG_ARGUMENT_ERROR($__DEBUG?$c:null);
			$tvars[$args[2]->getName()]=$args[0]->render($vars);
			array_push($varnames,$args[2]->getName());
		}
		$str=$c->renderComponents($tvars);
		foreach($tvars as $k=>$v){
			if(!in_array($k,$varnames)&&$vars[$k]!=$v)
				$vars[$k]=$v;
		}
		return $str;
	}
}
__SITE::addTag("with",new __Tag_with);
?>
