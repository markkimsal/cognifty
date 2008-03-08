<?php

class Cgn_Signal_Sig {

	var $_sourceObj;
	var $_sigName;
	var $_sourceClass;

	function Cgn_Signal_Sig($name, &$obj) {
		$this->_sigName = $name;
		$this->_sourceObj = $obj;
		$this->_sourceClass = get_class($obj);
	}

	function endLife() {
		unset($this->_sourceObj);
		unset($this->_sigName);
		unset($this->_sourceClass);
	}

	function getName() {
		return $this->_sigName;
	}


	function getClass() {
		return $this->_sourceClass;
	}

	function &getSource() {
		return $this->_sourceObj;
	}
}
