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
 * Creates a thumbnail from a source image with GD2 functions
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
class Thumbnail {
	
	/*-------------------*/
	/* V A R I A B L E S */
	/*-------------------*/
	
	/**#@+
	 * @access protected
	 */
	/**
	 * possible image formats
	 *
	 * @var array
	 */
	var $formats = array ('gif' => 1, 'jpg' => 2, 'png' => 3, 'wbmp' => 15, 'string' => 999 );
	
	/**
	 * maximal height of the generated thumbnail
	 *
	 * @var int
	 */
	var $thumb_max_height = 75;
	
	/**
	 * maximal width of the generated thumbnail
	 *
	 * @var int
	 */
	var $thumb_max_width = 75;
	
	/**
	 * quality or speed when generating the thumbnail
	 *
	 * @var boolean
	 */
	var $quality_thumb = TRUE;
	
	/**
	 * path/filename of imagefile
	 *
	 * @var string
	 */
	var $image_path;
	
	/**
	 * @var int
	 */
	var $image_width;
	
	/**
	 * @var int
	 */
	var $image_height;
	
	/**
	 * image format of source
	 *
	 * @var int
	 */
	var $image_type;
	
	/**
	 * @var int
	 */
	var $thumbnail_height;
	
	/**
	 * @var int
	 */
	var $thumbnail_width;
	
	/**
	 * image format of the thumbnail
	 *
	 * @var int
	 */
	var $thumbnail_type = 3;
	
	/**
	 * @var resource
	 */
	var $image;
	
	/**
	 * @var resource
	 */
	var $thumbnail;
	
	/**
	 * @var string
	 */
	var $version = '1.001';
	
	/**#@-*/
	
	/*-----------------------*/
	/* C O N S T R U C T O R */
	/*-----------------------*/
	
	/**
	 * Constructor
	 *
	 * @param string $file  path/filename of picture or stream from a DB field
	 * @return void
	 * @access protected
	 * @uses $image_path
	 */
	function Thumbnail($file = '') {
		
		$this->image_path = ( string ) $file;
		
		$this->readSourceImageData ();
		
		$this->thumb_max_height = $this->image_height;
		$this->thumb_max_width = $this->image_width;
	} // end constructor
	

	/*-------------------*/
	/* F U N C T I O N S */
	/*-------------------*/
	
	/**
	 * sets the output type of the thumbnail
	 *
	 * @param string $format gif, jpg, png, wbmp
	 * @return void
	 * @access public
	 * @uses $thumbnail_type
	 * @uses $formats
	 */
	function setOutputFormat($format = 'png') {
		if (array_key_exists ( trim ( $format ), $this->formats )) {
			$this->thumbnail_type = $this->formats [trim ( $format )];
		} // end if
	} // end function
	

	/**
	 * sets the max. height of the thumbnail
	 *
	 * @param int $height
	 * @return boolean
	 * @access public
	 * @uses readSourceImageData()
	 * @uses $image_height
	 * @uses $thumb_max_height
	 */
	function setMaxHeight($height = 0) {
		$height = ( int ) $height;
		if ($height <= $this->image_height && $height >= 0) {
			$this->thumb_max_height = $height;
			return ( boolean ) TRUE;
		} // end if
		return ( boolean ) FALSE;
	} // end function
	

	/**
	 * sets the max. width of the thumbnail
	 *
	 * @param int $width
	 * @return boolean
	 * @access public
	 * @uses readSourceImageData()
	 * @uses $image_width
	 * @uses $thumb_max_width
	 */
	function setMaxWidth($width = 0) {
		$width = ( int ) $width;
		if ($width <= $this->image_width && $width >= 0) {
			$this->thumb_max_width = $width;
			return ( boolean ) TRUE;
		} // end if
		return ( boolean ) FALSE;
	} // end function
	

	/**
	 * sets the max. width and height of the thumbnail
	 *
	 * passes values to the functions setMaxHeight() and setMaxWidth()
	 *
	 * @param int $width
	 * @param int $height
	 * @return boolean
	 * @access public
	 * @uses setMaxHeight()
	 * @uses setMaxWidth()
	 */
	function setMaxSize($width = 0, $height = 0) {
		if ($this->setMaxWidth ( $width ) === TRUE && $this->setMaxHeight ( $height ) === TRUE) {
			return ( boolean ) TRUE;
		} else {
			return ( boolean ) FALSE;
		} // end if
	} // end function
	

