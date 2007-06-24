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
function cgn_link_text($t) {
	$t = trim($t);
	$t = str_replace(' ',  '_', $t);
	$t = str_replace(',',  '_', $t);
	$t = str_replace('\'', '_', $t);
	$t = str_replace('"',  '_', $t);
	$t = str_replace('?',  '_', $t);
	$t = str_replace('!',  '_', $t);
	$t = str_replace('__', '_', $t);
	return $t;
}
?>
