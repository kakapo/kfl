<?php
/**
* Copyright (C) 2008 Happy Fish / YuQing
*
* This FastDFS php client may be copied only under the terms of the BSD License.
* Please visit the FastDFS Home Page http://www.csource.org/ for more detail.
**/

/* FastDFS protocol constant */
define('FDFS_PROTO_CMD_QUIT',  82);
define('TRACKER_PROTO_CMD_SERVER_LIST_GROUP',  91);
define('TRACKER_PROTO_CMD_SERVER_LIST_STORAGE',  92);
define('TRACKER_PROTO_CMD_SERVER_RESP',  90);

define('TRACKER_PROTO_CMD_SERVICE_QUERY_STORE_WITHOUT_GROUP',  101);
define('TRACKER_PROTO_CMD_SERVICE_QUERY_FETCH', 102);
define('TRACKER_PROTO_CMD_SERVICE_QUERY_UPDATE', 103);
define('TRACKER_PROTO_CMD_SERVICE_QUERY_STORE_WITH_GROUP', 104);
define('TRACKER_PROTO_CMD_SERVICE_RESP',  100);

define('STORAGE_PROTO_CMD_UPLOAD_FILE',  11);
define('STORAGE_PROTO_CMD_DELETE_FILE',  12);
define('STORAGE_PROTO_CMD_SET_METADATA',  13);
define('STORAGE_PROTO_CMD_DOWNLOAD_FILE',  14);
define('STORAGE_PROTO_CMD_GET_METADATA',  15);
define('STORAGE_PROTO_CMD_RESP',  10);

/**
 * for overwrite all old metadata
 */
define('STORAGE_SET_METADATA_FLAG_OVERWRITE', 'O');

/**
 * for replace, insert when the meta item not exist, otherwise update it
 */
define('STORAGE_SET_METADATA_FLAG_MERGE', 'M');

define('FDFS_PROTO_PKG_LEN_SIZE',  8);
define('FDFS_PROTO_CMD_SIZE',  1);
define('FDFS_GROUP_NAME_MAX_LEN',  16);
define('FDFS_IPADDR_SIZE',  16);
define('FDFS_RECORD_SEPERATOR',  "\001");
define('FDFS_FIELD_SEPERATOR',   "\002");

define('FDFS_PROTO_HEADER_LENGTH',  FDFS_PROTO_PKG_LEN_SIZE+2);
define('TRACKER_QUERY_STORAGE_FETCH_BODY_LEN',  FDFS_GROUP_NAME_MAX_LEN
		+ FDFS_IPADDR_SIZE - 1 + FDFS_PROTO_PKG_LEN_SIZE);
define('TRACKER_QUERY_STORAGE_STORE_BODY_LEN',  FDFS_GROUP_NAME_MAX_LEN
		+ FDFS_IPADDR_SIZE + FDFS_PROTO_PKG_LEN_SIZE);

define('FDFS_PROTO_HEADER_CMD_INDEX',  FDFS_PROTO_PKG_LEN_SIZE);
define('FDFS_PROTO_HEADER_STATUS_INDEX',  FDFS_PROTO_PKG_LEN_SIZE+1);

/* errno define, other errnos please see /usr/include/errno.h in UNIX system */
define('FDFS_EINVAL', 22);
define('FDFS_EIO', 5);

define('FDFS_DOWNLOAD_TO_BUFF',       1);
define('FDFS_DOWNLOAD_TO_FILE',       2);
define('FDFS_DOWNLOAD_TO_CALLBACK',   3);

define('FDFS_FILE_ID_SEPERATOR',   '/');
define('FDFS_FILE_EXT_NAME_MAX_LEN',   5);


/**
* change me to correct tracker server list, assoc array element:
*    ip_addr: the ip address or hostname of the tracker server
*    port:    the port of the tracker server
*    sock:    the socket handle to the tracker server, should init to -1 or null
*/
$fdfs_network_timeout = 30;  //seconds
$fdfs_tracker_servers = array();
$fdfs_tracker_server_index = 0;
$fdfs_tracker_servers[0] = array(
		'ip_addr' => '192.168.1.5',
		'port' => 22122,
		'sock' => -1);
//$fdfs_tracker_servers[1] = array(
//		'ip_addr' => '10.62.164.84',
//		'port' => 22122,
//		'sock' => -1);

$fdfs_tracker_server_count = count($fdfs_tracker_servers);

