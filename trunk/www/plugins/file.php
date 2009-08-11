<?php
/*-------------------------------文件扩展函数-----------------------------------

//文件名: file.func.php
//创建时间: 2006-05-15
//最后更新时间: 2006-06-25
//代码维护人: GTZHAO
//版本: 2.0
//描述: 文件上传类

//作用:模拟 parse_path 但其解决解析带中文文件名路径无法正常获得文件名的问题
parse_path

//作用:容量单位转换 
size_unit_convert

//作用:创建目录 可创建路径中包含的所有目录 并将其设置成指定模式
create_dir

//作用:删除目录 扩展函数 可删除目录中所有文件包括目录
del

//作用:遍历文件夹
list_dir

//作用:列出指定文件夹下所有文件
list_dir_file

//作用:检测指定目录大小
dir_size

//作用:完整读取文件 替代 file_get_contents 的方案 如果 file_get_contents 函数存在则执行 file_get_contents
read_file

//作用:将指定内容写入文件
write_file

//作用:判断目录是否可写
is_dir_writable

//作用:复制指定指定文件到指定目录及指定名称
copy_file
----------------------------------------------------------------------------*/



//作用:删除目录 扩展函数 可删除目录中所有文件包括目录
//参数:$path 要删除的文件/目录
//参数:$self true(包含指定目录)|false(不包括指定目录只删除指定目录下的)
//参数:$private_level 私有的内部递归参数用于判断是否到顶层
//返回:true|false
function del($path, $self = false, $private_level = 0)
{
	$list_dir = list_dir($path);//注此处使用了自定义扩展函数

	if(is_array($list_dir))
	{
		foreach($list_dir as $row)
		{
			if($row['type'] == 'dir')
			{
				if(!del($row['path'],'1',$root+1))
				{
					return false;
				}
			}
			else
			{
				@unlink($row['path']);
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
	if(!$dir = @dir($path))
	{
		return false;
	}
	$i = 0;
	while (false !== ($filename = $dir->read())) 
	{
		if (eregi("^\.{1,2}$",$filename)) {	continue; }

		$fileinfo = stat($dir->path.'/'.$filename);
		$pathinfo = pathinfo($filename);
		$filetype = filetype($dir->path.'/'.$filename);

		if(($type == 'file' && $filetype == 'dir') && $type != 'all'){	continue;	}			
		if(($type == 'dir' && $filetype == 'file') && $type != 'all'){	continue;	}

		$list[$i]['type'] = $filetype;
		$list[$i]['filename'] = $filename;
		$list[$i]['basename'] = basename($pathinfo['basename'],'.'.$pathinfo['extension']);
		$list[$i]['extension'] = $pathinfo['extension'];
		$list[$i]['time'] = $fileinfo['mtime'];
		$list[$i]['size'] = $fileinfo['size'];
		$list[$i]['dir'] = path_clean($dir->path);
		$list[$i]['path'] = path_clean($dir->path.'/'.$filename);

		$i++;
	}
	$dir->close();
	@array_multisort($list,SORT_ASC,$list);//排序 如果搜索全部类型则先列数组
	return $list;
}


//作用:列出指定文件夹下所有文件
//参数:$path 要遍历的文件夹
//返回:返回文件夹内容数组 | false 文件夹不存在
function list_dir_file($path,$i = 0)
{
	if(!$dir = @dir($path))
	{
		return false;
	}
	
	$list_array = list_dir($path);

	$list_array = is_array($list_array) ? $list_array : array() ;

	foreach ($list_array as $value)
	{
		if($value['type'] == 'dir')
		{
			list_dir_file($value['path'],$i+1);
		}
		else
		{
			$GLOBALS['LIST_ALL_FILE_INSIDE_ARRAY'][] = $value;
		}
	}

	if($i == 0)
	{
		$return_array = $GLOBALS['LIST_ALL_FILE_INSIDE_ARRAY'];
		unset($GLOBALS['LIST_ALL_FILE_INSIDE_ARRAY']);
	}

	return $return_array;
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

	@flock($file, LOCK_EX + LOCK_NB);
	fwrite($file, $content);
	@flock($file, LOCK_UN);
	fclose($file);

	return true;
}

//作用:判断目录是否可写
//参数:$dir 要检测的目录
//返回:true|false 是否写入成功
function is_dir_writable($dir) 
{
	if(!is_dir($dir)) 
	{
		create_dir($dir);
	}

	if(is_dir($dir))
	{
		if(write_file('test',$dir.'/test.test')) 
		{
			del($dir.'/test.test');
			$writable = true;
		}
		else 
		{
			del($dir.'/test.test');
			$writable = false;
		}
	}

	return $writable;
}

//作用:复制指定指定文件到指定目录及指定名称
//参数:$path 文件来源路径 可以为URL
//参数:$dir 文件存放目录 可选参数
//参数:$filename 新文件名称 可选参数 当此选项为空时默认与来源文件名相同
function copy_file($path,$dir = false,$filename = false)
{
	$file = parse_path($path);

	if(!($content = read_file($path)))
	{
		return false;
	}

	return write_file($content,( $dir ? $dir.'/' : '').($filename ? $filename : $file['filename']));
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

function dataPost($fields , $post_url)
{
	//$fields_string = '';
	//foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&' ; }
	//rtrim($fields_string ,'&') ; 
	//open connection
print_r($fields);
die;
	    $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $post_url ); 
   curl_setopt($ch, CURLOPT_POST, 1 );
   curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($ch) ; 

	curl_close($ch) ; 
		return $result;
}

?>