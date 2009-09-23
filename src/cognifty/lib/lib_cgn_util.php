<?php

/**
 * Universal Unique ID (UUID or GUID)
 * Taken from http://us2.php.net/manual/en/function.uniqid.php
 * Thanks to all the comment posters, including:
 * maciej dot strzelecki, dholmes, and mimic
 */
function cgn_uuid() {
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) 
	);
}


/**
 * Create a URL save, and generally Internet safe, string.
 * Change space for underscore, change question marks to underscores, etc...
 * then change all double underscores to a single underscore
 *
 * This is useful for cleaning up filenames and strange document titles.
 */
function cgn_link_text($lt) {

	$lt = trim($lt);
	$lt = str_replace('&', ' and ', $lt);
	$lt = str_replace(' ', '_', $lt);

	$pattern = '/[\x{21}-\x{2C}]|[\x{2F}]|[\x{5B}-\x{5E}]|[\x{7E}]/';
	$preglt = preg_replace($pattern, '_', $lt);
	if ($preglt == '') {
		//preg throws an error if the pattern cannot compile
		//(old preg libraries)
		$e = Cgn_ErrorStack::pullError('php');
		$len = strlen($lt);
		for($i = 0; $i < $len; $i++) {
			$hex =ord($lt{$i});
			if ($hex < 44 || $hex == 47 ) {
				$lt{$i} = '_';
			}

			if ($hex >= 91 && $hex <= 94 ) {
				$lt{$i} = '_';
			}
			if ($hex == 126 ) {
				$lt{$i} = '_';
			}
		}
		$preglt = $lt;
	}

	$lt = str_replace('___', '_', $preglt);
	$lt = str_replace('__', '_', $lt);
	$lt = str_replace('__', '_', $lt);

	return $lt;
}

function cgn_intToToken($int) {
	//10,000,000
	$crc =  substr(sprintf('%u',crc32($int)), 0, 3);
	$tok =  base_convert( $x.'a'.$crc, 11,26);
	return $tok;

}

function cgn_tokenToInt($tok) {
	$newtok = base_convert($tok,26,11);
	list($num, $expectCrc) =  explode('a',$newtok);
	//$crc =  substr(sprintf('%u',crc32($int)), 0, 3);
	//$crc should == $expectCrc
	return (int)$num;
}


