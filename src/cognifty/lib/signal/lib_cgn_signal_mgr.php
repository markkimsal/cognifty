<?php

/**
 * Manage signals and slots.  Match emitted signals to slots
 */
class Cgn_Signal_Mgr {

	var $_nameMatches   = array(); //signals connected by name only
	var $_moduleMatches = array(); //signals connected by module and sig name
	var $_objMatches    = array(); //signals connected by a specific module instance and sig name
		


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
		if (Cgn_ObjectStore::hasConfig('object://signal/signal/handler')) {

			//get new config signals from "local/signal.ini"
			Cgn_Signal_Mgr::connectConfigSignals();

			$sigHandler = Cgn_ObjectStore::getObject("object://defaultSignalHandler");
			//$sigHandler = Cgn_ObjectStore::getObject('object://defaultSignalHandler');

			$sig = new Cgn_Signal_Sig($signal, $objRefSig);
			$sigHandler->fireSignal($sig);
			$sig->endLife();
			unset($sig);

		} else {
			return false;
		}
	}

	/**
	 * Make connections out of signals defined in the boot/signal.ini file
	 */
	function connectConfigSignals() {
		if (!Cgn_ObjectStore::hasConfig('config://signal')) {
			return;
		}
		$sigs = Cgn_ObjectStore::getArray('config://signal');
		foreach ($sigs as $key=>$val) {
			$sigName = str_replace('/','_',$key);
			if (Cgn_Signal_Mgr::hasSig($sigName)) {
				continue;
			}
			includeobject($val);
			$classLoaderPackage = explode(':',$val);
			Cgn_Signal_Mgr::connectSig($sigName, Cgn_ObjectStore::getObject('object://'.$classLoaderPackage[2]), $classLoaderPackage[3]);
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


	function connectSig($signal, &$objRefSlot, $slot) {
		$sigHandler = Cgn_ObjectStore::getObject("object://defaultSignalHandler");
		$sigHandler->_nameMatches[] = array('signame'=>$signal, 'objref'=>$objRefSlot, 'slotname'=>$slot);
	}

	/**
	 * Search all connections until a match is found for this signal;
	 */
	function fireSignal(&$sig) {
		$signame = $sig->getName();
		foreach ($this->_nameMatches as $connection) {
			if ($connection['signame'] === $signame) {
				$connection['objref']->{$connection['slotname']}($sig);
				break;
			}
		}

		foreach ($this->_moduleMatches as $connection) {
			if ($connection['signame'] == $signame 
				&& $connection['modulename'] === $sig->getClass()) {

				$connection['objref']->{$connection['slotname']}($sig);
				break;
			}
		}

		foreach ($this->_objMatches as $connection) {
			if ($connection['signame'] == $signame 
				&& $connection['objref'] === $sig->getSource()) {

				$connection['objref']->{$connection['slotname']}($sig);
				break;
			}
		}
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
