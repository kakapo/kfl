<?php
/**
* Copyright (C) 2008 Happy Fish / YuQing
*
* This FastDFS php client may be copied only under the terms of the BSD License.
* Please visit the FastDFS Home Page http://www.csource.org/ for more detail.
**/

require 'fdfs_storage_client.php';

/**
* get metadata from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_id: the file id (including group name and filename)
* @param $meta_list return metadata assoc array
* @return 0 for success, none zero (errno) for fail
*/
function storage_get_metadata1($tracker_server, $storage_server, 
			$file_id, &$meta_list)
{
	$parts = explode(FDFS_FILE_ID_SEPERATOR, $file_id, 2);
	if (count($parts) != 2)
	{
		return FDFS_EINVAL;
	}

	return storage_get_metadata($tracker_server, $storage_server,
                        $parts[0], $parts[1], $meta_list);
}

/**
* delete file from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_id: the file id (including group name and filename)
* @return 0 for success, none zero (errno) for fail
*/
function storage_delete_file1($tracker_server, $storage_server, $file_id)
{
	$parts = explode(FDFS_FILE_ID_SEPERATOR, $file_id, 2);
	if (count($parts) != 2)
	{
		return FDFS_EINVAL;
	}

	return storage_delete_file($tracker_server, $storage_server, 
			$parts[0], $parts[1]);
}

/**
* download file from the storage server, internal function, do not use directly
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $download_type FDFS_DOWNLOAD_TO_FILE for filename (write to file), 
*        FDFS_DOWNLOAD_TO_BUFF for file buff (write to buff)
*        FDFS_DOWNLOAD_TO_CALLBACK for callback
* @param $file_id: the file id (including group name and filename)
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @param $file_buff filename when $download_type is FDFS_DOWNLOAD_TO_FILE, 
*                   file buff when $download_type is FDFS_DOWNLOAD_TO_BUFF
*                   callback function name when $download_type is FDFS_DOWNLOAD_TO_CALLBACK
* @param $arg callback function extra argument
* @param $file_size return the file size (bytes)
* @return 0 for success, none zero (errno) for fail
*/
function storage_do_download_file1($tracker_server, $storage_server, $download_type,
		$file_id, $file_offset, $download_bytes, &$file_buff, $arg, &$file_size)
{
	$parts = explode(FDFS_FILE_ID_SEPERATOR, $file_id, 2);
	if (count($parts) != 2)
	{
		return FDFS_EINVAL;
	}

	return storage_do_download_file($tracker_server, $storage_server, $download_type,
                $parts[0], $parts[1], $file_offset, $download_bytes, $file_buff, $arg, $file_size);
}

/**
* download file to file from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_id: the file id (including group name and filename)
* @param $local_filename  local filename to write
*        note: the path of the file must in the php open_basedir
* @param $file_size return the file size (bytes)
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @return 0 for success, none zero (errno) for fail
*/
function storage_download_file_to_file1($tracker_server, $storage_server, 
		$file_id, $local_filename, &$file_size, 
		$file_offset = 0, $download_bytes = 0)
{
	return storage_do_download_file1($tracker_server, $storage_server, 
			FDFS_DOWNLOAD_TO_FILE, $file_id, 
			$file_offset, $download_bytes, $local_filename, null, $file_size);
}

/**
* download file to buff from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_id: the file id (including group name and filename)
* @param $file_buff return the file buff (string)
* @param $file_size return the file size (bytes)
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @return 0 for success, none zero (errno) for fail
*/
function storage_download_file_to_buff1($tracker_server, $storage_server, 
		$file_id, &$file_buff, &$file_size, 
		$file_offset = 0, $download_bytes = 0)
{
	return storage_do_download_file1($tracker_server, $storage_server, 
			FDFS_DOWNLOAD_TO_BUFF, $file_id, 
			$file_offset, $download_bytes, $file_buff, null, $file_size);
}

