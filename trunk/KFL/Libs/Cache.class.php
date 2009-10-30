<?php
/**
 * usage: 
 $cache = new Cache($lifetime,$compress); 
 $cache->setCacheStore("file");// or memcache
 $cache->setCacheDir($dir);
 $cache->setCacheServer(array(array("host","port")));//memcached setting
 $cache->setCacheFile($filename);
 if($cache->isCached()){
 	$cache->output();
 }else{
 	$cache->save();
 }
  
 **/
class Cache {
	/**
	 * is caching
	 * @param int
	 */
	private $mIsCache = 0;
	
	/**
	 * cache time
	 * @param int
	 */
	private $mCacheTime = 300;
	
	/**
	 * cache dir
	 * @param string
	 */
	public $mCacheDir = "/tmp/";
	/**
	 * cache file
	 * @param string
	 */
	public $mCacheFile = '';
	/**
	 * $mCacheCompress
	 * @param int
	 */
 	private $mCacheCompress = 0;
 	
 	/**
	 * $mCacheStore
	 * @param string
	 */
 	private $mCacheStore = 'file';
  	/**
	 * $mCacheServer
	 * @param array
	 */	
 	private $mCacheServer = array();
 	 /**
	 * $mCacheMemcache
	 * @param object
	 */	
 	private $mCacheMemcache ;
  	 /**
	 * $mCacheContent
	 * @param string
	 */		
 	private $mCacheContent = '';
	
	/**
	 * Cache
	 * 0 表示永久缓存，<0 表示不缓存，>0 表示缓存时间
	 * @param int $lifetime
	 * @param int compressed
	 * @access public
	 * @return void
	 */
	public function Cache($lifetime,$compressed=1) {
		
		$this->mCacheTime = $lifetime;
		$this->mCacheCompress = $compressed;
		$this->mCacheDir = "tmp/_cache/";
		$this->mCacheFile = md5 ( $_SERVER ['REQUEST_URI'] );
		
		if ($this->mCacheTime >= 0)
			$this->mIsCache = 1;
		
		//
		ob_start ();
	
	
	}
	/**
	 * setCacheStore
	 * @param string $store_type file|memcache
	 * @access public
	 * @return void
	 */
	public function setCacheStore($store_type){
		$this->mCacheStore = $store_type;
	}
	/**
	 * setCacheStore
	 * @param int $compressed 0|1
	 * @access public
	 * @return void
	 */
	public function setCacheCompress($compressed){
		$this->mCacheCompress = $compressed;
	}
	
	/**
	 * setCacheServer
	 * @param array $store_server
	 * @access public
	 * @return void
	 */
	public function setCacheServer($store_server){
		$this->mCacheServer = $store_server;
	}
	
	/**
	 * setCacheDir
	 * @param string $dir
	 * @access public
	 * @return void
	 */	
	public function setCacheDir($dir) {
		
		$this->mCacheDir = $dir;
	}
	
	/**
	 * setCacheFile
	 * @param string $file
	 * @access public
	 * @return void
	 */	
	public function setCacheFile($file) {
		
		$this->mCacheFile = $file;
	}
	
