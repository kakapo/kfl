<?php
/**
* Copyright (C) 2008 Happy Fish / YuQing
*
* This FastDFS php client may be copied only under the terms of the BSD License.
* Please visit the FastDFS Home Page http://www.csource.org/ for more detail.
**/

/**
* get storage readable connection, if the $storage_server is connected, 
* do not connect again
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the storage server (assoc array), can be null
* @param $group_name the group name of storage server
* @param $filename the filename on the storage server
* @param $new_connection make a new connection flag, 
         true means create a new connection
* @return 0 for success, none zero (errno) for fail
*/
function storage_get_read_connection($tracker_server, &$storage_server, 
		$group_name, $filename, &$new_connection)
{
	return storage_get_connection($tracker_server, $storage_server, 
		TRACKER_PROTO_CMD_SERVICE_QUERY_FETCH, $group_name, $filename, 
		$new_connection);
}

/**
* get storage updatable connection, if the $storage_server is connected, 
* do not connect again
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the storage server (assoc array), can be null
* @param $group_name the group name of storage server
* @param $filename the filename on the storage server
* @param $new_connection make a new connection flag, 
         true means create a new connection
* @return 0 for success, none zero (errno) for fail
*/
function storage_get_update_connection($tracker_server, &$storage_server, 
		$group_name, $filename, &$new_connection)
{
	return storage_get_connection($tracker_server, $storage_server, 
		TRACKER_PROTO_CMD_SERVICE_QUERY_UPDATE, $group_name, $filename, 
		$new_connection);
}

/**
* get storage readable connection, if the $storage_server is connected, 
* do not connect again
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the storage server (assoc array), can be null
* @param $cmd the query command
* @param $group_name the group name of storage server
* @param $filename the filename on the storage server
* @param $new_connection make a new connection flag, 
         true means create a new connection
* @return 0 for success, none zero (errno) for fail
*/
function storage_get_connection($tracker_server, &$storage_server, 
		$cmd, $group_name, $filename, &$new_connection)
{
	if (!$storage_server)
	{
		if ($cmd == TRACKER_PROTO_CMD_SERVICE_QUERY_FETCH)
		{
			if (($result=tracker_query_storage_fetch($tracker_server, 
		       	         $storage_server, $group_name, $filename)) != 0)
			{
				return $result;
			}
		}
		else
		{
			if (($result=tracker_query_storage_update($tracker_server, 
		       	         $storage_server, $group_name, $filename)) != 0)
			{
				return $result;
			}
		}

		if (($result=fdfs_connect_server($storage_server)) != 0)
		{
			return $result;
		}

		$new_connection = true;
	}
	else
	{
		if (isset($storage_server['sock']) && $storage_server['sock'] >= 0)
		{
			$new_connection = false;
		}
		else
		{
			if (($result=fdfs_connect_server($storage_server)) != 0)
			{
				return $result;
			}

			$new_connection = true;
		}
	}

	return 0;
}

/**
* get storage writable connection, if the $storage_server is connected, 
* do not connect again
* @param $tracker_server the connected tracker server (assoc array)
* @param $group_name the group to upload file to, can be empty
* @param $storage_server the storage server (assoc array), can be null
* @param $new_connection make a new connection flag, 
         true means create a new connection
* @return 0 for success, none zero (errno) for fail
*/
function storage_get_write_connection($tracker_server, $group_name, &$storage_server, 
		&$new_connection)
{
	if (!$storage_server)
	{
		if (($result=tracker_query_storage_store($tracker_server, 
		                $storage_server, $group_name)) != 0)
		{
			return $result;
		}

		if (($result=fdfs_connect_server($storage_server)) != 0)
		{
			return $result;
		}

		$new_connection = true;
	}
	else
	{
		if (isset($storage_server['sock']) && $storage_server['sock'] >= 0)
		{
			$new_connection = false;
		}
		else
		{
			if (($result=fdfs_connect_server($storage_server)) != 0)
			{
				return $result;
			}

			$new_connection = true;
		}
	}

	return 0;
}