/**
* download file from the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_id: the file id (including group name and filename)
* @param $callback callback function name
* @param $arg callback function extra argument
* @param $file_size return the file size (bytes)
* @param $file_offset the start offset of the file
* @param $download_bytes download bytes, 0 for remain bytes from offset
* @return 0 for success, none zero (errno) for fail
*/
function storage_download_file_ex1($tracker_server, $storage_server, 
		$file_id, $callback, $arg, &$file_size, 
		$file_offset = 0, $download_bytes = 0)
{
	return storage_do_download_file1($tracker_server, $storage_server, 
		FDFS_DOWNLOAD_TO_CALLBACK, $file_id, 
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
* @param $file_id return the file id (including group name and filename)
* @param $remote_filename return the filename on the storage server
* @param $group_name specify the group to upload file to
* @return 0 for success, none zero (errno) for fail
*/
function storage_do_upload_file1($tracker_server, $storage_server, 
			$bFilename, $file_buff, $file_size, $file_ext_name,
			$meta_list, &$file_id, $group_name)
{
	if (($result=storage_do_upload_file($tracker_server, $storage_server, 
			$bFilename, $file_buff, $file_size, $file_ext_name,
			$meta_list, $group_name, $remote_filename)) != 0)
	{
		return $result;
	}

	$file_id = $group_name . FDFS_FILE_ID_SEPERATOR .  $remote_filename;
	return 0;
}

/**
* upload file by filename to the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $local_filename local file name to upload 
*        note: the path of the file must in the php open_basedir
* @param $meta_list metadata assoc array (key value pair array)
* @param $file_id return the file id (including group name and filename)
* @param $group_name specify the group to upload file to
* @return 0 for success, none zero (errno) for fail
*/
function storage_upload_by_filename1($tracker_server, $storage_server, 
			$local_filename, $meta_list, &$file_id, $group_name='')

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

	return storage_do_upload_file1($tracker_server, $storage_server, 
			true, $local_filename, $attr['size'], $file_ext_name,
			$meta_list, $file_id, $group_name);
}

/**
* upload file by buff to the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_buff the file content to upload
* @param $file_size the file content length
* @param $file_ext_name the file ext name (not including dot)
* @param $meta_list metadata assoc array (key value pair array)
* @param $file_id return the file id (including group name and filename)
* @param $group_name specify the group to upload file to
* @return 0 for success, none zero (errno) for fail
*/
function storage_upload_by_filebuff1($tracker_server, $storage_server, 
		$file_buff, $file_size, $file_ext_name, $meta_list, 
		&$file_id, $group_name='')
{
	return storage_do_upload_file1($tracker_server, $storage_server, 
			false, $file_buff, $file_size, $file_ext_name, 
			$meta_list, $file_id, $group_name);
}

/**
* change the metadata of the file on the storage server
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server the connected storage server (assoc array), can be null
* @param $file_id: the file id (including group name and filename)
* @param $meta_list metadata assoc array (key value pair array)
* @param $op_flag flag
*        STORAGE_SET_METADATA_FLAG_OVERWRITE('O') for overwrite all old metadata
*        STORAGE_SET_METADATA_FLAG_MERGE('M') for merge, insert when the meta 
*                                            item not exist, otherwise update it
* @return 0 for success, none zero (errno) for fail
*/
function storage_set_metadata1($tracker_server, $storage_server, 
			$file_id, $meta_list, $op_flag)
{
	$parts = explode(FDFS_FILE_ID_SEPERATOR, $file_id, 2);
	if (count($parts) != 2)
	{
		return FDFS_EINVAL;
	}

	return storage_set_metadata($tracker_server, $storage_server,
                        $parts[0], $parts[1], $meta_list, $op_flag);
}

/**
* query storage server to download file
* @param $tracker_server the connected tracker server (assoc array)
* @param $storage_server return the storage server (assoc array, not connected)
* @param $file_id: the file id (including group name and filename)
* @return 0 for success, none zero (errno) for fail
*/
function tracker_query_storage_fetch1($tracker_server, &$storage_server, $file_id)
{
	$parts = explode(FDFS_FILE_ID_SEPERATOR, $file_id, 2);
	if (count($parts) != 2)
	{
		return FDFS_EINVAL;
	}

	return tracker_query_storage_fetch($tracker_server, $storage_server,
                        $parts[0], $parts[1]);
}

?>
