<?php
/*
** filters.php
** 
** Default, built-in tags.
** 
** Author: Luo Qian
*/

//length add
class __Filter_add extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>add</code>' expecting at least 1 argument, found 0.":null);
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		if(is_array($var)&&is_array($val))
			return array_merge($var,$val);
		else if(is_numeric($var)&&is_numeric($val))
			return $var+$val;
		return "$var$val";
	}
}
__SITE::addFilter("add",new __Filter_add);

//addslashes filter
class __Filter_addslashes extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return addslashes($var);
	}
}
__SITE::addFilter("addslashes",new __Filter_addslashes);

//capfirst filter
class __Filter_capfirst extends AbstractFilter{
	public static function render($args,$var,&$vars){
		preg_match('/([\W]*)([\w])(.*)/m',$var,$m);
		$ch=$m[2];
		return $m[1].($ch>='a'&&$ch<='z'?chr(ord($ch)-32):$ch).$m[3];
	}
}
__SITE::addFilter("capfirst",new __Filter_capfirst);

//center filter
class __Filter_center extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>center</code>' expecting at least 1 argument, found 0.":null);
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		$diff=$val-strlen($var);
		if($diff<=0)
			return $var;
		return sprintf("%".(ceil(0.5*$diff))."s%s%".(floor(0.5*$diff))."s","",$var,"");
	}
}
__SITE::addFilter("center",new __Filter_center);

//cut filter
class __Filter_cut extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>cut</code>' expecting at least 1 argument, found 0.":null);
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		return str_replace($val,"",$var);
	}
}
__SITE::addFilter("cut",new __Filter_cut);

//date filter
class __Filter_date extends AbstractFilter{
	public static function render($args,$var,&$vars){
		$val="";
		if(count($args)){
			$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
			$val=$val_obj->render($vars);
		}
		else{
			$val="r";
		}
		if(is_numeric($var))
			return date($val,$var);
		return date($val);
	}
}
__SITE::addFilter("date",new __Filter_date);

//default filter
class __Filter_default extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>default</code>' expecting at least 1 argument, found 0.":null);
		if($var!=false)
			return $var;
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		return $val;
	}
}
__SITE::addFilter("default",new __Filter_default);

//default_if_null filter -- by implementation, the chances of this happening are super-slim; the value would have to be hacked into the filter, essentially...
class __Filter_default_if_null extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>default_if_null</code>' expecting at least 1 argument, found 0.":null);
		if($var!==null)
			return $var;
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		return $val;
	}
}
__SITE::addFilter("default_if_null",new __Filter_default_if_null);
__SITE::addFilter("default_if_none",new __Filter_default_if_null); //for python compatibility

//dictsort filter
class __Filter_dictsort extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>dictsort</code>' expecting at least 1 argument, found 0.":null);
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		$arr=array();
		foreach($var as $k=>$v)
			$arr[$v[$val]]=$v;
		ksort($arr);
		$ret=array();
		foreach($arr as $v)
			array_push($ret,$v);
		return $ret;
	}
}
__SITE::addFilter("dictsort",new __Filter_dictsort);

//dictsortreversed filter
class __Filter_dictsortreversed extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>dictsortreversed</code>' expecting at least 1 argument, found 0.":null);
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		$arr=array();
		foreach($var as $k=>$v)
			$arr[$v[$val]]=$v;
		$arr=krsort($arr);
		$ret=array();
		foreach($arr as $v)
			array_push($ret,$v);
		return $ret;
	}
}
__SITE::addFilter("dictsortreversed",new __Filter_dictsortreversed);

//divisibleby filter
class __Filter_divisibleby extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>divisibleby</code>' expecting at least 1 argument, found 0.":null);
		$val_obj=new ValComponent(new Token("{{ $args[0] }}"));
		$val=$val_obj->render($vars);
		return is_numeric($var)&&is_numeric($val)&&!($var%$val);
	}
}
__SITE::addFilter("divisibleby",new __Filter_divisibleby);