/**
* recv package header
* @param $server connected tracker or storage server (assoc array)
* @param $in_bytes return the package length
* @return 0 for success, none zero (errno) for fail
*/
function fdfs_recv_header($server, &$in_bytes)
{
	$pkg_len = fread($server['sock'], FDFS_PROTO_PKG_LEN_SIZE);
	$cmd = fread($server['sock'], 1);
	$status = fread($server['sock'], 1);
	if (ord($status) != 0)
	{
		$in_bytes = 0;
		return ord($status);
	}

	$in_bytes = fdfs_buff2long($pkg_len);
	if ($in_bytes < 0)
	{
		error_log("server: ${server['ip_addr']}:${server['port']}, "
			. "recv package size $in_bytes is not correct");
		$in_bytes = 0;
		return FDFS_EINVAL;
	}

	return 0;
}

/**
* recv response package from server
* @param $server connected tracker or storage server (assoc array)
* @param $expect_pkg_len expect body length, < 0 for uncertain
* @param $buff return the package buff
* @param $in_bytes return the package length
* @return 0 for success, none zero (errno) for fail
*/
function fdfs_recv_response($server, $expect_pkg_len, &$buff, &$in_bytes)
{
	$result = fdfs_recv_header($server, $in_bytes);
	if ($result != 0)
	{
		return $result;
	}

	if ($expect_pkg_len >= 0 && $expect_pkg_len != $in_bytes)
	{
		error_log("server: ${server['ip_addr']}:${server['port']}, "
			. "pkg length: $in_bytes is not correct, "
			. "expect pkg length: $expect_pkg_len");
		$in_bytes = 0;
		return FDFS_EINVAL;
	}

	if ($in_bytes == 0)
	{
		return 0;
	}

	$buff = '';
	$remain_bytes = $in_bytes;
	while ($remain_bytes > 0)
	{
		$s = fread($server['sock'], $remain_bytes);
		if (!$s)
		{
			error_log("server: ${server['ip_addr']}:${server['port']}, "
				. "fread fail");
			return FDFS_EIO;
		}

		$buff .= $s;
		$remain_bytes -= strlen($s);
	}

	return 0;
}

/**
* recv response package from server
* @param $server connected storage server (assoc array)
* @param $file_size  file size (bytes)
* @param $local_filename  local filename to write
* @return 0 for success, none zero (errno) for fail
*/
function fdfs_recv_file($server, $file_size, $local_filename)
{
	$fp = fopen($local_filename, 'wb');
	if ($fp === false)
	{
		error_log("open file \"$local_filename\" to write fail");
		return FDFS_EIO;
	}

	$sock = $server['sock'];
	$result = 0;
	$remain_bytes = $file_size;
	while ($remain_bytes > 0)
	{
		if ($remain_bytes > 16 * 1024)
		{
			$read_bytes = 16 * 1024;
		}
		else
		{
			$read_bytes = $remain_bytes;
		}

		$buff = fread($sock, $read_bytes);
		if (!$buff)
		{
			error_log("server: ${server['ip_addr']}:${server['port']}, "
				. "fread fail");
			$result = FDFS_EIO;
			break;
		}

		$bytes = strlen($buff);
		if (fwrite($fp, $buff, $bytes) != $bytes)
		{
			error_log("fwrite to \"$local_filename\" fail");
			$result = FDFS_EIO;
			break;
		}

		$remain_bytes -= $bytes;
	}

	fclose($fp);
	return $result;
}

/**
* send file to server
* @param $server connected storage server (assoc array)
* @param $local_filename  local filename to write
* @param $file_size  file size (bytes)
* @return 0 for success, none zero (errno) for fail
*/
function fdfs_send_file($server, $local_filename, $file_size)
{
	$fp = fopen($local_filename, 'rb');
	if ($fp === false)
	{
		error_log("open $local_filename to read fail");
		return FDFS_EIO;
	}

	$sock = $server['sock'];
	$result = 0;
	$remain_bytes = $file_size;
	while ($remain_bytes > 0)
	{
		if ($remain_bytes > 64 * 1024)
		{
			$read_bytes = 64 * 1024;
		}
		else
		{
			$read_bytes = $remain_bytes;
		}

		$buff = fread($fp, $read_bytes);
		if ($buff === false)
		{
			error_log("fread fail, file: $local_filename");
			$result = FDFS_EIO;
			break;
		}

		if (fwrite($sock, $buff, $read_bytes) != $read_bytes)
		{
			error_log("server: ${server['ip_addr']}:${server['port']}, "
				. "fwrite fail");
			$result = FDFS_EIO;
			break;
		}

		$remain_bytes -= $read_bytes;
	}

	fclose($fp);
	return $result;
}

