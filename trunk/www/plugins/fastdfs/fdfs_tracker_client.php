<?php
/**
* Copyright (C) 2008 Happy Fish / YuQing
*
* This FastDFS php client may be copied only under the terms of the BSD License.
* Please visit the FastDFS Home Page http://www.csource.org/ for more detail.
**/

/**
* disconnect server
* @param $server assoc array
* @return none
*/
function fdfs_disconnect_server(&$server)
{
	if (is_resource($server['sock']))
	{
		fclose($server['sock']);
		unset($server['sock']);
	}
}

/**
* connect server
* @param $server assoc array
* @return 0 for success, none zero (errno) for fail
*/
function fdfs_connect_server(&$server)
{
	global $fdfs_network_timeout;

	if (isset($server['sock']) && is_resource($server['sock']))
	{
		return 0;
	}

	$sock = fsockopen($server['ip_addr'], $server['port'], $errno, $errstr, 
			$fdfs_network_timeout);
	if ($sock === false)
	{
		error_log("connect to ${server['ip_addr']}:${server['port']} " 
			. "fail, errno: $errno, error info: $errstr");
		return $errno;
	}

	stream_set_timeout($sock, $fdfs_network_timeout);
	$server['sock'] = $sock;

	return 0;
}

/**
* disconnect all tracker servers
* @param 
* @return none
*/
function tracker_close_all_connections()
{
	global $fdfs_tracker_servers;
	foreach ($fdfs_tracker_servers as $server)
	{
		fdfs_disconnect_server($server);
	}
}

/**
* get connection to a tracker server
* @param 
* @return a connected tracker server(assoc array) for success, false for fail
*/
function tracker_get_connection()
{
	global $fdfs_tracker_servers;
	global $fdfs_tracker_server_index;
	global $fdfs_tracker_server_count;

	if (count($fdfs_tracker_servers) == 0)
	{
		error_log("no tracker server!");
		return false;
	}

	$server = $fdfs_tracker_servers[$fdfs_tracker_server_index];

	if (is_resource($server['sock']) ||
		fdfs_connect_server($server) == 0)
	{
		$fdfs_tracker_server_index++;
		if ($fdfs_tracker_server_index >= $fdfs_tracker_server_count)
		{
			$fdfs_tracker_server_index = 0;
		}
		return $server;
	}

	for ($i=$fdfs_tracker_server_index+1; $i<$fdfs_tracker_server_count; $i++)
	{
		if (fdfs_connect_server($fdfs_tracker_servers[$i]) == 0)
		{
			return $fdfs_tracker_servers[$i];
		}
	}

	for ($i=0; $i<$fdfs_tracker_server_index; $i++)
	{
		if (fdfs_connect_server($fdfs_tracker_servers[$i]) == 0)
		{
			return $fdfs_tracker_servers[$i];
		}
	}

	return false;
}

/**
* query storage server to download file
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server return the storage server (assoc array, not connected)
* @param $group_name the group name of the storage server
* @param $filename the filename on the storage server
* @return 0 for success, none zero (errno) for fail
*/
function tracker_query_storage_update($tracker_server, &$storage_server, 
		$group_name, $filename)
{
	return tracker_do_query_storage($tracker_server, $storage_server, 
		TRACKER_PROTO_CMD_SERVICE_QUERY_UPDATE, $group_name, $filename);
}

/**
* query storage server to download file
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server return the storage server (assoc array, not connected)
* @param $group_name the group name of the storage server
* @param $filename the filename on the storage server
* @return 0 for success, none zero (errno) for fail
*/
function tracker_query_storage_fetch($tracker_server, &$storage_server, 
		$group_name, $filename)
{
	return tracker_do_query_storage($tracker_server, $storage_server, 
		TRACKER_PROTO_CMD_SERVICE_QUERY_FETCH, $group_name, $filename);
}

