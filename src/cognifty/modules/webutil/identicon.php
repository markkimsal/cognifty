<?php

Cgn::loadLibrary('Vis::Lib_Cgn_Vis_Identicon');

class Cgn_Service_Webutil_Identicon extends Cgn_Service {

	var $presenter = 'self';

	public function mainEvent($req, &$t) {

		$seed = $req->cleanString('id');
		if (!$seed)
			$seed = $req->cleanString('seed');
		if (!$seed && isset($req->getvars[0]))
		   	$seed = Cgn::removeCtrlChar($req->getvars[0]);

		if (!$seed) {
			return false;
		}
		if (strlen($seed) < 1) {
			return false;
		}

		//default ingeger sizse
		$size = $req->cleanInt('s');
		if (!$size)
			$size = $req->cleanInt('size');
		if (!$size)
			$size = 64;


		$blocks = $req->cleanInt('b');
		if ($blocks != 36 && $blocks != 16 && $blocks != 25) {
			$blocks = 16;
		}

		$manual = $req->cleanString('s');
		if ($manual == 'l') {
			//large size
			$size = 128;$blocks = 36;
		}
		if ($manual == 'm') {
			//medium size
			$size = 64;$blocks = 16;
		}

		if ($manual == 's') {
			//medium size
			$size = 32;$blocks = 9;
		}

		$t['icon'] = new Cgn_Vis_Identicon_Geometry($seed, $size, $size, $blocks);
		if (!$t['icon']->buildIcon()) {
			//there was an error
			//destroy the icon object so output will fail
			$t['icon'] = NULL;
		}
	}

	public function debugEvent($req, &$t) {
		$t['icon'] = new Cgn_Vis_Identicon_Geometry($blueSeed, 128, 128, 36);
		$t['icon']->buildDebugIcon();
	}

	/**
	 * Show an image with image header
	 */
	public function output($req, $t) {
		if (!is_object($t['icon'])
			|| isset($t['error'])) {
			header('Content-type: image/png');
			$f = fopen(dirname(__FILE__).'/identicon_err.png', 'r');
			fpassthru($f);fclose($f);
			return false;
		}
		header('Cache-Control: public,max-age=5000');
		header('Pragma: pragma');
		header('Expires: '.gmdate("D, d M Y H:i:s", time()+(86400*100)));
		header('Content-type: image/png');
		echo $t['icon']->getIcon();
	}
}


