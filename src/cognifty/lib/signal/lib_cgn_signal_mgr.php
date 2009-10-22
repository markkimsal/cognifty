<?php

/**
 * Manage signals and slots.  Match emitted signals to slots
 */
class Cgn_Signal_Mgr {

	var $_nameMatches   = array(); //signals connected by name only
	var $_moduleMatches = array(); //signals connected by module and sig name
	var $_objMatches    = array(); //signals connected by a specific module instance and sig name
	var $_connected     = false;
		


	/**
	 * Emit a signal with the default signal handler (this class by default)
	 *
	 * @static
	 * @param string $signal the name of the signal
	 * @param object $objRefSig the firing object
	 * @return boolean true if a signal was fired, false if no handler exists
	 */
	public static function emit($signal='', &$objRefSig) {
		include_once(CGN_LIB_PATH.'/signal/lib_cgn_signal_sig.php');
		if (!Cgn_ObjectStore::hasConfig('object://signal/signal/handler')) {
			return NULL;
		}

		//get new config signals from "local/signal.ini"
		Cgn_Signal_Mgr::connectConfigSignals();

		$sigHandler = Cgn_ObjectStore::getObject("object://defaultSignalHandler");

		$sig = new Cgn_Signal_Sig($signal, $objRefSig);
		$retVal = $sigHandler->fireSignal($sig);
		$sig->endLife();
		unset($sig);

		return $retVal;
	}

	/**
	 * Make connections out of signals defined in the boot/signal.ini file
	 */
	function connectConfigSignals() {
		static $connected = FALSE;
		//ensure that connecting only happens once
		if ($connected) { return;}
		$connected = TRUE;
		if (!Cgn_ObjectStore::hasConfig('config://signal')) {
			return;
		}
		$sigs = Cgn_ObjectStore::getArray('config://signal');
		foreach ($sigs as $key=>$val) {
			list($uniqueName, $sigSlot) = explode('/', $key);
			if ($sigSlot == 'signal') {
				$sigName = str_replace('/','_',$val);
			} else {
				$slotObject = str_replace('/','_',$val);
				Cgn_ObjectStore::includeObject($val);
				$classLoaderPackage = explode(':',$slotObject);
				Cgn_Signal_Mgr::connectSig($uniqueName, $sigName, Cgn_ObjectStore::getObject('object://'.$classLoaderPackage[2]), $classLoaderPackage[3]);
				$sigName = '';
				$slotObject = '';
			}
		}
	}

	function hasSig($signal) {
		$sigHandler = Cgn_ObjectStore::getObject("object://defaultSignalHandler");
		foreach ($sigHandler->_nameMatches as $struct) {
			if ($struct['signame'] === $signal) {
				return true;
			}
		}
	}

	function connect($objRefSig, $signal, $objRefSlot, $slot) {
	}

	function connectSig($uniqueName, $signal, &$objRefSlot, $slot) {
		$sigHandler = Cgn_ObjectStore::getObject("object://defaultSignalHandler");
		$sigHandler->_nameMatches[$uniqueName] = array('signame'=>$signal, 'objref'=>$objRefSlot, 'slotname'=>$slot);
	}

	/**
	 * Search all connections until a match is found for this signal;
	 *
	 * @return mixed  returns bool result of slot's return value (return's NULL if there's a problem)
	 */
	function fireSignal(&$sig) {
		$signame = $sig->getName();
		$retVal = NULL;

		foreach ($this->_nameMatches as $connection) {
			if ($connection['signame'] === $signame) {
				$retVal = $connection['objref']->{$connection['slotname']}($sig);
//				break;
			}
		}

		foreach ($this->_moduleMatches as $connection) {
			if ($connection['signame'] == $signame 
				&& $connection['modulename'] === $sig->getClass()) {

				$retVal = $connection['objref']->{$connection['slotname']}($sig);
//				break;
			}
		}

		foreach ($this->_objMatches as $connection) {
			if ($connection['signame'] == $signame 
				&& $connection['objref'] === $sig->getSource()) {

				$retVal = $connection['objref']->{$connection['slotname']}($sig);
//				break;
			}
		}

		return $retVal;
	}

	/**
	 * Clean up object references
	 */
	function expireConnections() {
		foreach ($this->_nameMatches as $connection) {
			unset($connection['objref']);
		}
	}

	/**
	 * Clean up object references
	 */
	function __destruct() {
		$this->expireConnections();
	}
}