/**
* query storage server to download file
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server return the storage server (assoc array, not connected)
* @param $cmd the query command
* @param $group_name the group name of the storage server
* @param $filename the filename on the storage server
* @return 0 for success, none zero (errno) for fail
*/
function tracker_do_query_storage($tracker_server, &$storage_server, $cmd, 
		$group_name, $filename)
{
	$body = fdsf_pack_group_and_filename($group_name, $filename, $pkg_len);
	if (($result=fdfs_send_header($tracker_server, $pkg_len, 
			$cmd, 0)) != 0)
	{
		return $result;
	}

	if (fwrite($tracker_server['sock'], $body, $pkg_len) != $pkg_len)
	{
		error_log("tracker server: ${tracker_server['ip_addr']}:${tracker_server['port']}, "
			. "send data fail");
		return FDFS_EIO;
	}

	if (($result=fdfs_recv_response($tracker_server, 
		TRACKER_QUERY_STORAGE_FETCH_BODY_LEN, $in_buff, $in_bytes)) != 0)
	{
		return $result;
	}

	$storage_server = array('ip_addr' => trim(substr($in_buff, FDFS_GROUP_NAME_MAX_LEN, FDFS_IPADDR_SIZE-1)), 
				'port' => fdfs_buff2long(substr($in_buff, FDFS_GROUP_NAME_MAX_LEN+FDFS_IPADDR_SIZE-1)),
				'store_path_index' => 0,
				'sock' => -1);
	return 0;
}

/**
* query storage server to upload file
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server return the storage server (assoc array, not connected)
* @param $group_name the group to upload file to, can be empty
* @return 0 for success, none zero (errno) for fail
*/
function tracker_query_storage_store($tracker_server, &$storage_server, $group_name)
{
	if (empty($group_name))
	{
		$cmd = TRACKER_PROTO_CMD_SERVICE_QUERY_STORE_WITHOUT_GROUP;
		$out_len = 0;
	}
	else
	{
		$cmd = TRACKER_PROTO_CMD_SERVICE_QUERY_STORE_WITH_GROUP;
		$out_len = FDFS_GROUP_NAME_MAX_LEN;
	}

	if (($result=fdfs_send_header($tracker_server, $out_len, $cmd, 0)) != 0)
	{
		return $result;
	}

	if (!empty($group_name))
	{
		$groupname_len = strlen($group_name);
		if ($groupname_len >= FDFS_GROUP_NAME_MAX_LEN)
		{
			$body = substr($group_name, 0, FDFS_GROUP_NAME_MAX_LEN);
		}
		else
		{
			$body = $group_name;
			$body .= str_repeat("\000", FDFS_GROUP_NAME_MAX_LEN - $groupname_len);
		}

		if (fwrite($tracker_server['sock'], $body, FDFS_GROUP_NAME_MAX_LEN) != 
				FDFS_GROUP_NAME_MAX_LEN)
		{
			error_log("server: ${server['ip_addr']}:${server['port']}, "
				. "send data fail");
			return FDFS_EIO;
		}
	}

	if (($result=fdfs_recv_response($tracker_server, 
		TRACKER_QUERY_STORAGE_STORE_BODY_LEN, $in_buff, $in_bytes)) != 0)
	{
		return $result;
	}

	$storage_server = array(
			'ip_addr' => trim(substr($in_buff, 
				FDFS_GROUP_NAME_MAX_LEN, FDFS_IPADDR_SIZE-1)), 
			'port' => fdfs_buff2long(substr($in_buff, 
				FDFS_GROUP_NAME_MAX_LEN+FDFS_IPADDR_SIZE-1, FDFS_PROTO_PKG_LEN_SIZE)),
			'store_path_index' => ord(substr($in_buff, 
				FDFS_GROUP_NAME_MAX_LEN+FDFS_IPADDR_SIZE-1+FDFS_PROTO_PKG_LEN_SIZE)),
			'sock' => -1
		);

	return 0;
}
?>