//escape & force_escape filters
class __Filter_escape extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return
				str_replace("<","&lt;",
				str_replace(">","&gt;",
				str_replace("'","&#39;",
				str_replace("\"","&quot;",
				str_replace("&","&amp;",
					$var
				)))));
	}
}
__SITE::addFilter("escape",new __Filter_escape);
__SITE::addFilter("force_escape",new __Filter_escape); //really the same filter...

//escapejs filter
class __Filter_escapejs extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return json_encode($var);
	}
}
__SITE::addFilter("escapejs",new __Filter_escapejs);

//filesizeformat filter
class __Filter_filesizeformat extends AbstractFilter{
	public static function render($args,$var,&$vars){
		$prefixes=array("","K","M","G","T","P","E","Z","Y");
		for($i=0;$var>=1000&&$i<count($prefixes)-1;$i++)
			$var/=1024;
		$unit=$prefixes[$i].($prefixes[$i]==""?"":"i")."B";
		$size=sprintf("%.1f",$var);
		$size=preg_replace('/\.0*$/mi',"",$size);
		return "$size $unit";
	}
}
__SITE::addFilter("filesizeformat",new __Filter_filesizeformat);

//first filter
class __Filter_first extends AbstractFilter{
	public static function render($args,$var,&$vars){
		if(is_array($var))
			foreach($var as $v)
				return $v;
		return substr($var,0,1);
	}
}
__SITE::addFilter("first",new __Filter_first);

//fix_ampersands filter
class __Filter_fix_ampersands extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return preg_replace('/\&(?![^ \r\n\t]+;)/mi',"&amp;",$var);
	}
}
__SITE::addFilter("fix_ampersands",new __Filter_fix_ampersands);

//floatformat filter
class __Filter_floatformat extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>floatformat</code>' expecting at least 1 argument, found 0.":null);
		$fmt="-1";
		if(count($args[0]))
			$fmt=sprintf("%d",$args[0]);
		$num=sprintf("%d",abs($fmt));
		$val=sprintf("%.".$num."f",$var);
		if($fmt<0)
			$val=preg_replace('/\.0*$/mi',"",$val);
		return $val;
	}
}
__SITE::addFilter("floatformat",new __Filter_floatformat);

//get_digit filter
class __Filter_get_digit extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>get_digit</code>' expecting at least 1 argument, found 0.":null);
		$str=$var;
		if(preg_match('/^[\d]*$/mi',"$var$args[0]")&&$args[0]>0)
			$str=substr(sprintf("%0".$args[0]."d",$var),-$args[0],1);
		return $str;
	}
}
__SITE::addFilter("get_digit",new __Filter_get_digit);

//join filter
class __Filter_join extends AbstractFilter{
	public static function render($args,$var,&$vars){
		$param="";
		if(count($args))
			$param=$args[0];
		return join($param,$var);
	}
}
__SITE::addFilter("join",new __Filter_join);

//last filter
class __Filter_last extends AbstractFilter{
	public static function render($args,$var,&$vars){
		if(is_array($var))
			foreach(array_reverse($var) as $v)
				return $v;
		return substr($var,-1);
	}
}
__SITE::addFilter("last",new __Filter_last);

//length filter
class __Filter_length extends AbstractFilter{
	public static function render($args,$var,&$vars){
		if(is_array($var))
			return count($var);
		return strlen($var);
	}
}
__SITE::addFilter("length",new __Filter_length);

//length_is filter
class __Filter_length_is extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>length_is</code>' expecting at least 1 argument, found 0.":null);
		if(is_array($var))
			return count($var)==$args[0];
		return strlen($var)==$args[0];
	}
}
__SITE::addFilter("length_is",new __Filter_length_is);

//linebreaks filter
class __Filter_linebreaks extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return "<p>".str_replace("\n","<br />",str_replace("\n\n","</p><p>",$var))."</p>";
	}
}
__SITE::addFilter("linebreaks",new __Filter_linebreaks);

