<?php

/*-------------------------------字符串扩展函数-----------------------------------

//文件名: string.func.php
//创建时间: 2006-05-15
//最后更新时间: 2006-06-25
//代码维护人: GTZHAO
//作用:验证指定字符串在 用符号连接的 字符串集中出现次数 (严格比对)
str_exist_num

//作用:生成随机字符串
random_str

----------------------------------------------------------------------------------*/

//作用:验证指定字符串在 用符号连接的 字符串集中出现次数 (严格比对)
//参数:$str 需要检查出现次数的子串
//参数:$strs 用于比对的字符串
//参数:$sign 分隔符 默认为 ','
//返回:true|false

function find_in_set($str,$strs,$sign=',')
{
	$str = str_replace('.','\.',$str);
	preg_match_all("/^".$str."$|^".$str."$sign|$sign".$str."$sign|$sign".$str."$/", $strs,$str_array);
	return count($str_array[0]);
}

//获得局部内容
function str_part($rule = '', $data = '', $sign = '{DATA}')
{
	if(!strstr($rule, $sign))
	{
		return false;
	}

	$rule=explode($sign, $rule);
	
	//滤除头部
	$data = strstr($data, $rule[0]);
	var_dump($data);
	$data = substr($data, strlen($rule[0]));

	//滤除尾部
	$place = strpos($data, $rule[1]);
	return substr($data, 0, $place);
}

//作用:连接字符串函数当指定变量中不为空时则使用连接符号
//参数:$str 需要检查出现次数的子串
//参数:$strs 用于比对的字符串
//参数:$sign 分隔符 默认为 ','
//返回:true|false

function str_concat($str_left,$str_right,$sign=',')
{
	if(is_numeric($str_left) || $str_left!='')
	{
		$str_left .= $sign;
	}

	$str_left .= $str_right;
	return count($str_left);
}

//作用:生成随机字符串
//参数:$length 生成字符的长度
//参数:$type 生成随机字符所包含的字符 num|str|str_low|str_num|str_num_low
//返回:随机数
function random_str($length='5',$type='num')
{
	$type = strtolower($type);
	mt_srand((double)microtime()*1000000);
	switch($type)
	{
		case 'num':
			$strtemp='0123456789';
			for($i=0;$i<$length;$i++)
			{
				$irand.=substr($strtemp,mt_rand(0,9),1);
			}
			break;
		case 'str':
			$strtemp='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			for($i=0;$i<$length;$i++)
			{
				$irand.=substr($strtemp,mt_rand(0,51),1);
			}
			break;
		case 'str_low':
			$strtemp='abcdefghijklmnopqrstuvwxyz';
			for($i=0;$i<$length;$i++)
			{
				$irand.=substr($strtemp,mt_rand(0,25),1);
			}
			break;
		case 'str_num':
			$strtemp='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			for($i=0;$i<$length;$i++)
			{
				$irand.=substr($strtemp,mt_rand(0,61),1);
			}
			break;
		case 'str_num_low':
			$irand = substr(md5(mt_rand(0,65536)),0,$length);
			break;
	}

	return $irand;
}


//替换变量
function change_val($content,$var,$value)
{
	return preg_replace("/\\$".$var."\s*\=\s*[\"'].*?[\"']\s*;/is",'$'."{$var} = '{$value}';",$content);
}

//作用:URL参数字串 替换及增加
//参数:$link URL地址 如 "/asdfas/asdfs.php?a=5&b=6&d=7";
//参数:$parameter_array 参数数组如 array ('a'=> 2,'b'=> 3,'c'=> 4,'d'=> 7);
//返回:修改过参数的URL /asdfas/asdfs.php?a=2&b=3&c=4&d=7
function link_parameter($link,$parameter_array = array())
{

	$link_info = parse_url($link);

	foreach(explode('&',$link_info['query']) as $value)
	{
		$urval=explode('=',$value);
		$key = trim($urval[0]);
		$val = $urval[1];
		$link_parameter_array[$key] = $val;
	}

	$link_parameter_array = array_merge($link_parameter_array,$parameter_array);

    foreach($link_parameter_array as $key => $val)
	{
		if(trim($key)!='' && $val!='')
		{
			$urlstr .= $sign."$key=".(!preg_match('/%\w{2}/i',$val) ? urlencode($val) : $val );
			$sign='&';
		}
	}

	preg_match('/[^\?]*/i',$link,$link_array);

	$urlstr = $urlstr!='' ? '?'.$urlstr : $urlstr;
	$urlstr = $link_array[0].$urlstr;

	return $urlstr;
}


//将数组中数据读取用指定分隔符分割分割链接
function implode_array($array,$sign_x=",")
{
	//如果当不想让连接好的字符串中包含 单引号 可以使用 str_replace("'","",$array);
	if(is_array($array) && count($array))
	{
		foreach($array as $s)
		{
			$strings.="$sign_i'$s'";
			$sign_i=$sign_x;
		}
	}
	return $strings;
}

