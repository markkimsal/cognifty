<?php

/**
 * Account image
 *
 *
 * @emit account_image_save_after
 */
class Cgn_Service_Account_Img extends Cgn_Service {


	public $account  = NULL;
	public $user     = NULL;

	public $usesPerm = true;

	function Cgn_Service_Account_Img() {
	}

	public function hasAccess($u, $eventName) {
		if ($eventName == 'main') {
			return TRUE;
		}
		//for all other events, require a login
		return !$u->isAnonymous();
	}

	/**
	 * Signal whether or not the user can access the event $e of this service
	 *
	 * @return boolean  True if user has permission or service doesn't "usePerms"
	 */
	function authorize($e, $u) {
		//allow anyone to see images
		if ($e == 'main') {
			return TRUE;
		}
		if ($this->requireLogin && $u->isAnonymous() ) {
			return FALSE;
		}
		//if we don't specify permissions, then allow access
		if (!$this->usesPerms) {
			return TRUE;
		}

		return $this->hasAccess($u, $this->eventName);
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
		$a = new Cgn_DataItem('cgn_account');

		if ($aid =  $req->cleanInt(0)) {
			$a->load( $aid );
			$uid = $a->get('cgn_user_id');
		} else {
			//get current user
			$u = $req->getUser();
			$uid = $u->userId;
			$a->load( array('cgn_user_id = '.$uid) );
		}

		$aid = $a->get('cgn_account_id');

		//make the final filename
		$t['file'] = Cgn_User_Account::getImageFilename(Cgn_User_Account::makeAccountImageBasename($uid, $aid));

		$this->presenter = 'self';
	}


	/**
	 * Show the account icon, and an upload form
	 */
	function editEvent($req, &$t) {

		$t['picForm'] = $this->_loadPictureForm();
	}

	/**
	 * Resize the icon
	 *
	 * @param File $pic   binary file upload
	 * @param int   $id   ID of the user
	 */
	function saveEvent($req, &$t) {
		Cgn::loadModLibrary('Account::Account_Base');

		$u = $req->getUser();
		$a = Account_Base::loadByUserId($u->userId);
		if ($a->_dataItem->_isNew) {
			$a->save();
		}

		$file = $this->saveAccountImage($u->userId, $a->_dataItem->get('cgn_account_id'), 'pic');

		$this->user    = $u;
		$this->account = $a;
		$this->emit('account_image_save_after');
		unset($this->account);
		unset($this->user);

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

	/**
	 *  Create a form for uploading a new picture
	 */
	protected function _loadPictureForm() {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_widgets::lib_cgn_widget');
		$f = new Cgn_Form('form_upload_profile_pic', '', 'POST', 'multipart/form-data');
		$f->width      = '40em';
		$f->formHeader = 'Select an image file on your computer (4MB max)';

		$f->layout = new Cgn_Form_Layout_Dl();

		$f->action = cgn_sappurl('account', 'img', 'save');

		$f->appendElement(new Cgn_Form_ElementFile('pic', ''));

		return $f;
	}
}