	/**
	 * whether to create thumbs fast or with good quality
	 *
	 * @param boolean $boolean
	 * @return void
	 * @access public
	 * @uses $quality_thumb
	 */
	function setQualityOutput($boolean = TRUE) {
		$this->quality_thumb = ( boolean ) $boolean;
	} // end function
	

	/**
	 * reads metadata of the source image
	 *
	 * @return void
	 * @access protected
	 * @uses $image_width
	 * @uses $image_height
	 * @uses $image_type
	 * @uses $formats
	 */
	function readSourceImageData() {
		if (! file_exists ( $this->image_path )) { // if source pic wasnt found
			//hack by kakapo at 2008-05-16 start 
			$tmp_file = dirname ( __FILE__ ) . "/../tmp/" . 'tmp_img' . rand ();
			$fp = @fopen ( $tmp_file, 'w' );
			@fwrite ( $fp, $this->image_path );
			@fclose ( $fp );
			list ( $this->image_width, $this->image_height, $this->image_type, $attr ) = getimagesize ( $tmp_file );
			switch ($this->image_type) {
				case 1 :
					$this->image = imagecreatefromgif ( $tmp_file );
					break;
				case 2 :
					$this->image = imagecreatefromjpeg ( $tmp_file );
					break;
				case 3 :
					$this->image = imagecreatefrompng ( $tmp_file );
					break;
			}
			unset ( $attr );
			@unlink ( $tmp_file );
			//hack by kakapo at 2008-05-16 end 
		//			$this->image_path = 'error_pic';
		//			$this->image_width =& $this->thumb_max_width;
		//			$this->image_height =& $this->thumb_max_height;
		//			$this->image = @imagecreatetruecolor($this->image_width, $this->image_height)
		//							or die ('Cannot Initialize new GD image stream');
		//			$text_color = imagecolorallocate($this->image, 255, 255, 255);
		//			imagestring($this->image, 1, 2,
		//					    ($this->image_height / 2 - 10),
		//					    "Could't find", $text_color);
		//			imagestring($this->image, 1, 2,
		//					    ($this->image_height / 2 - 4),
		//					    'source image', $text_color);
		//			imagestring($this->image, 1, 2,
		//						($this->image_height / 2 + 4),
		//						'(Thumbnail V' . $this->version . ')', $text_color);
		} else {
			if (! isset ( $this->image_width )) {
				list ( $this->image_width, $this->image_height, $this->image_type, $attr ) = getimagesize ( $this->image_path );
				unset ( $attr );
				if (! in_array ( $this->image_type, $this->formats )) {
					die ( "Can't create thumbnail from '" . $this->image_type . "' source: " . $this->image_path );
				} // end if
			} // end if
		} // end if
	} // end function
	

	/**
	 * reads the source image into a variable
	 *
	 * @return void
	 * @access protected
	 * @uses $image
	 * @uses readSourceImageData()
	 * @uses $image_type
	 * @uses $image_path
	 */
	function readSourceImage() {
		if (! isset ( $this->image )) {
			
			switch ($this->image_type) {
				case 1 :
					$this->image = imagecreatefromgif ( $this->image_path );
					break;
				case 2 :
					$this->image = imagecreatefromjpeg ( $this->image_path );
					break;
				case 3 :
					$this->image = imagecreatefrompng ( $this->image_path );
					break;
				case 15 :
					$this->image = imagecreatefromwbmp ( $this->image_path );
					break;
				case 999 :
				default :
					$this->image = imagecreatefromstring ( $this->image_path );
					break;
			} // end switch
		} // end if
	

	} // end function
	

