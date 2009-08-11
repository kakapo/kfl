<?php
/*
require("FastDFS.class.php");
//

//download
echo FastDFS::factory()->downToFile('group1/M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png', 'nginx.png'), "<br />";
echo FastDFS::factory()->downToFileEx('group1/M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png'), "<br />";
echo FastDFS::factory()->downToBuff('group1/M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png'), "<br />";

//delete
FastDFS::factory()->delFile('group1/M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png');

//upload
FastDFS::factory()->setgroup('group1');
echo FastDFS::factory()->upByFileName('nginx.png'), "<br />";
echo FastDFS::factory()->upByBuff( file_get_contents('nginx.png'), 'png' ), "<br />";


*/
require APP_DIR.'/plugins/fastdfs/fdfs_common.php';
require APP_DIR.'/plugins/fastdfs/fdfs_tracker_client.php';
require APP_DIR.'/plugins/fastdfs/fdfs_storage_client.php';

class FastDFS
{
        private $tracker_server;
        private $storage_server;
        private $group_name='';

        public static function factory()
        {
                static $obj;

                if(!$obj)
                        $obj = new FastDFS();

                return $obj;
        }

        public function __construct()
        {
                $this->storage_server = null;
                $this->connection();
        }
		public function setgroup($group_name){
			 $this->group_name = $group_name;
		}
        public function connection()
        {
                $tracker_server = tracker_get_connection();
                if ($tracker_server == false)
                {
                        return $this->halt("tracker_get_connection fail\n");
                }

                $this->tracker_server = $tracker_server;
        }

        /**
         * 根据文件名上传
         *
         * @param string $local_filename
         * @param array $meta_list
         * @return remote filename
         */
        public function upByFileName($local_filename, $meta_list=array())
        {
                //$local_filename = 'nginx-logo.png';
                //$group_name = '';   //you can specify the group to upload file to
                //$meta_list = array('width' => 1024, 'height' => 768, 'color' => '#c0c0c0');

                $result = storage_upload_by_filename($this->tracker_server, $this->storage_server,
                                        $local_filename, $meta_list,
                                        $this->group_name, $remote_filename);
                if ($result == 0)
                {
                        //echo "group_name=$group_name, remote_filename=$remote_filename\n";
                        return $this->group_name."/".$remote_filename;
                }
                else
                {
                        return $this->halt("storage_upload_by_filename fail, result=$result\n");
                }
        }

        /**
         * 把文件 buff 上传
         *
         * @param string $file_buff
         * @param string $file_ext
         * @param array $meta_list
         * @return remote filename
         */
        public function upByBuff($file_buff, $file_ext, $meta_list=array())
        {
                if ($file_buff != false)
                {
                        $file_size = strlen($file_buff);
                        //$group_name = '';  //you can specify the group to upload file to
                        $result = storage_upload_by_filebuff($this->tracker_server, $this->storage_server,
                                $file_buff, $file_size, $file_ext, $meta_list,
                                $this->group_name, $remote_filename);

                        if ($result == 0)
                        {
                                //echo "group_name=$group_name, remote_filename=$remote_filename\n";
                                return $this->group_name."/".$remote_filename;
                        }
                        else
                        {
                                return $this->halt("storage_upload_by_filename fail, result=$result\n");
                        }
                }
        }

        /**
         * 下载到本地文件
         *
         * @param string $remote_filename 远程文件名
         * @param string $local_filename 本地文件名
         * @return bool
         */
        public function downToFile($remote_filename, $local_filename)
        {
                //$remote_filename = 'M00/00/00/fWaowEoaQj8AAFMnYnV13w36.php';
        	    if($this->group_name=='') {
        	    	$pos = strpos($remote_filename,"/");
        	    	$this->group_name = substr($remote_filename,0,$pos);
        	    	$remote_filename = substr($remote_filename,$pos+1);
        	    }
                $result = tracker_query_storage_fetch($this->tracker_server, $this->storage_server,
                                $this->group_name, $remote_filename);
                if ($result == 0)
                {
                        //echo "storage server ${storage_server['ip_addr']}:${storage_server['port']}\n <br />";
                }
                else
                {
                        return $this->halt("tracker_query_storage_fetch fail, errno: $result\n");
                }

                $result = storage_download_file_to_file($this->tracker_server, $this->storage_server,
                                $this->group_name, $remote_filename, $local_filename, $file_size);
                if ($result == 0)
                {
                        //echo "download file to file success, file size: $file_size\n <br />";
                        return true;
                }
                else
                {
                        return $this->halt("storage_download_file_to_file fail, errno: $result\n");
                }
        }

        /**
         * 下载到 buff
         *
         * @param string $remote_filename 远程文件名
         * @return file name
         */
        public function downToBuff($remote_filename)
        {
        	    if($this->group_name=='') {
        	    	$pos = strpos($remote_filename,"/");
        	    	$this->group_name = substr($remote_filename,0,$pos);
        	    	$remote_filename = substr($remote_filename,$pos+1);
        	    }
                $result = storage_download_file_to_buff($this->tracker_server, $this->storage_server,
                $this->group_name, $remote_filename, $file_buff, $file_size);
                if ($result == 0)
                {
                        //echo "download file to buff success, file size: $file_size" . ", buff size:" . strlen($file_buff) . "\n <br />";
                        $fname = str_replace('/', '_', $remote_filename);
                        file_put_contents($fname, $file_buff);
                        return $fname;
                }
                else
                {
                        return $this->halt("storage_download_file_to_buff fail, errno: $result\n");
                }
        }

        public function downToFileEx($remote_filename)
        {
        	    if($this->group_name=='') {
        	    	$pos = strpos($remote_filename,"/");
        	    	$this->group_name = substr($remote_filename,0,$pos);
        	    	$remote_filename = substr($remote_filename,$pos+1);
        	    }
                $local_filename = str_replace('/', '-', $remote_filename);
                $fp = fopen($local_filename, 'wb');
                if ($fp === false)
                {
                        die("open file \"$local_filename\" to write fail");
                }
                else
                {
                        $result = storage_download_file_ex($this->tracker_server, $this->storage_server,
                                $this->group_name, $remote_filename, 'write_file_callback', $fp, $file_size);
                        if ($result == 0)
                        {
                                return $local_filename;
                                //echo "download file to file success, file size: $file_size\n <br />";
                        }
                        else
                        {
                                return $this->halt("storage_download_file_to_file fail, errno: $result\n");
                        }
                        fclose($fp);
                }
        }

        /**
         * 删除文件
         *
         * @param string $remote_filename 远程文件名
         * @return bool
         */
        public function delFile($remote_filename)
        {
        	    if($this->group_name=='') {
        	    	$pos = strpos($remote_filename,"/");
        	    	$this->group_name = substr($remote_filename,0,$pos);
        	    	$remote_filename = substr($remote_filename,$pos+1);
        	    }
     
                $result = storage_delete_file($this->tracker_server, $this->storage_server, $this->group_name, $remote_filename);

                return ($result==0) ? true : false;
        }


        public function halt($msg)
        {
                echo "<p>{$msg}</p>";
                return false;
        }

        public function disconnection()
        {
                fdfs_quit($this->tracker_server);
                tracker_close_all_connections();
        }

        public function __destruct()
        {
                $this->disconnection();
        }

}



function write_file_callback($arg, $file_size, $file_buff, $buff_bytes)
{
        if (fwrite($arg, $file_buff, $buff_bytes) != $buff_bytes)
        {
                echo "in write_file_callback fwrite fail";
                return FDFS_EIO;
        }

        return 0;
}
?>