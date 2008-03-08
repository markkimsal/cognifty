<?php

if (! defined('CGN_SIG_INIT') ) {
	Cgn_Signal_Mgr::init();
	define('CGN_SIG_INIT',true);
	include_once(CGN_LIB_PATH.'/signal/lib_cgn_signal_sig.php');
}

/**
 * Manage signals and slots.  Match emitted signals to slots
 */
class Cgn_Signal_Mgr extends Cgn_Singleton {

	var $_nameMatches   = array(); //signals connected by name only
	var $_moduleMatches = array(); //signals connected by module and sig name
	var $_objMatches    = array(); //signals connected by a specific module instance and sig name
		

	/**
	 * Initialize the singleton
	 */
	function init() {
		$x = new Cgn_Signal_Mgr();
		Cgn_Signal_Mgr::getSingleton($x);
		Cgn_Signal_Mgr::connectConfigSignals();
	}

	function emit($signal='', &$objRefSig) {
		$sig = new Cgn_Signal_Sig($signal, $objRefSig);
		$this->fireSignal($sig);
		$sig->endLife();
		unset($sig);
	}

	/**
	 * Make connections out of signals defined in the boot/signal.ini file
	 */
	function connectConfigSignals() {
		$sigs = Cgn_ObjectStore::getArray('config://signal');
		foreach ($sigs as $key=>$val) {
			includeobject($val);
			$sigName = str_replace('/','_',$key);
			$classLoaderPackage = explode(':',$val);
			Cgn_Signal_Mgr::connectSig($sigName, Cgn_ObjectStore::getObject('object://'.$classLoaderPackage[2]), $classLoaderPackage[3]);
		}
	}

	function connect($objRefSig, $signal, $objRefSlot, $slot) {
	}

	function connectSig($signal, &$objRefSlot, $slot) {
		$manager = Cgn_Signal_Mgr::getSingleton();
		$manager->_nameMatches[] = array('signame'=>$signal, 'objref'=>$objRefSlot, 'slotname'=>$slot);
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
		$manager = Cgn_Signal_Mgr::getSingleton();
		foreach ($this->_nameMatches as $connection) {
			unset($connect['objref']);
		}
	}


	/**
	 * Clean up object references
	 */
	function __destruct() {
		$this->expireConnections();
	}

}