	/**
	 * sets the actual width and height of the thumbnail based on the source image size and the max limits for the thumbnail
	 *
	 * @return void
	 * @access protected
	 * @uses readSourceImageData()
	 * @uses $image_height
	 * @uses $thumb_max_height
	 * @uses $image_width
	 * @uses $thumb_max_width
	 */
	function setThumbnailSize() {
		//added  by kakapo 2006-05-10 start
		$resizewidth = '';
		$resizeheight = '';
		if (($this->thumb_max_width && $this->image_width > $this->thumb_max_width) || ($this->thumb_max_height && $this->image_height > $this->thumb_max_height)) {
			if ($this->thumb_max_width && $this->image_width > $this->thumb_max_width) {
				$widthratio = $this->thumb_max_width / $this->image_width;
				$resizewidth = true;
			}
			if ($this->thumb_max_height && $this->image_height > $this->thumb_max_height) {
				
				$heightratio = $this->thumb_max_height / $this->image_height;
				$resizeheight = true;
			}
			
			if ($resizewidth && $resizeheight) {
				if ($widthratio < $heightratio) {
					$sizefactor = $widthratio;
				} else {
					$sizefactor = $heightratio;
				}
			} elseif ($resizewidth) {
				$sizefactor = $widthratio;
			
			} elseif ($resizeheight) {
				$sizefactor = $heightratio;
			}
		
		} else {
			$sizefactor = ( int ) 1;
		}
		//added  by kakapo 2006-05-10 end
		

		/* bug
	    if (($this->image_height > $this->thumb_max_height)
	    	|| ($this->image_width < $this->thumb_max_width)) {
	        $sizefactor = (double) (($this->image_height > $this->image_width) ? ($this->thumb_max_height / $this->image_height) : ($this->thumb_max_width / $this->image_width));
	    } else {
	        $sizefactor = (int) 1;
	    } // end if
	    
	    */
		$this->thumbnail_width = ( int ) ($this->image_width * $sizefactor);
		$this->thumbnail_height = ( int ) ($this->image_height * $sizefactor);
		unset ( $sizefactor );
	} // end function
	

	/**
	 * sets the actual width and height of the thumbnail based on the source image size and the max limits for the thumbnail
	 *
	 * @return void
	 * @access protected
	 * @uses readSourceImageData()
	 * @uses $image_height
	 * @uses $thumb_max_height
	 * @uses $image_width
	 * @uses $thumb_max_width
	 */
	function setThumbnailSize_kakapo() {
		//add by kakapo 2006-05-10
		if (($this->thumb_max_width && $this->image_width > $this->thumb_max_width) || ($this->thumb_max_height && $this->image_height > $this->thumb_max_height)) {
			if ($this->thumb_max_width && $this->image_width > $this->thumb_max_width) {
				$widthratio = $this->thumb_max_width / $this->image_width;
				$resizewidth = true;
			}
			if ($this->thumb_max_height && $this->image_height > $this->thumb_max_height) {
				
				$heightratio = $this->thumb_max_height / $this->image_height;
				$resizeheight = true;
			}
			
			if ($resizewidth && $resizeheight) {
				if ($widthratio > $heightratio) {
					$sizefactor = $widthratio;
				} else {
					$sizefactor = $heightratio;
				}
			} elseif ($resizewidth) {
				$sizefactor = $widthratio;
			
			} elseif ($resizeheight) {
				$sizefactor = $heightratio;
			}
		
		} else {
			$sizefactor = ( int ) 1;
		}
		
		/* bug
	    if (($this->image_height > $this->thumb_max_height)
	    	|| ($this->image_width < $this->thumb_max_width)) {
	        $sizefactor = (double) (($this->image_height > $this->image_width) ? ($this->thumb_max_height / $this->image_height) : ($this->thumb_max_width / $this->image_width));
	    } else {
	        $sizefactor = (int) 1;
	    } // end if
	    
	    */
		$this->thumbnail_width = ( int ) ($this->image_width * $sizefactor);
		$this->thumbnail_height = ( int ) ($this->image_height * $sizefactor);
		unset ( $sizefactor );
	} // end function
	/**
	 * creates the thumbnail and saves it to a variable
	 *
	 * @return void
	 * @access protected
	 * @uses setThumbnailSize()
	 * @uses readSourceImage()
	 * @uses $thumbnail
	 * @uses $thumbnail_width
	 * @uses $thumbnail_height
	 * @uses $quality_thumb
	 * @uses $image
	 * @uses $image_width
	 * @uses $image_height
	 */
	function createThumbnail() {
		$this->setThumbnailSize ();
		$this->readSourceImage ();
		
		if (! isset ( $this->thumbnail )) {
			
			if (! in_array ( $this->thumb_max_width, array (75, 120, 100, 66 ) )) {
				$this->thumbnail = imagecreatetruecolor ( $this->thumbnail_width, $this->thumbnail_height );
				
				if ($this->quality_thumb === TRUE) {
					imagecopyresampled ( $this->thumbnail, $this->image, 0, 0, 0, 0, $this->thumbnail_width, $this->thumbnail_height, $this->image_width, $this->image_height );
				} else {
					imagecopyresized ( $this->thumbnail, $this->image, 0, 0, 0, 0, $this->thumbnail_width, $this->thumbnail_height, $this->image_width, $this->image_height );
				} // end if
			

			} else {
				
				$this->setThumbnailSize_kakapo ();
				$tmp_thumb = imagecreatetruecolor ( $this->thumbnail_width, $this->thumbnail_height );
				
				if ($this->quality_thumb === TRUE) {
					imagecopyresampled ( $tmp_thumb, $this->image, 0, 0, 0, 0, $this->thumbnail_width, $this->thumbnail_height, $this->image_width, $this->image_height );
				} else {
					imagecopyresized ( $tmp_thumb, $this->image, 0, 0, 0, 0, $this->thumbnail_width, $this->thumbnail_height, $this->image_width, $this->image_height );
				}
				
				$this->thumbnail = imagecreatetruecolor ( $this->thumb_max_width, $this->thumb_max_height );
				imagecopy ( $this->thumbnail, $tmp_thumb, 0, 0, 0, 0, $this->thumb_max_width, $this->thumb_max_height );
				
				imagedestroy ( $tmp_thumb );
			
			}
		
		} // end if
	

	} // end function
	

