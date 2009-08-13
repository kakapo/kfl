<?php
/**
 * usage: 
 $cache = new Cache($lifetime); 
 $cache->setCacheDir($dir);
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
	 * Cache
	 * 0 表示永久缓存，<0 表示不缓存，>0 表示缓存时间
	 * @param int $lifetime
	 * @access public
	 * @return void
	 */
	public function Cache($lifetime) {
		
		$this->mCacheTime = $lifetime;
		if ($this->mCacheTime >= 0)
			$this->mIsCache = 1;
		
		$this->mCacheDir = "tmp/_cache/";
		$this->mCacheFile = md5 ( $_SERVER ['REQUEST_URI'] );
		
		ob_start ();
	
	}
	public function setCacheDir($dir) {
		
		$this->mCacheDir = $dir;
	}
	public function setCacheFile($file) {
		
		$this->mCacheFile = $file;
	}
	/**
	 * is_cached
	 * @access public
	 * @return void
	 */
	public function isCached() {
		
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
	/**
	 * output
	 * @access public
	 * @return void
	 */
	public function output() {
		echo $this->fetch();
		ob_end_flush ();
	}
	/**
	 * fetch
	 * @access public
	 * @return string
	 */
	public function fetch() {
		return file_get_contents ( $this->mCacheDir . $this->mCacheFile );
		
	}
	/**
	 * save
	 * @param $content
	 * @access public
	 * @return void
	 */
	public function save($content = '') {
		if ($this->mIsCache) {
			if (! is_dir ( $this->mCacheDir )) {
				$this->_mkdirr ( $this->mCacheDir );
			}
			$content = ! empty ( $content ) ? $content : ob_get_contents ();
			
			$mCacheFile = $this->mCacheDir . $this->mCacheFile;
			
			file_put_contents ( $mCacheFile, $content, LOCK_EX );
			
			ob_end_flush ();
		}
	
	}
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
}

?>