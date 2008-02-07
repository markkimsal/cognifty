<?php


class Cgn_ActiveFormatter {

	var $_varType;
	var $_varVal;

	function Cgn_ActiveFormatter($val='',$type='') {
		$this->_varVal  = $val;
		$this->_varType = $type;
	}


	function printAs($type) {

		switch($type) {
			case 'phone':
				$p = str_replace('-','',$this->_varVal);
				$p = str_replace('(','',$p);
				$p = str_replace(')','',$p);
				$p = str_replace(' ','',$p);
				$prefix = substr($p,0,3);
				$first = substr($p,3,3);
				$second = substr($p,6,4);
				return '('.$prefix.') '.$first.'-'.$second;
			break;
		}
	}
}

//global $global_formatter;
//$global_formatter = new Cgn_ActiveFormatter();
//$global_formatter->init($t);
?>
