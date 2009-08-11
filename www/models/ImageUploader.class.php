<?php
class ImageUploader extends Model {
	var $mSize;
	var $mFileName;
	var $mFileMime;
	var $mFileTmpName;
	var $mFileExt;
	var $mErrorNo;

	function setFileValues($file) {
		//size
		$this->mSize = $file ['size'];
		//filename
		$this->mFileName = $file ['name'];
		//type
		$this->mFileMime = $file ['type'];
		//tmp_name
		$this->mFileTmpName = $file ['tmp_name'];
		//file ext
		$this->mFileExt = strrchr ( $this->mFileName, '.' );
		//error_no
		$this->mErrorNo = $file ['error'];
	}
	function validateUploaded() {
		if ($this->mErrorNo == 0) {
			return true;
		} else {
			return false;
		}
	}
	function validateFileType() {
		$im = '';

		if (($this->mFileMime == "image/jpeg") || ($this->mFileMime == "image/pjpeg")) {
			$im = imagecreatefromjpeg ( $this->mFileTmpName );
		} elseif ($this->mFileMime == "image/x-png") {
			$im = imagecreatefrompng ( $this->mFileTmpName );
		} elseif ($this->mFileMime == "image/gif") {
			$im = imagecreatefromgif ( $this->mFileTmpName );
		} elseif ($this->mFileMime == "application/x-shockwave-flash") {
			$im = 'swf';
		}
		if ($im) {
			return true;
		} else {
			return false;
		}
	}
	function validateSize($maxWidth, $maxHeight) {
		list ( $width, $height, $type, $attr ) = getimagesize ( $this->mFileTmpName );
		if (($width <= $maxWidth) && ($height <= $maxHeight)) {
			return true;
		} else {
			return false;
		}
	}
	function uploadAndThumbnail($maxWidth, $maxHeight, $todir, $tofile, $quality = 85) {
		$this->addComponent ( 'wmthumbnail' );
		$wm = new WMThumbnail ( $this->mFileTmpName, 0 );
		$wm->setMaxWidth ( $maxWidth );
		$wm->setMaxHeight ( $maxHeight );

		//if($is_wm){
		//$thumbnail->addLogo($logourl, $position, 1);
		//}


		$mix = $wm->save ( $todir . "/" . $tofile, $quality );
		@chmod ( $todir . "/" . $tofile, 0777 );
	}
	function upload($todir, $tofile) {
		@move_uploaded_file ( $this->mFileTmpName, $todir . "/" . $tofile );
		@chmod ( $todir . "/" . $tofile, 0777 );
	}
	function getOriName() {
		return $this->mFileName;
	}
	function getName() {
		return md5 ( microtime () ) . $this->mFileExt;
	}
}
?>