/**
* get metadata from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $group_name: the group name of storage server
* @param $filename: filename on storage server
* @param $meta_list return metadata assoc array
* @return 0 for success, none zero (errno) for fail
*/
function storage_get_metadata($tracker_server, $storage_server, 
			$group_name, $filename, &$meta_list)
{
	if (($result=storage_get_update_connection($tracker_server, 
		$storage_server, $group_name, $filename, $new_connection)) != 0)
	{
		return $result;
	}

	while (1)
	{
	/**
	send pkg format:
	FDFS_GROUP_NAME_MAX_LEN bytes: group_name
	remain bytes: filename
	**/

	$body = fdsf_pack_group_and_filename($group_name, $filename, $pkg_len);
	if (($result=fdfs_send_header($storage_server, $pkg_len, 
			STORAGE_PROTO_CMD_GET_METADATA, 0)) != 0)
	{
		break;
	}

	if (fwrite($storage_server['sock'], $body, $pkg_len) != $pkg_len)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "send data fail");
		$result = FDFS_EIO;
		break;
	}

	if (($result=fdfs_recv_response($storage_server, -1, $file_buff, $file_size)) != 0)
	{
		break;
	}

	if ($file_size == 0)
	{
		$meta_list = array();
		break;
	}

	$meta_list = fdfs_split_metadata($file_buff);
	break;
	}

	if ($new_connection)
	{
		fdfs_quit($storage_server);
		fdfs_disconnect_server($storage_server);
	}

	return $result;
}

/**
* delete file from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $group_name: the group name of storage server
* @param $filename: filename on storage server
* @return 0 for success, none zero (errno) for fail
*/
function storage_delete_file($tracker_server, $storage_server, 
			$group_name, $filename)
{
	if (($result=storage_get_update_connection($tracker_server, 
		$storage_server, $group_name, $filename, 
		$new_connection)) != 0)
	{
		return $result;
	}

	while (1)
	{
	/**
	send pkg format:
	FDFS_GROUP_NAME_MAX_LEN bytes: group_name
	remain bytes: filename
	**/

	$body = fdsf_pack_group_and_filename($group_name, $filename, $pkg_len);
	if (($result=fdfs_send_header($storage_server, $pkg_len, 
			STORAGE_PROTO_CMD_DELETE_FILE, 0)) != 0)
	{
		break;
	}

	if (fwrite($storage_server['sock'], $body, $pkg_len) != $pkg_len)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "send data fail");
		$result = FDFS_EIO;
		break;
	}

	$result = fdfs_recv_response($storage_server, 0, $in_buff, $in_bytes);
	break;
	}

	if ($new_connection)
	{
		fdfs_quit($storage_server);
		fdfs_disconnect_server($storage_server);
	}

	return $result;
}

/**
* download file from the storage server, internal function, do not use directly
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $group_name the group name of storage server
* @param $remote_filename filename on storage server
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @param $download_type FDFS_DOWNLOAD_TO_FILE for filename (write to file), 
*        FDFS_DOWNLOAD_TO_BUFF for file buff (write to buff)
*        FDFS_DOWNLOAD_TO_CALLBACK for callback
* @param $file_buff filename when $download_type is FDFS_DOWNLOAD_TO_FILE, 
*                   file buff when $download_type is FDFS_DOWNLOAD_TO_BUFF
*                   callback function name when $download_type is FDFS_DOWNLOAD_TO_CALLBACK
* @param $arg callback function extra argument
* @param $file_size return the file size (bytes)
* @return 0 for success, none zero (errno) for fail
*/
function storage_do_download_file($tracker_server, $storage_server, $download_type,
		$group_name, $remote_filename, $file_offset, $download_bytes, 
		&$file_buff, $arg, &$file_size)
{
	$file_size = 0;
	if (($result=storage_get_read_connection($tracker_server, 
		$storage_server, $group_name, $remote_filename, 
		$new_connection)) != 0)
	{
		return $result;
	}

	while (1)
	{
	/**
	send pkg format:
	8 bytes: file start offset
	8 bytes: download bytes 
	FDFS_GROUP_NAME_MAX_LEN bytes: group_name
	remain bytes: filename
	**/
	$body = fdfs_long2buff($file_offset);
	$body .= fdfs_long2buff($download_bytes);
	$body .= fdsf_pack_group_and_filename($group_name, $remote_filename, $pkg_len);
	$pkg_len += 16;
	if (($result=fdfs_send_header($storage_server, $pkg_len, 
			STORAGE_PROTO_CMD_DOWNLOAD_FILE, 0)) != 0)
	{
		break;
	}

	if (fwrite($storage_server['sock'], $body, $pkg_len) != $pkg_len)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "send data fail");
		$result = FDFS_EIO;
		break;
	}

	if ($download_type == FDFS_DOWNLOAD_TO_FILE)
	{
		$result = fdfs_recv_header($storage_server, $in_bytes);
		if ($result != 0)
		{
			break;
		}

		$result = fdfs_recv_file($storage_server, $in_bytes, $file_buff);
		if ($result != 0)
		{
			break;
		}
	}
	else if ($download_type == FDFS_DOWNLOAD_TO_BUFF)
	{
		if (($result=fdfs_recv_response($storage_server, -1, $file_buff, $in_bytes)) != 0)
		{
			break;
		}
	}
	else
	{
		$result = fdfs_recv_header($storage_server, $in_bytes);
		if ($result != 0)
		{
			break;
		}

		$callback = $file_buff;
		$remain_bytes = $in_bytes;
		while ($remain_bytes > 0)
		{
			$s = fread($storage_server['sock'], $remain_bytes > 2048 ? 2048 : $remain_bytes);
			if (!$s)
			{
				error_log("server: ${storage_server['ip_addr']}:${storage_server['port']}, "
					. "fread fail");
				$result = FDFS_EIO;
				break;
			}

			$len = strlen($s);
			if (($result=call_user_func($callback, $arg, $in_bytes, $s, $len)) !== 0)
			{
				break;
			}

			$remain_bytes -= $len;
		}

		if ($remain_bytes != 0)
		{
			break;
		}
	}

	$file_size = $in_bytes;
	break;
	}

	if ($new_connection)
	{
		fdfs_quit($storage_server);
		fdfs_disconnect_server($storage_server);
	}

	return $result;
}