//linebreaksbr filter
class __Filter_linebreaksbr extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return str_replace("\n","<br />",$var);
	}
}
__SITE::addFilter("linebreaksbr",new __Filter_linebreaksbr);

//linenumbers filter
class __Filter_linenumbers extends AbstractFilter{
	public static function render($args,$var,&$vars){
		$arr=explode("\n",$var);
		$out="";
		for($i=0;$i<count($arr);$i++)
			$out.=sprintf("%d. %s\n",$i+1,$arr[$i]);
		return $out;
	}
}
__SITE::addFilter("linenumbers",new __Filter_linenumbers);

//ljust filter
class __Filter_ljust extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!$count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>ljust</code>' expecting at least 1 argument, found 0.":null);
		return sprintf("%s%".max($args[0]-strlen($var),0)."s",$var,"");
	}
}
__SITE::addFilter("ljust",new __Filter_ljust);

//lower filter
class __Filter_lower extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return strtolower($var);
	}
}
__SITE::addFilter("lower",new __Filter_lower);

//make_list filter
class __Filter_make_list extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return str_split("","$var");
	}
}
__SITE::addFilter("make_list",new __Filter_make_list);

//phone2numeric filter
class __Filter_phone2numeric extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return
			preg_replace('/[A-C]/mi',"2",
			preg_replace('/[D-F]/mi',"3",
			preg_replace('/[G-I]/mi',"4",
			preg_replace('/[J-L]/mi',"5",
			preg_replace('/[M-O]/mi',"6",
			preg_replace('/[P-S]/mi',"7",
			preg_replace('/[T-V]/mi',"8",
			preg_replace('/[W-Z]/mi',"9",
			$var))))))));
	}
}
__SITE::addFilter("phone2numeric",new __Filter_phone2numeric);

//pluralize filter
class __Filter_pluralize extends AbstractFilter{
	public static function render($args,$var,&$vars){
		$suffices=array("","s");
		if(count($args)){
			if(preg_match('/[^,]*,.*/mi',$args[0]))
				$suffices=array_slice(explode($args[0]),0,2);
			else
				$suffices[1]=$args[0];
		}
		
		return $suffices[$var!=1];
	}
}
__SITE::addFilter("pluralize",new __Filter_pluralize);

//random filter
class __Filter_random extends AbstractFilter{
	public static function render($args,$var,&$vars){
		if(!is_array($var))
			$var=str_split("$var");
		return $var[rand(0,count($var)-1)];
	}
}
__SITE::addFilter("random",new __Filter_random);

//removetags filter
class __Filter_removetags extends AbstractFilter{
	public static function render($args,$var,&$vars){
		if(!count($args))
			return $var;
		$tags=explode(" ",$args[0]);
		$tagarr=array();
		foreach($tags as $t)
			$tagstr[]="(<[ ]*[/]?[ ]*{$t}[ ]*>)";
		return preg_replace('/'.join("|",$tagarr).'/m',"",$var);
	}
}
__SITE::addFilter("removetags",new __Filter_removetags);

//rjust filter
class __Filter_rjust extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!$count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>rjust</code>' expecting at least 1 argument, found 0.":null);
		return sprintf("%".max($args[0]-strlen($var),0)."s%s","",$var);
	}
}
__SITE::addFilter("rjust",new __Filter_rjust);

//safe filter
class __Filter_safe extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return $var;
	}
}
__SITE::addFilter("safe",new __Filter_safe);

//slice filter
class __Filter_slice extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>slice</code>' expecting at least 1 argument, found 0.":null);
		if(!is_array($var))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>slice</code>' expecting array argument, not '".$var."'.":null);
		preg_match_all('/(-?[\d]*\:-?[\d]*|-?[\d]+)/mi',$args[0],$bounds);
		$bounds=explode(":",$bounds[0][0]);
		while(count($bounds)<2)
			array_push($bounds,"");
		if($bounds[0]==="")
			$bounds[0]=0;
		else if($bounds[0]<0)
			$bounds[0]=count($var)-abs($bounds[0]);
		
		if($bounds[1]==="")
			$bounds[1]=count($var);
		else if($bounds[1]<0)
			$bounds[1]=count($var)-abs($bounds[1]);
		
		if($bounds[0]<=$bounds[1])
			return array_slice($var,$bounds[0],$bounds[1]-$bounds[0]);
		else
			return array(); //swap this and the following line to allow having a left bound higher than the right bound represent the subarray being reversed
			//return array_reverse(array_slice($var,$bounds[1],$bounds[0]-$bounds[1]));
	}
}
__SITE::addFilter("slice",new __Filter_slice);

