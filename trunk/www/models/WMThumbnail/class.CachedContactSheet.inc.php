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
 * include base class
 */
include_once ('class.ContactSheet.inc.php');

/**
 * @package Thumbnail
 */
/**
 * Creates a contactsheet from a images in a given folder and/or those added manually and caches it for a given time
 *
 * Tested with Apache 1.3.27 and PHP 4.3.3
 * Last change: 2003-09-25
 *
 * @access public
 * @author Michael Wimmer <flaimo 'at' gmx 'dot' net>
 * @copyright Michael Wimmer
 * @link http://www.flaimo.com/
 * @package Thumbnail
 * @example sample_cs.php Sample script
 * @version 1.001
 */
class CachedContactSheet extends ContactSheet {
	
	/*-------------------*/
	/* V A R I A B L E S */
	/*-------------------*/
	
	/**#@+
	 * @access protected
	 */
	/**
	 * caching time
	 *
	 * @var int
	 */
	var $cache_time = 10;
	
	/**
	 * holds cache object
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
	 * @param string $folder  folder to search in for images, empty string if no dir should be searched through
	 * @param boolean $sub_dirs search sub directories or not
	 * @param int $seconds  caching time in seconds
	 * @return void
	 * @access public
	 * @uses ContactSheet::ContactSheet()
	 * @uses $cache_time
	 */
	function CachedContactSheet($folder = '', $sub_dirs = FALSE, $seconds = 10) {
		parent::ContactSheet ( $folder, $sub_dirs );
		$this->cache_time = ( int ) $seconds;
	} // end constructor
	

	/**
	 * fills the cache variable with the cache object
	 *
	 * @return void
	 * @access protected
	 * @uses PictureCache
	 * @uses $cache_time
	 * @uses $cache
	 * @uses $cs_type
	 */
	function setCache() {
		if (! isset ( $this->cache )) {
			$this->cache = new PictureCache ( 'cs_' . str_replace ( '/', '', $folder ), $this->cs_type, $this->cache_time );
		} // end if
	} // end function
	

	/**
	 * outputs the cs image to the browser
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @param int $quality jpg-quality: 0-100
	 * @return mixed
	 * @access public
	 * @uses ContactSheet::setOutputFormat()
	 * @uses ContactSheet::setContactSheetImage()
	 * @uses setCache()
	 * @uses PictureCache
	 * @uses $cs_type
	 * @uses $cs_image
	 */
	function outputContactSheet($format = 'png', $quality = 75) {
		parent::setOutputFormat ( $format );
		$this->setCache ();
		if ($this->cache_time === 0 || $this->cache->isPictureCached () === FALSE) {
			parent::setContactSheetImage ();
			if ($this->cache_time > 0) {
				$this->cache->writePictureCache ( $this->cs_image, $quality );
			} // end if
			parent::outputContactSheet ( $format, $quality );
		} else {
			$ct = array (1 => 'gif', 2 => 'jpeg', 3 => 'png', 15 => 'vnd.wap.wbmp' );
			if (array_key_exists ( $this->thumbnail_type, $ct )) {
				header ( 'Content-type: image/' . $ct [$this->cs_type] );
			} // end if
			

			header ( 'Expires: ' . date ( "D, d M Y H:i:s", time () + $this->cache_time ) . ' GMT' );
			header ( 'Cache-Control: public' );
			header ( 'Cache-Control: max-age=' . $this->cache_time );
			echo $this->cache->returnPictureCache ();
		} // end if
	} // end function
	

	/**
	 * returns the cs image for further processing
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @return mixed
	 * @access public
	 * @uses ContactSheet::setOutputFormat()
	 * @uses ContactSheet::setContactSheetImage()
	 * @uses setCache()
	 * @uses PictureCache
	 * @uses setContactSheetImage()
	 * @uses $cs_image
	 */
	function returnContactSheet($format = 'png') {
		parent::setOutputFormat ( $format );
		$this->setCache ();
		if ($this->cache_time === 0 || $this->cache->isPictureCached () === FALSE) {
			parent::setContactSheetImage ();
			if ($this->cache_time > 0) {
				$this->cache->writePictureCache ( $this->cs_image, 100 );
			} // end if
		} else {
			switch ($this->cs_type) {
				case 1 :
					$this->cs_image = imagecreatefromgif ( $this->cache->returnCachePicturename () );
					break;
				case 2 :
					$this->cs_image = imagecreatefromjpeg ( $this->cache->returnCachePicturename () );
					break;
				case 3 :
					$this->cs_image = imagecreatefrompng ( $this->cache->returnCachePicturename () );
					break;
				case 15 :
					$this->cs_image = imagecreatefromwbmp ( $this->cache->returnCachePicturename () );
					break;
				case 999 :
				default :
					$this->cs_image = imagecreatefromstring ( $this->cache->returnCachePicturename () );
					break;
			} // end switch
		} // end if
		return $this->cs_image;
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
		

		parent::setOutputFormat ( $format );
		$this->setCache ();
		$path = $this->cache->getCacheFilepath ( $format, $quality );
		if ($path != FALSE) {
			return ( string ) $path;
		} else { // trys to create cache and return filename
			parent::setContactSheetImage ();
			$this->cache->writePictureCache ( $this->cs_image, $quality );
			return $this->cache->getCacheFilepath ( $format, $quality );
		} // end if
	} // end function
} // end class CachedContactSheet
?>