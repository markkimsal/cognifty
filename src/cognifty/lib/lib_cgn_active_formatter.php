<?php


class Cgn_ActiveFormatter {

	var $_varVal;
	var $_varFmt = NULL;

	function Cgn_ActiveFormatter($val='',$format='') {
		$this->_varVal  = $val;
		if ($format !== '') {
			$this->_varFmt = $format;
		}
	}


	function printAs($type) {

		switch($type) {
			case 'phone':
				$clean = $this->cleanVar($this->_varVal);
				$clean = $this->cleanSpaces($clean);
				return Cgn_ActiveFormatter_Printer::printAsPhone(
					$clean,
					$this->_varFmt
				);
			break;
			case 'email':
				$clean = $this->cleanEmail($this->_varVal);
				return Cgn_ActiveFormatter_Printer::printAsEmail(
					$clean,
					$this->_varFmt
				);

			break;
		}
		return $this->_varVal;
	}

	public function cleanVar($v) { 
		return preg_replace('/([^[:alpha:]0-9[:space:]])+/u', '', $v);
	}

	public function cleanSpaces($v) { 
		return preg_replace('/[[:space:]]+/u', '', $v);
	}

	public function cleanEmail($v) { 
		return preg_replace('/([^[:alpha:]0-9[:space:]@\.])+/u', '', $v);
	}

}

class Cgn_ActiveFormatter_Settings {

	public static function getDefault($type) {
		switch($type) {
			case 'phone':
				return '(%d) %d-%d';

			case 'email-full':
				return '%s <%s>';

			case 'email':
				return '%s';
		}
	}
}

class Cgn_ActiveFormatter_Printer {

	public static function printAsPhone($clean,$format='') {
		if ($format == '') {
			$format = Cgn_ActiveFormatter_Settings::getDefault('phone');
		}
		$prefix = substr($clean,0,3);
		$first = substr($clean,3,3);
		$second = substr($clean,6,4);
		return sprintf($format, $prefix, $first, $second);
	}


	public static function printAsEmail($clean,$format='') {
		//full formatting has "Name <user@domain.com>"
		if ($format == '') {
			if (strstr($clean, ' ') !== FALSE) {
				$format = Cgn_ActiveFormatter_Settings::getDefault('email-full');
			} else {
				$format = Cgn_ActiveFormatter_Settings::getDefault('email');
			}
		}

		//find the email part, and the rest is the 'name' part
		if (strstr($clean, ' ') !== FALSE) {
			$fullName = '';
			$email = '';
			$parts = explode(' ',$clean);
			foreach ($parts as $p) {
				if (strstr($p,'@')) {
					$email = $p;
				} else {
					if ($fullName !=='') {$fullName .= ' ';}
					$fullName .= $p;
				}
			}
			$toFormat = array($fullName, $email);
		} else {
			$toFormat = array($clean);
		}

		return vsprintf($format, $toFormat);
	}
}
?>
