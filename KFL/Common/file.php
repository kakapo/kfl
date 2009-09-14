<?php
/*-------------------------------文件扩展函数-----------------------------------

//文件名: file.func.php
//创建时间: 2006-05-15
//最后更新时间: 2006-06-25
//代码维护人: GTZHAO
//版本: 2.0
//描述: 文件上传类

----------------------------------------------------------------------------*/

//作用:模拟 清理路径中多余的斜杠 以及将所有 斜杠转换为 /
//参数:$path 需要清理的路径字串
//返回:清理后的路径字串

function path_clean($path)
{
	$path = str_replace('://','__SLASH_SLASH_COLON__',$path);
	$path = str_replace('\\','/',$path);
	$path = preg_replace('/\/\.$/','/',str_replace('/./','/',preg_replace('/\/{2,}/','/',$path)));
	$path = str_replace('__SLASH_SLASH_COLON__','://',$path);

	return $path;
}

//作用:模拟 parse_path 但其解决解析带中文文件名路径无法正常获得文件名的问题
//参数:$path 需要解析的路径
//返回:数组 下标分别为 dirname,filename,basename,extension;
function parse_path($path)
{
	$path = path_clean($path);
	$pathinfo = pathinfo($path);
	$pathinfo['filename'] = preg_replace('/^'.preg_quote($pathinfo['dirname'],'/').'\//' ,'', $path);
	$pathinfo['basename'] = preg_replace('/\.'.$pathinfo['extension'].'$/' ,'', $pathinfo['filename']);
	return $pathinfo;
}

//作用:容量单位转换
//参数:$size 容量及单位 b(默认)|kb|mb|gb|tb
//参数:$tounit 输出单位 b|kb|mb|gb|tb|auto 当值为 auto(默认) 的时候按照容量自适应生成
//返回:数值 + 单位
function size_unit_convert($size,$tounit='auto')
{
	preg_match("/([0-9]+)\s*([a-zA-Z]*)/i",$size,$size_array);
	$size = $size_array[1];
	$from_unit = strtolower($size_array[2]);
	$from_unit = $from_unit ? $from_unit : 'b' ;
	$tounit = strtolower($tounit);

	$unit['b'] = 1;
	$unit['kb'] = 1024;
	$unit['mb'] = 1048576;
	$unit['gb'] = 1073741824;
	$unit['tb'] = 1099511627776;
	$size_bit = $size * $unit[$from_unit];

	if($tounit=='auto')
	{
		if(($convert_size = $size_bit) < 1024)
		{
			return round($convert_size,2).' Bytes';
		}

		if(($convert_size = $size_bit/1024) < 1024)
		{
			return round($convert_size,2).' KB';
		}

		if(($convert_size = $size_bit/1048576) < 1024)
		{
			return round($convert_size,2).' MB';
		}

		if(($convert_size = $size_bit/1073741824) < 1024)
		{
			return round($convert_size,2).' GB';
		}

		if(($convert_size = $size_bit/1099511627776) < 1024)
		{
			return round($convert_size).' TB';
		}
	}
	else
	{
		return round($size_bit / $unit[$tounit],2).strtoupper($tounit);
	}
}

//作用:创建目录 可创建路径中包含的所有目录 并将其设置成指定模式
//参数:$path 创建目录路径
//参数:$mode 目录模式(权限)
//返回:true|false
function create_dir($path,$mode = 0777)
{
	preg_match_all('/([^\\\|\/]+)[\\\|\/]*/',$path,$array_dir);
	$os = explode(' ', php_uname());
	if(strtolower($os[0])!='windows')
	{
		$array_dir['1']['0'] = '/'.$array_dir['1']['0'];
	}
	$dir = '';
	foreach($array_dir['1'] as $temp_dir)
	{
		$dir.=$temp_dir.'/';
		if(!file_exists($dir))
		{
			@mkdir($dir,$mode);
		}
	}

	if(!file_exists($path))
	{
		return false;
	}

	return true;
}

//作用:删除目录 扩展函数 可删除目录中所有文件包括目录
//参数:$path 要删除的文件/目录
//参数:$self true(包含指定目录)|false(不包括指定目录只删除指定目录下的)
//参数:$private_level 私有的内部递归参数用于判断是否到顶层
//参数:$types 文件类型，例如.jpg|.gif
//返回:true|false
function del($path, $self = false, $private_level = 0,$types='')
{
	
	$list_dir = list_dir($path);//注此处使用了自定义扩展函数

	if(is_array($list_dir))
	{
		foreach($list_dir as $row)
		{
			if($row['type'] == 'dir')
			{
				if(!del($row['path'],'1',$private_level+1))
				{
					return false;
				}
			}
			else
			{
				if($types==''){
					@unlink($row['path']);
				}else{
					$items = explode("|",$types);
					foreach ($items as $type){
						if(stripos($row['filename'],$type)){
							if(is_file($row['path'])) @unlink($row['path']);
						}
					}
				}
			}
		}
	}
	else
	{
		@unlink($path);
	}

	if($private_level!=0 || $self)
	{
		if(is_dir($path) && !@rmdir($path))
		{
			return false;
		}
	}
	return true;
}


