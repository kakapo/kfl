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
include_once ('class.CachedThumbnail.inc.php');

/**
 * @package Thumbnail
 */
/**
 * Creates a thumbnail from a source image, put a watermark/logo on it and caches it for a given time
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
class WMThumbnail extends CachedThumbnail {
	
	/*-------------------*/
	/* V A R I A B L E S */
	/*-------------------*/
	
	/**#@+
	 * @access protected
	 */
	/**
	 * path/filename of logo / watermark
	 *
	 * @var string
	 */
	var $wm_image_path;
	
	/**
	 * @var resource
	 */
	var $wm_image;
	
	/**
	 * @var int
	 */
	var $wm_image_height;
	
	/**
	 * @var int
	 */
	var $wm_image_width;
	
	/**
	 * image format of logo
	 *
	 * @var int
	 */
	var $wm_image_type;
	
	/**
	 * holds all logos/watermarks
	 *
	 * @var array
	 */
	var $logos = array ();
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
	 * @uses CachedThumbnail::CachedThumbnail()
	 * @uses $wm_image_path
	 */
	function WMThumbnail($file = '', $seconds = 0) {
		parent::CachedThumbnail ( $file, $seconds );
	} // end constructor
	

	/**
	 * reads metadata of the logo image
	 *
	 * @param string $path  path/filename of picture
	 * @return void
	 * @access protected
	 * @uses $wm_image_width
	 * @uses $wm_image_height
	 * @uses $wm_image_type
	 * @uses $formats
	 */
	function readWMImageData($path = '') {
		if (strlen ( trim ( $path ) ) > 0) {
			list ( $this->wm_image_width, $this->wm_image_height, $this->wm_image_type, $attr ) = getimagesize ( $path );
			unset ( $attr );
			if (! in_array ( $this->wm_image_type, $this->formats )) {
				die ( "Can't create thumbnail from '" . $this->wm_image_type . "' source: " . $this->wm_image_path );
			} // end if
		} // end if
	} // end function
	

	/**
	 * reads the logo pic into a variable
	 *
	 * @param string $path  path/filename of picture
	 * @return void
	 * @access protected
	 * @uses $wm_image
	 * @uses readWMImageData()
	 * @uses $wm_image_type
	 * @uses $wm_image_path
	 */
	function readWMImage($path = '') {
		if (strlen ( trim ( $path ) ) > 0 && ! isset ( $this->wm_image )) {
			$this->readWMImageData ( $path );
			switch ($this->wm_image_type) {
				case 1 :
					$this->wm_image = imagecreatefromgif ( $path );
					break;
				case 2 :
					$this->wm_image = imagecreatefromjpeg ( $path );
					break;
				case 3 :
					$this->wm_image = imagecreatefrompng ( $path );
					break;
				case 15 :
					$this->wm_image = imagecreatefromwbmp ( $path );
					break;
				case 999 :
				default :
					$this->wm_image = imagecreatefromstring ( $path );
					break;
			} // end switch
		} // end if
	} // end function
	

	/**
	 * sets the position of the logo /watermark
	 *
	 * @param string $logo  path/filename of the logo
	 * @param int $position 1 = left-top, 2 = right-top, 3 = right-bottom, 4 = left-bottom, 5 = center
	 * @param int $margin margin to the border of the thumbnail
	 * @return void
	 * @access public
	 * @uses $position
	 */
	function addLogo($logo = '', $position = 3, $margin = 1) {
		if (file_exists ( $logo ) && ($position > 0 && $position < 6)) {
			$this->logos [] = array ('path' => trim ( $logo ), 'pos' => $position, 'margin' => $margin );
		} // end if
	} // end function
	

	/**
	 * creates the thumbnail and saves it to a variable
	 *
	 * @return void
	 * @access protected
	 * @uses Thumbnail::createThumbnail()
	 * @uses readWMImage()
	 * @uses $thumbnail
	 * @uses $thumbnail_width
	 * @uses $thumbnail_height
	 * @uses $wm_image_width
	 * @uses $wm_image_height
	 * @uses $position
	 * @uses $wm_image
	 * @uses $logos
	 */
	function createThumbnail() {
		parent::createThumbnail ();
		imagealphablending ( $this->thumbnail, true );
		foreach ( $this->logos as $logo ) {
			if (strlen ( trim ( $logo ['path'] ) ) > 0) {
				$this->readWMImage ( $logo ['path'] );
				$start_pos_x = $this->thumbnail_width - $logo ['margin'] - $this->wm_image_width;
				$start_pos_y = $this->thumbnail_height - $logo ['margin'] - $this->wm_image_height;
				switch ($logo ['pos']) {
					case 1 : // left-top
						imagecopy ( $this->thumbnail, $this->wm_image, $logo ['margin'], $logo ['margin'], 0, 0, $this->wm_image_width, $this->wm_image_height );
						break;
					case 2 : // right-top
						imagecopy ( $this->thumbnail, $this->wm_image, $start_pos_x, $logo ['margin'], 0, 0, $this->wm_image_width, $this->wm_image_height );
						break;
					case 3 : // right-bottom
						imagecopy ( $this->thumbnail, $this->wm_image, $start_pos_x, $start_pos_y, 0, 0, $this->wm_image_width, $this->wm_image_height );
						break;
					case 4 : // left-bottom
						imagecopy ( $this->thumbnail, $this->wm_image, $logo ['margin'], $start_pos_y, 0, 0, $this->wm_image_width, $this->wm_image_height );
						break;
					case 5 : // center
					default :
						$middle_x = ($this->thumbnail_width / 2) - ($this->wm_image_width / 2);
						$middle_y = ($this->thumbnail_height / 2) - ($this->wm_image_height / 2);
						imagecopy ( $this->thumbnail, $this->wm_image, $middle_x, $middle_y, 0, 0, $this->wm_image_width, $this->wm_image_height );
						break;
				} // end switch
				unset ( $this->wm_image );
			} // end if
		} // end foreach
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
	 * @uses createThumbnail()
	 * @uses CachedThumbnail::outputThumbnail()
	 */
	function outputThumbnail($format = 'png', $quality = 75) {
		parent::setOutputFormat ( $format );
		parent::setCache ();
		if ($this->cache_time === 0 || $this->cache->isPictureCached () === FALSE) {
			$this->createThumbnail ();
			if ($this->cache_time > 0) {
				$this->cache->writePictureCache ( $this->thumbnail, 100 );
			} // end if
		} // end if
		parent::outputThumbnail ( $format, $quality );
	} // end function
	

	/**
	 * returns the variable with the thumbnail image
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @return mixed
	 * @access public
	 * @uses createThumbnail()
	 * @uses CachedThumbnail::returnThumbnail()
	 */
	function returnThumbnail($format = 'png') {
		parent::setOutputFormat ( $format );
		parent::setCache ();
		if ($this->cache_time === 0 || $this->cache->isPictureCached () === FALSE) {
			$this->createThumbnail ();
			if ($this->cache_time > 0) {
				$this->cache->writePictureCache ( $this->thumbnail, 100 );
			} // end if
		} // end if
		return parent::returnThumbnail ( $format );
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
		parent::setCache ();
		$path = $this->cache->getCacheFilepath ( $format, $quality );
		if ($path != FALSE) {
			return ( string ) $path;
		} else { // trys to create cache and return filename
			$this->createThumbnail ();
			$this->cache->writePictureCache ( $this->thumbnail, $quality );
			return $this->cache->getCacheFilepath ( $format, $quality );
		} // end if
	} // end function


} // end class CachedThumbnail
?>