/**
* download file to file from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $group_name the group name of storage server
* @param $remote_filename filename on storage server
* @param $local_filename  local filename to write
*        note: the path of the file must in the php open_basedir
* @param $file_size return the file size (bytes)
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @return 0 for success, none zero (errno) for fail
*/
function storage_download_file_to_file($tracker_server, $storage_server, 
		$group_name, $remote_filename, $local_filename, &$file_size, 
		$file_offset = 0, $download_bytes = 0)
{
	return storage_do_download_file($tracker_server, $storage_server, 
		FDFS_DOWNLOAD_TO_FILE, $group_name, $remote_filename, 
		$file_offset, $download_bytes, $local_filename, null, $file_size);
}

/**
* download file to buff from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $group_name the group name of storage server
* @param $remote_filename filename on storage server
* @param $file_buff return the file buff (string)
* @param $file_size return the file size (bytes)
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @return 0 for success, none zero (errno) for fail
*/
function storage_download_file_to_buff($tracker_server, $storage_server, 
		$group_name, $remote_filename, &$file_buff, &$file_size, 
		$file_offset = 0, $download_bytes = 0)
{
	return storage_do_download_file($tracker_server, $storage_server, 
			FDFS_DOWNLOAD_TO_BUFF, $group_name, $remote_filename, 
			$file_offset, $download_bytes, $file_buff, null, $file_size);
}

/**
* download file to buff from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $group_name the group name of storage server
* @param $remote_filename filename on storage server
* @param $callback callback function name
* @param $arg callback function extra argument
* @param $file_size return the file size (bytes)
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @return 0 for success, none zero (errno) for fail
*/
function storage_download_file_ex($tracker_server, $storage_server, 
		$group_name, $remote_filename, $callback, $arg, &$file_size, 
		$file_offset = 0, $download_bytes = 0)
{
	return storage_do_download_file($tracker_server, $storage_server, 
		FDFS_DOWNLOAD_TO_CALLBACK, $group_name, $remote_filename, 
		$file_offset, $download_bytes, $callback, $arg, $file_size);
}

