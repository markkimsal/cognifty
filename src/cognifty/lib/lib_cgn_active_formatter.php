<?php


class Cgn_ActiveFormatter {

	var $_varVal;
	var $_varFmt  = NULL;
	var $_varType = NULL;
	var $_htmlEscape = TRUE;

	function Cgn_ActiveFormatter($val='', $type='', $format='') {
		$this->_varVal  = $val;
		if ($type   !== '') {
			$this->_varType = $type;
		}
		if ($format !== '') {
			$this->_varFmt = $format;
		}
	}

	public function __toString() {
		return $this->printAs($this->_varType);
	}

	function printAs($type) {

		//in case of a failed type match
		$output = $this->_varVal;

		switch($type) {
			case 'phone':
				$clean = $this->cleanVar($this->_varVal);
				$clean = $this->cleanSpaces($clean);
				$output = Cgn_ActiveFormatter_Printer::printAsPhone(
					$clean,
					$this->_varFmt
				);
			break;
			case 'email':
				$clean = $this->cleanEmail($this->_varVal);
				$output = Cgn_ActiveFormatter_Printer::printAsEmail(
					$clean,
					$this->_varFmt
				);

			break;
		}

		if ($this->_htmlEscape) {
			return htmlentities($output);
		}
		return $output;
	}

	function showAs($val, $type) {
		$af = new Cgn_ActiveFormatter($val, $type);
				//echo $af->_varType; echo 'l skdjf';
		return $af->printAs($af->_varType);
	}

	function showComposed($val, $type) {
		$output = '';
		switch ($type) {
		case 'CONTENT_ENTRY':
		case 'CONTENT_ENTRY_LIST':
			$output .= Cgn_ActiveFormatter_Title::getOutput($val);
			$output .= Cgn_ActiveFormatter_Content::getOutput($val);
			$output .= Cgn_ActiveFormatter_Content_Links::getOutput($val);
			break;
		}
		return $output;
	}

	function show($val, $hint='') {
		//guess that the value's type
		if ($hint != '') {
			$type = Cgn_ActiveFormatter::getTypeFromHint($hint);
			$composed = Cgn_ActiveFormatter::isTypeComposed($type);
			$list     = Cgn_ActiveFormatter::isTypeList($type);
			if (!$composed) {
				return Cgn_ActiveFormatter::showAs($val,$type);
			} else {
				if ($list) {
					$output = '';
					foreach ($val as $_listItem) {
						$output .= Cgn_ActiveFormatter::showComposed($_listItem,$type);
					}
					return $output;
				} else {
					return Cgn_ActiveFormatter::showComposed($val,$type);
				}
			}
		} else {
			echo "hint is blank";
		}

	}

	function getTypeFromHint($hint) {
		switch ($hint) {
			case 'phone': return 'PHONE';
			case 'title': return 'TITLE';
			case 'entry': return 'CONTENT_ENTRY';
			case 'content': return 'CONTENT_ENTRY';
			case 'articles': return 'CONTENT_ENTRY_LIST';
			case 'blog_entry': return 'CONTENT_ENTRY_BLOG';
		}
		return 'UNKNOWN ('.$hint.')';
	}


	/**
	 * A type is 'composed' if it is complex and contains other parts that need formatting separately.
	 *
	 * ex. A blog entry is composed of a title, some content, and some 'read more' links
	 *     A phone number is not composed of anything.
	 * @return bool type is composed if true
	 */
	function isTypeComposed($type) {
		switch ($type) {
			case 'PHONE': return false;
			case 'TITLE': return false;
			case 'CONTENT_ENTRY': return true;
			case 'CONTENT_ENTRY_LIST': return true;
			case 'CONTENT_ENTRY_BLOG': return true;
		}
		return false;
	}

	/**
	 * A type is a 'list' if it is an array
	 *
	 * @return bool type is a list if true
	 */
	function isTypeList($type) {
		switch ($type) {
			case 'CONTENT_ENTRY_LIST': return true;
		}
		return false;
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
//
//

class Cgn_ActiveFormatter_Title {
	function getOutput($val){
		return "<h2>".$val->title."</h2>\n";
	}
}

class Cgn_ActiveFormatter_Content {
	function getOutput($val){
		return "<p>".$val->content."</p>\n";
	}
}

class Cgn_ActiveFormatter_Content_Links {
	function getOutput($val) {
		return '<div class="links"><a href="'. cgn_appurl('main','content','').$val->link_text .'">Read More...</a> </div>';
	}
}
?>