//作用:遍历文件夹
//参数:$path 要遍历的文件夹
//参数:$type 传回文件数组的类型 all|dir|file
//返回:返回文件夹内容数组 | false 文件夹不存在
function list_dir($path,$type = 'all')
{
	$list = array();
	if(!$dir = @dir($path))
	{
		return false;
	}
	$i = 0;
	while (false !== ($filename = $dir->read()))
	{
		if (preg_match("/^(\.{1,2}|\.svn)$/ism",$filename)) {	continue; }

		$fileinfo = stat($dir->path.'/'.$filename);
		$pathinfo = pathinfo($filename);
		$filetype = filetype($dir->path.'/'.$filename);
		//if($filetype == 'dir') list_dir($dir->path,$list[$i]);
		if(($type == 'file' && $filetype == 'dir') && $type != 'all'){	continue;	}
		if(($type == 'dir' && $filetype == 'file') && $type != 'all'){	continue;	}
		$list[$i]['id'] = $filename.uniqid("_");
		$list[$i]['type'] = $filetype;
		$list[$i]['name'] = mb_convert_encoding($filename, "UTF-8", "GBK");
		$list[$i]['basename'] = $pathinfo['basename'];
		$list[$i]['extension'] = isset($pathinfo['extension'])?$pathinfo['extension']:'';
		$list[$i]['time'] = date ("Y-m-d H:i:s", $fileinfo['mtime']);
		$list[$i]['size'] = size_unit_convert($fileinfo['size']);
		if($filetype=='dir') $list[$i]['folders'] = array(array("_reference"=>''));
		$list[$i]['dir'] = $dir->path;
		$list[$i]['path'] = urlencode(encrypt($dir->path.'/'.$filename));

		$i++;
	}
	$dir->close();
	//@array_multisort($list,SORT_DESC,$list);//排序 如果搜索全部类型则先列数组
	return $list;
}

function list_all_dir($path,&$tree){
	if(!is_dir($path)) return false;
	if( !$dir = dir($path))
	{
		return false;
	}
	$i = 0;
	
	while (false !== ($filename = $dir->read()))
	{
		$t = array();
		if (preg_match("/^(\.{1,2}|\.svn)$/ism",$filename)) {	continue; }
		$filetype = filetype($dir->path.'/'.$filename);
		
		if($filetype == 'dir'){
			list_all_dir($dir->path.'/'.$filename,$t['folders']);
		}
		$fileinfo = stat($dir->path.'/'.$filename);
		$pathinfo = pathinfo($filename);
		
		$t['path'] = urlencode(encrypt($dir->path.'/'.$filename));
		
		$filename = mb_convert_encoding($filename, "UTF-8", "GBK");
		$t['id'] = $filename.uniqid("_");
		$t['filetype'] = $filetype;
		$t['name'] = $filename;
		
		$t['basename'] = $pathinfo['basename'];
		$t['extension'] = isset($pathinfo['extension'])?$pathinfo['extension']:'';
		$t['time'] = date ("Y-m-d H:i:s.", $fileinfo['mtime']);
		$t['size'] = size_unit_convert($fileinfo['size']);	
		$t['dir'] = urlencode($dir->path);
		
		$tree[$i] = $t;
		$i++;
		@array_multisort($tree, SORT_REGULAR,SORT_DESC   );//排序 如果搜索全部类型则先列数组
	}
	
	$dir->close();
	
}

//作用:检测指定目录大小
//参数:$path 要检测的目录
//返回:返回文件夹所占用的空间的字节数
function dir_size($path)
{
	static $dir_size;
	$list_dir = list_dir($path);

	if(is_array($list_dir))
	{
		foreach($list_dir as $row)
		{
			if($row['type']=='dir')
			{
				dir_size($row['path']);
			}
			else
			{
				$dir_size += filesize($row['path']);
			}
		}
	}

	return $dir_size;
}


//作用:完整读取文件 替代 file_get_contents 的方案 如果 file_get_contents 函数存在则执行 file_get_contents
//参数:$path 要读取的文件路径
//返回:返回读取的文件内容
function read_file($path)
{
	if (!($file = @fopen($path,'rb')))
	{
		return false;
	}
	$content="";
	@flock($file, LOCK_SH);
	while($line=fread($file,2048))
	{
		$content .= $line;
	}

	@flock($file, LOCK_UN);
	fclose($file);

	return $content;
}

//作用:将指定内容写入文件
//参数:$content 要写入的内容
//参数:$path 文件存放路径
//参数:$mode 写入模式 默认 wb
//返回:true|false 是否写入成功
function write_file($content,$path,$mode='wb')
{
	if(!is_dir(dirname($path)))
	{
		@create_dir(dirname($path));
	}

	if(!($file = fopen($path,$mode)))
	{
		return false;
	}

	file_put_contents($path,$content,LOCK_EX);
	return true;
}


//作用:复制文件或目录下的所有文件到指定目录
function icopy($path, $dir)
{
	if(!file_exists($path))
	{
		return false;
	}

	$tmpPath = parse_path($path);


	if(!is_dir($path))
	{
		create_dir($dir);
		if(!copy($path, $dir.'/'.$tmpPath['filename']))
		{
			return false;
		}
	}
	else
	{
		create_dir($dir);
		foreach((array)list_dir($path) as $lineArray)
		{
			if($lineArray['type'] == 'dir')
			{
				icopy($lineArray['path'], $dir.'/'.$lineArray['filename']);
			}
			else
			{
				icopy($lineArray['path'], $dir);
			}
		}
	}

	return true;
}

?>