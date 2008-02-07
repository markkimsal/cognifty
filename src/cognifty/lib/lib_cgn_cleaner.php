<?php

/**
 * Cleans input arguments
 * magic quotes has already been stripped out.
 */
class Cgn_Cleaner {

	/**
	 * return a clean and verified e-mail
	 */
	function getEmail($em) {
		return Cgn_Cleaner::getStringAs($em,'email');
	}

	/**
	 * return a string cleaned as a certain type
	 */
	function getStringAs($str,$type='string') {
		return $str;
	}

}

function cgn_cleanPassword($em) {
	return Cgn_Cleaner::getStringAs($em);
}

function cgn_cleanEmail($em) {
	return Cgn_Cleaner::getEmail($em);
}