/**
* long to big-endian buff, because PHP does not support 64 bits integer, we use 32 bits
* @param $n the integer number
* @return 8 bytes big-endian buff (string)
*/
function fdfs_long2buff($n)
{
	/*
	return sprintf('%c%c%c%c%c%c%c%c'
			, ($n >> 56) & 0xFF
			, ($n >> 48) & 0xFF
			, ($n >> 40) & 0xFF
			, ($n >> 32) & 0xFF
			, ($n >> 24) & 0xFF
			, ($n >> 16) & 0xFF
			, ($n >> 8) & 0xFF
			, $n & 0xFF);
	*/

	return "\000\000\000\000" . pack('N', $n);
}

/**
* big-endian buff to long, because PHP does not support 64 bits integer, we use 32 bits
* @param $buff 8 bytes big-endian buff
* @return the 32 bits integer number
*/
function fdfs_buff2long($buff)
{
	$arr = unpack('N', substr($buff, 4, 4));
	return $arr['1'];
}

/**
* pack package header
* @param $pkg_len the package length
* @param $cmd the command
* @param $status the status
* @return package header string
*/
function fdfs_pack_header($pkg_len, $cmd, $status)
{
	return fdfs_long2buff($pkg_len) . sprintf('%c%c', $cmd, $status);
}

/**
* send package header
* @param $server the connected server (assoc array)
* @param $pkg_len the package length
* @param $cmd the command
* @param $status the status
* @return 0 for success, none zero (errno) for fail
*/
function fdfs_send_header($server, $pkg_len, $cmd, $status)
{
	$header = fdfs_pack_header($pkg_len, $cmd, $status);
	if (fwrite($server['sock'], $header, FDFS_PROTO_HEADER_LENGTH) !=
		FDFS_PROTO_HEADER_LENGTH)
	{
		error_log("server: ${server['ip_addr']}:${server['port']}, "
			. "send data fail");
		return FDFS_EIO;
	}

	return 0;
}

/**
* send QUIT command to server
* @param $server the connected server (assoc array)
* @return 0 for success, none zero (errno) for fail
*/
function fdfs_quit($server)
{
	return fdfs_send_header($server, 0, FDFS_PROTO_CMD_QUIT, 0);
}

/**
* pack group_name and filename
* @param $group_name the group name of the storage server
* @param $filename the filename on the storage server
* @param $len return the packed length
* @return packed string
*/
function fdsf_pack_group_and_filename($group_name, $filename, &$len)
{
	$filename_len = strlen($filename);
	$groupname_len = strlen($group_name);
	$len = FDFS_GROUP_NAME_MAX_LEN + $filename_len;

	if ($groupname_len > FDFS_GROUP_NAME_MAX_LEN)
	{
		$body = substr($group_name, 0, FDFS_GROUP_NAME_MAX_LEN);
	}
	else
	{
		$body = $group_name;
		$body .= str_repeat("\000", FDFS_GROUP_NAME_MAX_LEN - $groupname_len);
	}

	return $body . $filename;
}

/**
* pack metadata array to string
* @param $meta_list metadata assoc array
* @return packed metadata string
*/
function fdfs_pack_metadata($meta_list)
{
	if (!is_array($meta_list) || count($meta_list) == 0)
	{
		return '';
	}

	$s = '';
	$i = 0;
	foreach($meta_list as $key => $value)
	{
		if ($i > 0)
		{
			$s .= FDFS_RECORD_SEPERATOR;
		}
		$s .= $key . FDFS_FIELD_SEPERATOR . $value;
		$i++;
	}

	return $s;
}

/**
* pack split metadata string to assoc array
* @param $metadata metadata string
* @return metadata array
*/
function fdfs_split_metadata($metadata)
{
	$meta_list = array();

	$rows = explode(FDFS_RECORD_SEPERATOR, $metadata);
	foreach ($rows as $r)
	{
		$cols = explode(FDFS_FIELD_SEPERATOR, $r, 2);
		if (count($cols) == 2)
		{
			$meta_list[$cols[0]] = $cols[1];
		}
	}

	return $meta_list;
}

?>