//slugify filter
class __Filter_slugify extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return str_replace(" ","-",preg_replace('/[^\w ]/mi',"",trim($var)));
	}
}
__SITE::addFilter("slugify",new __Filter_slugify);

//stringformat filter
class __Filter_stringformat extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>stringformat</code>' expecting at least 1 argument, found 0.":null);
		return sprintf("%".substr($args[0],1,-1),$var);
	}
}
__SITE::addFilter("stringformat",new __Filter_stringformat);

//striptags filter
class __Filter_striptags extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return preg_replace('/<[^<>]*>/mi',"",$var);
	}
}
__SITE::addFilter("striptags",new __Filter_striptags);

//time filter
class __Filter_time extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return strftime(count($args)&&$args[0]!="\"TIME_FORMAT\""?substr($args[0],1,-1):$vars["TIME_FORMAT"],time());
	}
}
__SITE::addFilter("time",new __Filter_time);

//title filter
class __Filter_title extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return ucwords($var);
	}
}
__SITE::addFilter("title",new __Filter_title);

//truncatechars filter
class __Filter_truncatechars extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>truncatechars</code>' expecting at least 1 argument, found 0.":null);
		return strlen($var)>$args[0]?substr($var,0,$args[0]-3)."...":$var;
	}
}
__SITE::addFilter("truncatechars",new __Filter_truncatechars);

//truncatewords filter
class __Filter_truncatewords extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>truncatewords</code>' expecting at least 1 argument, found 0.":null);
		preg_match_all('/([^ ]+)/mi',$var,$arr);
		$words=$arr[0];
		if(count($words)>$args[0])
			$words=array_merge(array_slice($words,0,$args[0]),array("..."));
		return join(" ",$words);
	}
}
__SITE::addFilter("truncatewords",new __Filter_truncatewords);

//truncatewords_html filter
class __Filter_truncatewords_html extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>truncatewords_html</code>' expecting at least 1 argument, found 0.":null);
		preg_match_all('/(<[^ >]+>)|([ ]?[^ <]+[ ]?)/mi',$var,$arr);
		$tokens=$arr[0];
		$str="";
		$tag_stack=array();
		$wordcount=0;
		foreach($tokens as $tok){
			if(preg_match('/<[ ]*\/[ ]*([[:alpha:]]+)[ ]*>/mi',$tok,$name)){ //closing tag
				$name=$name[1];
				while(($prev=array_pop($tag_stack))!=$name){
					$str.="</$prev>";
					array_pop($tag_stack);
				}
				$str.="</$name>";
			}
			else if(preg_match('/<([ ]*[[:alpha:]]+)+[ ]*\/[ ]*>/mi',$tok)) //self-closing tag
				$str.=$tok;
			else if(preg_match('/<[ ]*([[:alpha:]]+)([ ]*[[:alpha:]]+)*[ ]*>/mi',$tok,$name)){ //opening tag
				$name=$name[1];
				$str.=$tok;
				array_push($tag_stack,$name);
			}
			else if($wordcount<$args[0]){ //not a tag at all
				$str.=$tok;
				$wordcount++;
			}
			else if($wordcount==$args[0]){
				$str.="...";
				break;
			}
		}
		while(count($tag_stack))
			$str.="</".array_pop($tag_stack).">";
		return $str;
	}
}
__SITE::addFilter("truncatewords_html",new __Filter_truncatewords_html);

