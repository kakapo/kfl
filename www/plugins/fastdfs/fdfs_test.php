<?php
/**
* Copyright (C) 2008 Happy Fish / YuQing
*
* This FastDFS php client may be copied only under the terms of the BSD License.
* Please visit the FastDFS Home Page http://www.csource.org/ for more detail.
**/

require 'fdfs_common.php';
require 'fdfs_tracker_client.php';
require 'fdfs_storage_client.php';

/**
* download file callback function for test
* @param $arg callback function extra argument
* @param $file_size the file size (file total bytes)
* @param $file_buff the file buff
* @param $buff_bytes buff bytes
* @return 0 for success, none zero (errno) for fail
*/
function write_file_callback($arg, $file_size, $file_buff, $buff_bytes)
{
	if (fwrite($arg, $file_buff, $buff_bytes) != $buff_bytes)
	{
		error_log("in write_file_callback fwrite fail");
		return FDFS_EIO;
	}

	return 0;
}

/*
$s = fdfs_long2buff(0x7FFE1234);
for ($i=0; $i<strlen($s); $i++)
{
	printf('%X ', ord(substr($s, $i, 1)));
}
echo "\n";
echo fdfs_buff2long($s) . "\n";
*/

/*
Before run this test program, you should modify fdfs_common.php,
config the global assoc array $fdfs_tracker_servers correctly.
*/

$tracker_server = tracker_get_connection();
if ($tracker_server == false)
{
	echo "tracker_get_connection fail\n";
	exit(1);
}

/*
$result = tracker_query_storage_store($tracker_server, $storage_server);
if ($result == 0)
{
	echo "storage server ${storage_server['ip_addr']}:${storage_server['port']}\n";
}
else
{
	echo "tracker_query_storage_store fail, errno: $result\n";
}
*/
$storage_server = null;

/*
change to your filename to upload, the path of the file
must in the php open_basedir
*/
$local_filename = 'fdfs_storage_client.php';

$group_name = '';   //you can specify the group to upload file to
$meta_list = array('width' => 1024, 'height' => 768, 'color' => '#c0c0c0');

$result = storage_upload_by_filename($tracker_server, $storage_server,
                        $local_filename, $meta_list,
                        $group_name, $remote_filename);
if ($result == 0)
{
	echo "group_name=$group_name, remote_filename=$remote_filename\n";
}
else
{
	echo "storage_upload_by_filename fail, result=$result\n";
}

$file_buff = file_get_contents($local_filename);
if ($file_buff != false)
{
	$file_size = strlen($file_buff);
	$group_name = '';  //you can specify the group to upload file to
	$result = storage_upload_by_filebuff($tracker_server, $storage_server,
		$file_buff, $file_size, "php", $meta_list,
		$group_name, $remote_filename);
	if ($result == 0)
	{
		echo "group_name=$group_name, remote_filename=$remote_filename\n";
	}
	else
	{
		echo "storage_upload_by_filename fail, result=$result\n";
	}
}

$result = tracker_query_storage_fetch($tracker_server, $storage_server,
                $group_name, $remote_filename);
if ($result == 0)
{
	echo "storage server ${storage_server['ip_addr']}:${storage_server['port']}\n";
}
else
{
	echo "tracker_query_storage_fetch fail, errno: $result\n";
}

sleep(1);  //wait for file replication
$local_filename = 'ttt.dat';  //the path of the file must in the php open_basedir
$result = storage_download_file_to_file($tracker_server, $storage_server,
                $group_name, $remote_filename, $local_filename, $file_size);
if ($result == 0)
{
	echo "download file to file success, file size: $file_size\n";
}
else
{
	echo "storage_download_file_to_file fail, errno: $result\n";
}

$result = storage_download_file_to_buff($tracker_server, $storage_server,
                $group_name, $remote_filename, $file_buff, $file_size);
if ($result == 0)
{
	echo "download file to buff success, file size: $file_size" . ", buff size:" . strlen($file_buff) . "\n";
	file_put_contents(str_replace('/', '_', $remote_filename), $file_buff);
}
else
{
	echo "storage_download_file_to_buff fail, errno: $result\n";
}


$local_filename = str_replace('/', '-', $remote_filename);
$fp = fopen($local_filename, 'wb');
if ($fp === false)
{
	error_log("open file \"$local_filename\" to write fail");
}
else
{
	$result = storage_download_file_ex($tracker_server, $storage_server,
                $group_name, $remote_filename, 'write_file_callback', $fp, $file_size);
	if ($result == 0)
	{
		echo "download file to file success, file size: $file_size\n";
	}
	else
	{
		echo "storage_download_file_to_file fail, errno: $result\n";
	}
	fclose($fp);
}

$meta_list = array('font' => 'Aris', 'Author' => 'Tom');
$result = storage_set_metadata($tracker_server, $storage_server, $group_name,
		$remote_filename, $meta_list, STORAGE_SET_METADATA_FLAG_MERGE);
echo "set metadata result: $result\n";

sleep(1); //wait for file replication
$result = storage_get_metadata($tracker_server, $storage_server, $group_name,
		$remote_filename, $meta_list);
if ($result == 0)
{
	var_dump($meta_list);
}
else
{
	echo "storage_get_metadata fail, errno: $result\n";
}

$result = storage_delete_file($tracker_server, $storage_server, $group_name, $remote_filename);
echo "delete file result: $result\n";

fdfs_quit($tracker_server);
tracker_close_all_connections();

?>