	/**
	 * is_cached
	 * @access public
	 * @return void
	 */
	public function isCached() {
		if($this->mCacheStore=='file'){
			if ($this->mIsCache && is_file ( $this->mCacheDir . $this->mCacheFile )) {
				if ($this->mCacheTime == 0) {
					return true;
				}
				
				$modify_time = @filemtime ( $this->mCacheDir . $this->mCacheFile );
				if (time () - $modify_time < $this->mCacheTime) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}	
		if($this->mCacheStore=='memcache'){
			$this->mCacheMemcache = $this->_setupMemcached($this->mCacheServer);
			
			$this->mCacheContent = $this->mCacheMemcache->get($this->mCacheFile);
			
			if(!$this->mCacheContent) return false;
			else return true;
		}
	}
	/**
	 * output
	 * @access public
	 * @return void
	 */
	public function output() {
		$content= $this->fetch();
		if($this->mCacheCompress==1){
			header("Content-Encoding: gzip");
			header("Vary: Accept-Encoding");
	        header("Content-Length: ".strlen($content));
		}
		echo $content;
		ob_end_flush ();
	}
	/**
	 * fetch
	 * @access public
	 * @return string
	 */
	public function fetch() {
		if($this->mCacheStore=='file'){
			return file_get_contents ( $this->mCacheDir . $this->mCacheFile );
		}
		if($this->mCacheStore=='memcache'){
			return $this->mCacheContent;
		}
	}
	/**
	 * save
	 * @param $content
	 * @access public
	 * @return void
	 */
	public function save($content = '') {
		$designated = ! empty ( $content ) ? 1 : 0;
		$content =  ( $designated ) ? $content : ob_get_contents ();
		if(!$designated) $content .="\r\n <!-- Cached ";
		if($this->mCacheCompress==1) {
			if(!$designated) $content .=" with gzip by KFL at ".date("Y-m-d H:i:s")."-->";
			$content = gzencode($content,9);
		}else{
			if(!$designated) $content .="by KFL at ".date("Y-m-d H:i:s")."-->";
		}
		if($this->mCacheStore=='file'){
			if ($this->mIsCache) {
				if (! is_dir ( $this->mCacheDir )) {
					$this->_mkdirr ( $this->mCacheDir );
				}
				$mCacheFile = $this->mCacheDir . $this->mCacheFile;
				write_file($content,$mCacheFile);	
				ob_end_flush ();
			}		
		}
		
		if($this->mCacheStore=='memcache'){			
			$this->mCacheMemcache->set($this->mCacheFile,$content,0,$this->mCacheTime);
			ob_end_flush ();
		}
	}
	
	/**
	 * clear
	 * @access public
	 * @return void
	 */
	public function clear() {
		@unlink ( $this->mCacheDir . $this->mCacheFile );
	}
	
	/**
	 * Create a directory structure recursively
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.0.0
	 * @link        http://aidanlister.com/repos/v/function.mkdirr.php
	 * @param       string   $pathname    The directory structure to create
	 * @return      bool     Returns TRUE on success, FALSE on failure
	 */
	private function _mkdirr($pathname, $mode = 0777) {
		// Check if directory already exists
		if (is_dir ( $pathname ) || empty ( $pathname )) {
			return true;
		}
		
		// Ensure a file does not already exist with the same name
		if (is_file ( $pathname )) {
			trigger_error ( 'mkdirr() File exists', E_USER_WARNING );
			return false;
		}
		
		// Crawl up the directory tree
		$next_pathname = substr ( $pathname, 0, strrpos ( $pathname, "/" ) );
		if ($this->_mkdirr ( $next_pathname, $mode )) {
			if (! file_exists ( $pathname )) {
				$rs = mkdir ( $pathname, $mode );
				chmod ( $pathname, $mode );
				return $rs;
			}
		}
		return false;
	}
	
	/**
	 * _setupMemcached
	 * @param array $server
	 * @access private
	 * @return void
	 */
	private function _setupMemcached($server){
		if (!class_exists('Memcache'))
        {
        	trigger_error("Fatal Error: Memcache extension not exists!", E_USER_ERROR);
            die();
        }
        if(!(isset($GLOBALS['pageMemcacheObj']) && is_object($GLOBALS['pageMemcacheObj']))){
			if(isset($server) && is_array($server)){
				
			 	$memcache  = new Memcache;
			 	foreach ($server as $key => $v) {
		 		  if(!empty($v['host']) && !empty($v['port'])){
		 			$memcache->addServer($v['host'], $v['port']);
		 		  }
			 	}
			}
			$GLOBALS['pageMemcacheObj'] = $memcache;
        }
		
		return $GLOBALS['pageMemcacheObj'];
	}
}

?>