//unordered_list filter
class __Filter_unordered_list extends AbstractFilter{
	private static function ul($arr,$tabs=0){
		$str="";
		foreach($arr as $k){
			$str.=str_repeat("\t",$tabs)."<li>";
			if(is_array($k))
				$str.="\n<ul>\n".self::ul($k,$tabs+1)."</ul>\n";
			else
				$str.=$k;
			$str.="</li>\n";
		}
		return $str;
	}
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!is_array($var))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>unordered_list</code>' expecting variable to be an array.":null);
		return self::ul($var);
	}
}
__SITE::addFilter("unordered_list",new __Filter_unordered_list);

//upper filter
class __Filter_upper extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return strtoupper($var);
	}
}
__SITE::addFilter("upper",new __Filter_upper);

//urlize filter
class __Filter_urlize extends AbstractFilter{
	public static function render($args,$var,&$vars){
		if(preg_match_all('/[ ]*(((http:\/\/|https:\/\/|www\.).*)|(([^:\/]*:\/\/)?[^\/]*\.(com|edu|gov|int|mil|net|org)))/mi',$var,$m)){
			foreach(array_unique($m[1]) as $url)
				$var=str_replace($url,"<a href=\"$url\" rel=\"nofollow\">$url</a>",$var);
		}
		return $var;
	}
}
__SITE::addFilter("urlize",new __Filter_urlize);

//urlizetrunc filter
class __Filter_urlizetrunc extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>urlizetrunc</code>' expecting at least 1 argument, found 0.":null);
		if(preg_match_all('/[ ]*(((http:\/\/|https:\/\/|www\.).*)|(([^:\/]*:\/\/)?[^\/]*\.(com|edu|gov|int|mil|net|org)))/mi',$var,$m)){
			foreach(array_unique($m[1]) as $url)
				$var=str_replace($url,"<a href=\"$url\" rel=\"nofollow\">".(strlen($url)>$args[0]?substr($url,0,$args[0]-3)."...":$url)."</a>",$var);
		}
		return $var;
	}
}
__SITE::addFilter("urlizetrunc",new __Filter_urlizetrunc);

//wordcount filter
class __Filter_wordcount extends AbstractFilter{
	public static function render($args,$var,&$vars){
		preg_match_all('/[[:word:]]+/mi',$var,$words);
		return count($words[0]);
	}
}
__SITE::addFilter("wordcount",new __Filter_wordcount);

//wordwrap filter
class __Filter_wordwrap extends AbstractFilter{
	public static function render($args,$var,&$vars){
		global $__DEBUG;
		if(!count($args))
			new INTERNAL_SERVER_ERROR($__DEBUG?"Filter '<code>wordwrap</code>' expecting at least 1 argument, found 0.":null);
		preg_match_all('/([[:^word:]]*[[:word:]]+[[:^word:]]*)/mi',$var,$words);
		$words=$words[0];
		$lines=array("");
		foreach($words as $w){
			if(strlen($lines[count($lines)-1])&&strlen($lines[count($lines)-1])+strlen($w)>$args[0])
				array_push($lines,$w);
			else
				$lines[count($lines)-1].=$w;
		}
		return join("\n",$lines);
	}
}
__SITE::addFilter("wordwrap",new __Filter_wordwrap);

//yesno filter
class __Filter_yesno extends AbstractFilter{
	public static function render($args,$var,&$vars){
		$mapping=array("yes","no","maybe");
		if(count($args)&&strlen($args[0]))
			$mapping=explode(",",$args[0]);
		if(count($mapping)<2)
			$mapping[1]="no";
		
		if($var!==False&&$var!==null) //yes-check
			return $mapping[0];
		if(count($mapping)>2&&$var===null) //null-check, if set
			return $mapping[2];
		return $mapping[1]; //defaults to no-check
	}
}
__SITE::addFilter("yesno",new __Filter_yesno);
/*

//title filter
class __Filter_title extends AbstractFilter{
	public static function render($args,$var,&$vars){
		return ucwords($var);
	}
}
__SITE::addFilter("title",new __Filter_title);
*/
?>