/**
* upload file to the storage server, internal function, do not use directly
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $bFilename true for filename (read from file), 
* @param $file_buff filename when $bFilename is true, else file buff 
* @param $file_size the file size (bytes)
* @param $file_ext_name the file ext name (not including dot)
* @param $meta_list metadata assoc array (key value pair array)
* @param $group_name specify the group to upload file to, 
                     return the group name of the storage server
* @param $remote_filename return the filename on the storage server
* @return 0 for success, none zero (errno) for fail
*/
function storage_do_upload_file($tracker_server, $storage_server, 
			$bFilename, $file_buff, $file_size, $file_ext_name,
			$meta_list, &$group_name, &$remote_filename)
{
	$remote_filename = '';

	if (($result=storage_get_write_connection($tracker_server, $group_name,
		$storage_server, $new_connection)) != 0)
	{
		return $result;
	}

	$group_name = '';

	while (1)
	{
	/**
	1 byte: store path index
	8 bytes: meta data bytes
	8 bytes: file size
	FDFS_FILE_EXT_NAME_MAX_LEN bytes: file ext name
	meta data bytes: each meta data seperated by \x01,
			 name and value seperated by \x02
	file size bytes: file content
	**/
	if ($meta_list)
	{
		$meta_buff = fdfs_pack_metadata($meta_list);
		$meta_bytes = strlen($meta_buff);
	}
	else
	{
		$meta_buff = '';
		$meta_bytes = 0;
	}

	$pkg_len = 1 + 2 * FDFS_PROTO_PKG_LEN_SIZE + FDFS_FILE_EXT_NAME_MAX_LEN
		     + $meta_bytes + $file_size;
	if (($result=fdfs_send_header($storage_server, $pkg_len, 
			STORAGE_PROTO_CMD_UPLOAD_FILE, 0)) != 0)
	{
		break;
	}

	$body = chr(isset($storage_server['store_path_index']) ? $storage_server['store_path_index'] : 0)
		 . fdfs_long2buff($meta_bytes);
	$body .= fdfs_long2buff($file_size);
	if ($file_ext_name !== null)
	{
		$ext_name_len = strlen($file_ext_name);
		if ($ext_name_len > FDFS_FILE_EXT_NAME_MAX_LEN)
		{
			$ext_name_len = FDFS_FILE_EXT_NAME_MAX_LEN;
			$file_ext_name = substr($file_ext_name, 0, $ext_name_len);
		}
	}
	else
	{
		$file_ext_name = '';
	}
	$body .= str_pad($file_ext_name, FDFS_FILE_EXT_NAME_MAX_LEN, chr(0), STR_PAD_RIGHT);

	if (fwrite($storage_server['sock'], $body, 1 + 2 * FDFS_PROTO_PKG_LEN_SIZE + FDFS_FILE_EXT_NAME_MAX_LEN) 
		!= 1 + 2 * FDFS_PROTO_PKG_LEN_SIZE + FDFS_FILE_EXT_NAME_MAX_LEN)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "send data fail");
		$result = FDFS_EIO;
		break;
	}

	if ($meta_bytes > 0 && 
		fwrite($storage_server['sock'], $meta_buff, $meta_bytes) != $meta_bytes)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "send data fail");
		$result = FDFS_EIO;
		break;
	}

	if ($bFilename)
	{
		if (($result=fdfs_send_file($storage_server, $file_buff,
			$file_size)) != 0)
		{
			break;
		}
	}
	else
	{
		if ($file_size> 0 && fwrite($storage_server['sock'], 
			$file_buff, $file_size) != $file_size)
		{
			error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
				. "send data fail");
			$result = FDFS_EIO;
			break;
		}
	}

	if (($result=fdfs_recv_response($storage_server, 
		-1, $in_buff, $in_bytes)) != 0)
	{
		break;
	}

	if ($in_bytes <= FDFS_GROUP_NAME_MAX_LEN)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "length: $in_bytes is invalid, should > " . FDFS_GROUP_NAME_MAX_LEN);
		$result = FDFS_EINVAL;
		break;
	}

	$group_name = trim(substr($in_buff, 0, FDFS_GROUP_NAME_MAX_LEN));
	$remote_filename = substr($in_buff, FDFS_GROUP_NAME_MAX_LEN);

	break;
	}

	if ($new_connection)
	{
		fdfs_quit($storage_server);
		fdfs_disconnect_server($storage_server);
	}

	return $result;
}

/**
* upload file by filename to the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $local_filename local file name to upload 
*        note: the path of the file must in the php open_basedir
* @param $meta_list metadata assoc array (key value pair array)
* @param $group_name return the group name of the storage server
* @param $remote_filename return the filename on the storage server
* @return 0 for success, none zero (errno) for fail
*/
function storage_upload_by_filename($tracker_server, $storage_server, 
			$local_filename, $meta_list, 
			&$group_name, &$remote_filename)