	/**
	 * outputs the thumbnail to the browser
	 *
	 * @param string $format
	 * @param int $quality
	 * @return void
	 * @access public
	 * @uses setOutputFormat()
	 * @uses createThumbnail()
	 * @uses $thumbnail_type
	 * @uses $thumbnail
	 */
	function outputThumbnail($format = 'png', $quality = 75) {
		$this->setOutputFormat ( $format );
		$this->createThumbnail ();
		switch ($this->thumbnail_type) {
			case 1 :
				header ( 'Content-type: image/gif' );
				imagegif ( $this->thumbnail );
				break;
			case 2 :
				$quality = ( int ) $quality;
				if ($quality < 0 || $quality > 100) {
					$quality = 75;
				} // end if
				

				header ( 'Content-type: image/jpeg' );
				imagejpeg ( $this->thumbnail, '', $quality );
				break;
			case 3 :
				header ( 'Content-type: image/png' );
				imagepng ( $this->thumbnail );
				break;
			case 15 :
				header ( 'Content-type: image/vnd.wap.wbmp' );
				imagewbmp ( $this->thumbnail );
				break;
		} // end switch
		imagedestroy ( $this->thumbnail );
		imagedestroy ( $this->image );
	} // end function
	

	/**
	 * returns the variable with the thumbnail image
	 *
	 * @param string $format
	 * @return mixed
	 * @access public
	 * @uses setOutputFormat()
	 * @uses createThumbnail()
	 * @uses $thumbnail
	 */
	function returnThumbnail() {
		$this->setOutputFormat ();
		$this->createThumbnail ();
		return $this->thumbnail;
	} // end function
	

	/**
	 * returns the height of the thumbnail
	 *
	 * @return int
	 * @access public
	 * @uses $thumbnail_height
	 */
	function getThumbHeight() {
		$this->createThumbnail ();
		return ( int ) $this->thumbnail_height;
	} // end function
	

	/**
	 * returns the width of the thumbnail
	 *
	 * @return int
	 * @access public
	 * @uses $thumbnail_width
	 */
	function getThumbWidth() {
		$this->createThumbnail ();
		return ( int ) $this->thumbnail_width;
	} // end function
	

	/**
	 * returns the width of the thumbnail
	 *
	 * @return int
	 * @access public
	 * @uses $thumbnail_width
	 */
	function getPictureName() {
		return ( string ) $this->image_path;
	} // end function
	

	/**
	 * ����ͼƬ��һ���ļ���ȥ
	 * 
	 * @access public
	 */
	function save($fileName = null, $q = 75) {
		$contents = $this->returnThumbnail ();
		
		if ($this->image_type == 2) {
			return imagejpeg ( $contents, $fileName, $q );
		}
		if ($this->image_type == 1) {
			return imagegif ( $contents, $fileName );
		}
		if ($this->image_type == 3) {
			return imagepng ( $contents, $fileName, $q / 10 );
		}
	
	}

} // end class Thumbnail
?>