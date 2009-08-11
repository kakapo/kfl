<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
//+----------------------------------------------------------------------+
//| WAMP (XP-SP1/1.3.27/4.0.12/4.3.3)                                    |
//+----------------------------------------------------------------------+
//| Copyright (c) 1992-2003 Michael Wimmer                               |
//+----------------------------------------------------------------------+
//| I don't have the time to read through all the licences to find out   |
//| what the exactly say. But it's simple. It's free for non commercial  |
//| projects, but as soon as you make money with it, i want my share :-) |
//| (License : Free for non-commercial use)                              |
//+----------------------------------------------------------------------+
//| Authors: Michael Wimmer <flaimo 'at' gmx 'dot' net>                  |
//+----------------------------------------------------------------------+
//
// $Id$


/**
 * @package Thumbnail
 */
/**
 * Creates a thumbnail from a source image and caches it for a given time
 *
 * Tested with Apache 1.3.27 and PHP 4.3.3
 * Last change: 2003-09-25
 *
 * @access public
 * @author Michael Wimmer <flaimo 'at' gmx 'dot' net>
 * @copyright Michael Wimmer
 * @link http://www.flaimo.com/
 * @package Thumbnail
 * @example sample_thumb.php Sample script
 * @version 1.001
 */
class PictureCache {
	
	/*-------------------*/
	/* V A R I A B L E S */
	/*-------------------*/
	
	/**#@+
	 * @access protected
	 */
	/**
	 * name of the cache dir
	 *
	 * @var string
	 */
	var $cache_dir = '_cache/pic_cache';
	
	/**
	 * prefix for every cache file
	 *
	 * @var string
	 */
	var $prefix = 'tc_';
	
	/**
	 * check if cache dir is available
	 *
	 * @var boolean
	 */
	var $check_cache_dir = FALSE;
	
	/**
	 * amount of seconds thumbs should be cached. 0 = no cache
	 *
	 * @var int
	 */
	var $cache_time = 0;
	
	/**
	 * possible image formats
	 *
	 * @var array
	 */
	var $types = array (1 => 'gif', 2 => 'jpg', 3 => 'png', 15 => 'wbmp', 999 => 'string' );
	
	/**
	 * name/path of picture
	 *
	 * @var string
	 */
	var $image_path;
	
	/**
	 * image type of cached thumbnail
	 *
	 * @var int
	 */
	var $image_type = 3;
	/**#@-*/
	
	/*-----------------------*/
	/* C O N S T R U C T O R */
	/*-----------------------*/
	
	/**
	 * Constructor
	 *
	 * @param string $file  path/filename of picture
	 * @param int $type  image type
	 * @param int $seconds  amount of seconds thumbs should be cached. 0 = no cache
	 * @return void
	 * @access public
	 * @uses checkCacheDir()
	 * @uses $cache_time
	 * @uses $image_path
	 * @uses $image_type
	 */
	function PictureCache($file = '', $type = 3, $seconds = 0) {
		if ($this->checkCacheDir () === FALSE) {
			die ( 'error creating cache directory' );
		} // end if
		$this->cache_time = ( int ) $seconds;
		$this->image_path = ( string ) $file;
		$this->image_type = ( int ) $type;
	} // end constructor
	

	/*-------------------*/
	/* F U N C T I O N S */
	/*-------------------*/
	
	/**
	 * Checks if the cache directory exists, else trys to create it
	 *
	 * @return boolean
	 * @access protected
	 * @uses $check_cache_dir
	 * @uses $cache_dir
	 */
	function checkCacheDir() {
		if ($this->check_cache_dir === FALSE || is_dir ( $this->cache_dir )) {
			return ( boolean ) TRUE;
		} else {
			return ( boolean ) ((! mkdir ( $this->cache_dir, 0700 )) ? FALSE : TRUE);
		} // end if
	} // end function
	

	/**
	 * Sticks together filename + path
	 *
	 * @return string
	 * @access public
	 * @uses $cache_dir
	 * @uses $prefix
	 * @uses $image_path
	 * @uses $types
	 * @uses $image_type
	 */
	function returnCachePicturename() {
		return ( string ) $this->cache_dir . '/' . $this->prefix . md5 ( $this->image_path ) . '.' . $this->types [$this->image_type];
	} // end function
	

	/**
	 * Checks if a picture is cached and up to date
	 *
	 * @return boolean
	 * @access public
	 * @uses returnCachePicturename()
	 * @uses $cache_time
	 */
	function isPictureCached() {
		$filetime = @filemtime ( $this->returnCachePicturename () );
		if (! isset ( $filetime )) {
			return ( boolean ) FALSE;
		} // end if
		if ((time () - $filetime) > $this->cache_time) {
			return ( boolean ) FALSE;
		} // end if
		return ( boolean ) TRUE;
	} // end function
	

	/**
	 * Returns a cached picture
	 *
	 * @return mixed
	 * @access public
	 * @uses returnCachePicturename()
	 */
	function returnPictureCache() {
		return readfile ( $this->returnCachePicturename (), 'r' );
	} // end function
	

	/**
	 * writes a thumbnail to a file
	 *
	 * @param $image variable with image
	 * @param int $quality jpg-quality: 0-100
	 * @return void
	 * @access public
	 * @uses $thumbnail_type
	 * @uses $thumbnail
	 * @uses returnCachePicturename()
	 */
	function writePictureCache($image = '', $quality = 75) {
		if (strlen ( trim ( $image ) ) > 0) {
			switch ($this->image_type) {
				case 1 :
					imagegif ( $image, $this->returnCachePicturename () );
					break;
				case 2 :
					$quality = ( int ) $quality;
					if ($quality < 0 || $quality > 100) {
						$quality = 75;
					} // end if
					imagejpeg ( $image, $this->returnCachePicturename (), $quality );
					break;
				case 3 :
					imagepng ( $image, $this->returnCachePicturename () );
					break;
				case 15 :
					imagewbmp ( $image, $this->returnCachePicturename () );
					break;
			} // end switch
		}
	} // end function
	

	/**
	 * returns the path/filename of the cached thumbnail
	 *
	 * if cached pic is not available returns false
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @param int $quality jpg-quality: 0-100
	 * @return mixed string or FALSE if no cached pic is available
	 * @access public
	 * @uses returnCachePicturename()
	 */
	function getCacheFilepath($format = 'png', $quality = 75) {
		if (file_exists ( $this->returnCachePicturename () )) {
			return $this->returnCachePicturename ();
		} else {
			return ( boolean ) FALSE;
		}
	} // end function
	

	/**
	 * returns the cache time in seconds
	 *
	 * @return int $cache_time
	 * @access public
	 * @uses $cache_time
	 */
	function getCacheTime() {
		return ( int ) $this->cache_time;
	} // end function
} // end class PictureCache
?>