//中文字符串截取函数
function cn_substr($string,$sublen,$exp="")
{
    if($sublen>=strlen($string))
    {
        return $string;
    }
    $s="";
    for($i=0;$i<$sublen;$i++)
    {
        if(ord($string{$i})>127) 
        {
            $s.=$string{$i}.$string{++$i};
            continue;
        }else{
            $s.=$string{$i};
            continue;
        } 
    }
    return $s.$exp;
}


//字符串安全转换函数
function str_convert($string)
{

	if(is_array($string)) 
	{
		foreach($string as $key => $val) 
		{
			$string[$key] = safe_convert($val);
		}
	} 
	else 
	{
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',str_replace(array('&',' ', '"', '\'', '<', '>'), array( '&amp;','&nbsp;', '&quot;', '&#039;', '&lt;', '&gt;'), $string));
	}
	
	return $string;
}

//扩展 addslashes 作用为对数组进行 addslashes

function iaddslashes($value)
{
	if(is_array($value))
	{
		foreach($value as $key => $val) 
		{
			$value[$key] = iaddslashes($val);
		}
	}
	else 
	{
		if(!is_object($value))
		{
			$value = addslashes($value);
		}
	}
	
	return $value;
}

//扩展 stripslashes 作用为对数组进行 stripslashes
function istripslashes($value)
{
	if(is_array($value)) 
	{
		foreach($value as $key => $val) 
		{
			$value[$key] = istripslashes($val);
		}
	}
	else 
	{
		if(!is_object($value))
		{
			$value = stripslashes($value);
		}
	}
	
	return $value;
}

// Array & String 转 String
function to_string($val,$sign = ',')
{
	if(is_array($val))
	{
		$val = trim(implode($sign,$val),$sign);
	}
	else
	{
		$val = trim($val,',');
	}

	return $val;
}

// String 转 Array & String
function to_array($val, $sign = ',')
{
	if(!is_array($val))
	{
		$val = explode($sign,trim($val,$sign));
	}

	return $val;
}

//判断是否为空
function is_blank($value)
{
	$value = trim($value);
	if(is_numeric($value) || $value)
	{
		return false;
	}

	return true;
}

//显示数组
function dump($val, $type = false)
{
	echo '<pre>';
	if($type)
	{
		var_dump($val);
	}
	else
	{
		print_r($val);
	}
	echo '</pre>';
}

//修复HTML缺少标签
function html_fixed($html)
{
	$ignore_tag = ',br,meta,base,hr,!,img,input,';

	//去除注释
	$html = preg_replace('/<\!--.*?-->/is','',$html);

	//获得起始标签
	preg_match_all ("/<([^>]+)>/i",$html,$html_arr);

	foreach($html_arr[1] as $rs)
	{

		if(preg_match('/^\//',$rs) || preg_match('/\/$/',$rs))
		{
			continue;
		}

		$rs = preg_replace('/ +.+/','',$rs);

		if(@preg_match('/,'.$rs.',/i',$ignore_tag))
		{
			continue;
		}

		$start_tag[$rs]++;
	}

	//获得结束标签
	preg_match_all ("/<\/([^>]+)>/i",$html,$html_arr);

	foreach($html_arr[1] as $rs)
	{
		$rs = trim($rs);
		$end_tag[$rs]++;
	}

	foreach((array)$start_tag as $key => $count)
	{
		if(($sum = $count - $end_tag[$key]) > 0 )
		{
			for($i = 0; $i < $sum; $i++)
			{
				$html .= "</{$key}>";
			}
		}
	}

	foreach((array)$end_tag as $key => $count)
	{
		if(($sum = $count - $start_tag[$key]) > 0 )
		{
			for($i = 0; $i < $sum; $i++)
			{
				$html = "<{$key}>{$html}";
			}
		}
	}

	return $html;
}

function istr_replace($search, $replace, $value)
{
	if(is_array($value)) 
	{
		foreach($value as $key => $val) 
		{
			$value[$key] = istr_replace($search, $replace, $val);
		}
	}
	else 
	{
		if(!is_object($value))
		{
			$value = str_replace($search, $replace, $value);
		}
	}
	
	return $value;
}

//删除所选择标签
function strip_selected_tags($text, $tags)
{
   $tags = to_array($tags);
   foreach ($tags as $tag){
       while(preg_match('/<'.$tag.'(|\W[^>]*)>(.*)<\/'. $tag .'>/iusU', $text, $found)){
           $text = str_replace($found[0],$found[2],$text);
       }
   }

   return preg_replace('/(<('.join('|',$tags).')(|\W.*)\/>)/iusU', '', $text);
}

//获得第一个正则分析数据 implode
function preg_match_all_implode($rule, $glue, $content, $depth = 1)
{
	preg_match_all($rule, $content, $data);

	return implode($glue, $data[$depth]);
}

//如果 json_encode 不存在则用PEAR的 Services_JSON 实现
if(!function_exists('json_encode'))
{
	require_once('Services/JSON.php');
	$GLOBALS['SERVICES_JSON_OBJECT'] = new Services_JSON();

	function json_encode($data)
	{
		return $GLOBALS['SERVICES_JSON_OBJECT']->encode($data);
	}

	function json_decode($data)
	{
		return $GLOBALS['SERVICES_JSON_OBJECT']->decode($data);
	}
}
?>