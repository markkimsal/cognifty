<?php

/**
 * Account image
 *
 */
class Cgn_Service_Account_Img extends Cgn_Service {

	var $requireLogin = true;

	function Cgn_Service_Account_Main() {
	}

	/**
	 * Show the account icon.
	 *
	 * @param String $i   file hash of account GUID
	 * @param int    $uid GUID of account
	 */
	function mainEvent($req, &$t) {
		$t['file'] = $req->cleanString('i');
		if ($t['file']) {
			return TRUE;
		}
		Cgn::loadLibrary('Acct::Lib_Cgn_Account');
		if ($uid =  $req->cleanInt(0)) {

		} else {
			//get current user
			$u = $req->getUser();
			$uid = $u->userId;
		}

		$a = new Cgn_DataItem('cgn_account');
		$a->load('cgn_user_id', $u->userId);
		$aid = $a->get('cgn_account_id');

		//make the final filename
		$t['file'] = Cgn_User_Account::getImageFilename(Cgn_User_Account::makeAccountImageBasename($uid, $aid));
//		var_dump($t['file']);
//		exit();

		$this->presenter = 'self';
	}


	/**
	 * Show the account icon, and an upload form
	 */
	function editEvent($req, &$t) {

	}

	/**
	 * Resize the icon
	 *
	 * @param File $pic   binary file upload
	 * @param int   $id   ID of the user
	 */
	function saveEvent($req, &$t) {

		$u = $req->getUser();
		$a = new Cgn_DataItem('cgn_account');
		$a->load('cgn_user_id', $u->userId);
		$file = $this->saveAccountImage($u->userId, $a->get('cgn_account_id'), 'pic');
		if (!$file) {
			$u->addMessage("Sorry, cannot save your image.", 'msg_warn');
			return FALSE;
		}
		$ret = $this->resizeImage($file, $_FILES['pic']['type'], 70);
		$u->addSessionMessage("Your picture has been updated.");
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account');
	}

	/**
	 * Output the account image with content-type header
	 */
	function output($req, &$t) {
		/*
		$start = BASE_DIR.'var/acct_img';
		$name = basename($t['file']);
		$one = $start.'/'.substr($name, 0, 1);
		$two = $one.'/'.substr($name, 1, 1);
		$f = @fopen($two.'/w'.$name, 'r');
		 */
		$f = @fopen($t['file'], 'r');
		if (!$f) {
			$f = fopen(BASE_DIR.'/media/icons/default/user_icon.png', 'r');
		}

		header('Cache-Control: public,max-age=5000');
		//expire in 1 hour, keeps browser from re-requesting every hit
		header('Pragma: pragma');
		header('Expires: '.gmdate("D, d M Y H:i:s", time()+3600));
		header('Content-Type: image/png');
		//$f = fopen(BASE_DIR.'/media/icons/default/user_icon.png', 'r');
		fpassthru($f);
		fclose($f);
	}


	/**
	 * Move apache file uploads into the proper dir
	 *
	 * @return String the final destination of the saved image.
	 */
	public function saveAccountImage( $uid, $aid, $name='pic') {
		Cgn::loadLibrary('Acct::Lib_Cgn_Account');
		//make the final filename
		$baseName = Cgn_User_Account::makeAccountImageBasename($uid, $aid);
		$destDir = $this->makeBalanceDir($baseName);
		if (!$destDir) {
			return FALSE;
		}
		$target = $destDir.'/'.$baseName;

		//check for apache file uploads
		if (is_array($_FILES) && isset($_FILES[$name]) && $_FILES[$name]['error'] == 0) {
			move_uploaded_file($_FILES[$name]['tmp_name'],$target);
		}
		return $target;
	}

	public function makeBalanceDir($name) {
		$start = BASE_DIR.'var/acct_img';
		if (!is_dir($start)) {
			if(!@mkdir($start))  return FALSE;
		}

		$one = $start.'/'.substr($name, 0, 1);
		$two = $one.'/'.substr($name, 1, 1);

		if (!is_dir($one)) {
			if(!@mkdir($one)) return FALSE;
		}
		if (!is_dir($two)) {
			if(!@mkdir($two)) return FALSE;
		}
		return $two;
	}

	public function resizeImage($file, $mime, $maxWidth=-1, $maxHeight=-1) {
		//if we got no resize dimension, do nothing
		if ($maxWidth == -1 && $maxHeight == -1) {
			return TRUE;
		}

		//rely on GD
		if (!function_exists('imagecreate')) { return; }
/*
		if ($this->dataItem->mime == '') {
			$this->figureMime();
		} else {
			$this->mimeType = $this->dataItem->mime;
		}

		$tmpfname = tempnam('/tmp/', "cgnimg_");
		$si = fopen($tmpfname, "w+b");
*/

		switch ($mime) {
			case 'image/png':
			$orig = imageCreateFromPng($file);
			break;

			case 'image/jpeg':
			case 'image/jpg':
			$orig = imageCreateFromJpeg($file);
			break;

			case 'image/gif':
			$orig = imageCreateFromGif($file);
			break;
		}
		if (!$orig) { 
			return FALSE;
		}

		$width  = imageSx($orig);
		$height = imageSy($orig);
		if ($width > $maxWidth) {
			//resize proportionately
			$ratio = $maxWidth / $width;
			$newwidth  = $maxWidth;
			$newheight = $height * $ratio;
		} else {
			$newwidth = $width;
			$newheight = $height;
		}

		$thumbwidth = 50;
		$thumbheight = 50;

		if ($width > $thumbwidth) {
			//resize proportionately
			$ratio = $thumbwidth / $width;
			$new2width  = $thumbwidth;
			$new2height = intval($height * $ratio);
		} else {
			//Check if image is really tall and thin.
			//Don't do this for the medium size image because 
			// vertically tall images aren't a problem for most layouts.
			if ($height > $thumbheight) {
				$ratio = $thumbheight / $height;
				$new2height  = $thumbheight;
				$new2width   = intval($width * $ratio);
			} else {
				//use defaults, image is small enough 
				$new2width = $width;
				$new2height = (int)$height;
			}
		}
		$webImage = imageCreateTrueColor($newwidth,$newheight);
		if (!$webImage) { die('no such handle');}
		imageCopyResampled(
			$webImage, $orig,
			0, 0,
			0, 0,
			$newwidth, $newheight,
			$width, $height);



		$thmImage = imageCreateTrueColor($new2width,$new2height);
		imageCopyResampled(
			$thmImage, $orig,
			0, 0,
			0, 0,
			$new2width, $new2height,
			$width, $height);

/*
header('Content-type: image/png');
imagePng($thmImage);
exit();
 */
		$dir = dirname($file);
		$base = basename($file);

//		ob_start(); // start a new output buffer
		imagePng( $webImage, $dir.'/w'.$base.'.png', 6);

//		$this->dataItem->web_image = ob_get_contents();
//		ob_end_clean(); // stop this output buffer
		imageDestroy($webImage);

//		ob_start(); // start a new output buffer
		imagePng( $thmImage, $dir.'/t'.$base.'.png', 6 );
//		$this->dataItem->thm_image = ob_get_contents();
//		ob_end_clean(); // stop this output buffer
		imageDestroy($thmImage);
	}
}
?>
