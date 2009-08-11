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
 * include used classes
 */
include_once ('class.Thumbnail.inc.php');
include_once ('class.PictureCache.inc.php');

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
class CachedThumbnail extends Thumbnail {
	
	/*-------------------*/
	/* V A R I A B L E S */
	/*-------------------*/
	
	/**#@+
	 * @access protected
	 */
	/**
	 * amount of seconds thumbs should be cached. 0 = no cache
	 *
	 * @var int
	 */
	var $cache_time = 0;
	
	/**
	 * flipped formats array
	 *
	 * @var array
	 */
	var $types;
	
	/**
	 * holds PictureCache object
	 *
	 * @var object
	 */
	var $cache;
	/**#@-*/
	
	/*-----------------------*/
	/* C O N S T R U C T O R */
	/*-----------------------*/
	
	/**
	 * Constructor
	 *
	 * @param string $file  path/filename of picture
	 * @param int $seconds  amount of seconds thumbs should be cached. 0 = no cache
	 * @return void
	 * @access public
	 * @uses Thumbnail::Thumbnail()
	 * @uses $cache_time
	 * @uses $types
	 */
	function CachedThumbnail($file = '', $seconds = 0) {
		parent::Thumbnail ( $file );
		$this->cache_time = ( int ) $seconds;
		$this->types = array_flip ( $this->formats );
	} // end constructor
	

	/*-------------------*/
	/* F U N C T I O N S */
	/*-------------------*/
	
	/**
	 * fills the cache variable with the cache object
	 *
	 * @return void
	 * @access protected
	 * @uses PictureCache
	 * @uses $cache_time
	 * @uses $cache
	 * @uses $image_path
	 * @uses $thumbnail_type
	 */
	function setCache() {
		if (! isset ( $this->cache )) {
			$this->cache = new PictureCache ( $this->image_path, $this->thumbnail_type, $this->cache_time );
		} // end if
	} // end function
	

	/**
	 * outputs the thumbnail to the browser
	 *
	 * overrides method of base class
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @param int $quality jpg-quality: 0-100
	 * @return mixed
	 * @access public
	 * @uses setOutputFormat()
	 * @uses setCache()
	 * @uses $cache_time
	 * @uses Thumbnail::isPictureCached()
	 * @uses Thumbnail::createThumbnail()
	 * @uses PictureCache::writePictureCache()
	 * @uses Thumbnail::outputThumbnail()
	 * @uses $thumbnail
	 * @uses PictureCache::returnPictureCache()
	 */
	function outputThumbnail($format = 'png', $quality = 75) {
		parent::setOutputFormat ( $format );
		$this->setCache ();
		if ($this->cache_time === 0 || $this->cache->isPictureCached () === FALSE) {
			parent::createThumbnail ();
			if ($this->cache_time > 0) {
				$this->cache->writePictureCache ( $this->thumbnail, $quality );
			} // end if
			parent::outputThumbnail ( $format, $quality );
		} else {
			$ct = array (1 => 'gif', 2 => 'jpeg', 3 => 'png', 15 => 'vnd.wap.wbmp' );
			if (array_key_exists ( $this->thumbnail_type, $ct )) {
				header ( 'Content-type: image/' . $ct [$this->thumbnail_type] );
			} // end if
			

			header ( 'Expires: ' . date ( "D, d M Y H:i:s", time () + $this->cache_time ) . ' GMT' );
			header ( 'Cache-Control: public' );
			header ( 'Cache-Control: max-age=' . $this->cache_time );
			echo $this->cache->returnPictureCache ();
		} // end if
	} // end function
	

	/**
	 * returns the variable with the thumbnail image
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @return mixed
	 * @access public
	 * @uses setOutputFormat()
	 * @uses Thumbnail::setCache()
	 * @uses $cache_time
	 * @uses Thumbnail::isPictureCached()
	 * @uses Thumbnail::createThumbnail()
	 * @uses Thumbnail::writePictureCache()
	 * @uses $thumbnail_type
	 * @uses $thumbnail
	 * @uses Thumbnail::returnCachePicturename()
	 */
	function returnThumbnail($format = 'png') {
		$this->setOutputFormat ( $format );
		$this->setCache ();
		if ($this->cache_time === 0 || $this->cache->isPictureCached () === FALSE) {
			parent::createThumbnail ();
			if ($this->cache_time > 0) {
				$this->cache->writePictureCache ( $this->thumbnail, 100 );
			} // end if
		} else {
			switch ($this->thumbnail_type) {
				case 1 :
					$this->thumbnail = imagecreatefromgif ( $this->cache->returnCachePicturename () );
					break;
				case 2 :
					$this->thumbnail = imagecreatefromjpeg ( $this->cache->returnCachePicturename () );
					break;
				case 3 :
					$this->thumbnail = imagecreatefrompng ( $this->cache->returnCachePicturename () );
					break;
				case 15 :
					$this->thumbnail = imagecreatefromwbmp ( $this->cache->returnCachePicturename () );
					break;
				case 999 :
				default :
					$this->thumbnail = imagecreatefromstring ( $this->cache->returnCachePicturename () );
					break;
			} // end switch
		} // end if
		return $this->thumbnail;
	} // end function
	

	/**
	 * returns the path/filename of the cached thumbnail
	 *
	 * if cached pic is not available, tries to create it with the given parameters
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @param int $quality jpg-quality: 0-100
	 * @return mixed string or FALSE if no cached pic is available
	 * @access public
	 * @uses $cache_time
	 * @uses PictureCache::isPictureCached()
	 * @uses setOutputFormat()
	 * @uses PictureCache::writePictureCache()
	 * @uses Thumbnail::createThumbnail()
	 */
	function getCacheFilepath($format = 'png', $quality = 75) {
		if ($this->cache_time === 0) {
			return ( boolean ) FALSE; // no cached thumb available
		} // end if
		

		$this->setOutputFormat ( $format );
		$this->setCache ();
		$path = $this->cache->getCacheFilepath ( $format, $quality );
		if ($path != FALSE) {
			return ( string ) $path;
		} else { // trys to create cache and return filename
			parent::createThumbnail ();
			$this->cache->writePictureCache ( $this->thumbnail, $quality );
			return $this->cache->getCacheFilepath ( $format, $quality );
		} // end if
	} // end function
} // end class CachedThumbnail
?>