{
	if (($attr=stat($local_filename)) === false)
	{
		$group_name = '';
		$remote_filename = '';
		return FDFS_EIO;
	}

	if (!is_file($local_filename))
	{
		$group_name = '';
		$remote_filename = '';
		return FDFS_EINVAL;
	}

	$pos = strrpos($local_filename, '.');
	if ($pos === false)
	{
		$file_ext_name = '';
	}
	else
	{
		$file_ext_name = substr($local_filename, $pos + 1);
	}

	return storage_do_upload_file($tracker_server, $storage_server, 
			true, $local_filename, $attr['size'], $file_ext_name, 
			$meta_list, $group_name, $remote_filename);
}

/**
* upload file by buff to the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_buff the file content to upload
* @param $file_size the file content length
* @param $file_ext_name the file ext name (not including dot)
* @param $meta_list metadata assoc array (key value pair array)
* @param $group_name return the group name of the storage server
* @param $remote_filename return the filename on the storage server
* @return 0 for success, none zero (errno) for fail
*/
function storage_upload_by_filebuff($tracker_server, $storage_server, 
			$file_buff, $file_size, $file_ext_name, $meta_list, 
			&$group_name, &$remote_filename)
{
	return storage_do_upload_file($tracker_server, $storage_server, 
			false, $file_buff, $file_size, $file_ext_name,
			$meta_list, $group_name, $remote_filename);
}

/**
* change the metadata of the file on the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $group_name the group name of storage server
* @param $filename the filename on the storage server
* @param $meta_list metadata assoc array (key value pair array)
* @param $op_flag flag
*        STORAGE_SET_METADATA_FLAG_OVERWRITE('O') for overwrite all old metadata
*        STORAGE_SET_METADATA_FLAG_MERGE('M') for merge, insert when the meta 
*                                            item not exist, otherwise update it
* @return 0 for success, none zero (errno) for fail
*/
function storage_set_metadata($tracker_server, $storage_server, 
			$group_name, $filename, 
			$meta_list, $op_flag)
{
	if (($result=storage_get_update_connection($tracker_server, 
		$storage_server, $group_name, $filename, 
		$new_connection)) != 0)
	{
		return $result;
	}

	/**
	the request pkg body format:
	8 bytes: filename length
	8 bytes: meta data size
	1 bytes: operation flag,
              'O' for overwrite all old metadata
	      'M' for merge, insert when the meta item not exist, 
                  otherwise update it
	FDFS_GROUP_NAME_MAX_LEN bytes: group_name
	filename
	meta data bytes: each meta data seperated by \x01,
                 name and value seperated by \x02
	**/
	while (1)
	{
	$filename_len = strlen($filename);

	if ($meta_list)
	{
		$meta_buff = fdfs_pack_metadata($meta_list);
		$meta_bytes = strlen($meta_buff);
	}
	else
	{
		$meta_buff = '';
		$meta_bytes = 0;
	}

	$body = fdfs_long2buff($filename_len);
	$body .= fdfs_long2buff($meta_bytes);
	$body .= $op_flag;

	$body .= fdsf_pack_group_and_filename($group_name, $filename, $pkg_len);
	$pkg_len += 2 * FDFS_PROTO_PKG_LEN_SIZE + 1;

	if (($result=fdfs_send_header($storage_server, $pkg_len + $meta_bytes, 
			STORAGE_PROTO_CMD_SET_METADATA, 0)) != 0)
	{
		break;
	}

	if (fwrite($storage_server['sock'], $body, $pkg_len) != $pkg_len)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "send data fail");
		$result = FDFS_EIO;
		break;
	}

	if ($meta_bytes > 0 
		&& fwrite($storage_server['sock'], $meta_buff, $meta_bytes) != $meta_bytes)
	{
		error_log("storage server: ${storage_server['ip_addr']}:${storage_server['port']}, "
			. "send data fail");
		$result = FDFS_EIO;
		break;
	}

	$result = fdfs_recv_response($storage_server, 0, $in_buff, $in_bytes);
	break;
	}

	if ($new_connection)
	{
		fdfs_quit($storage_server);
		fdfs_disconnect_server($storage_server);
	}

	return $result;
}
?>
