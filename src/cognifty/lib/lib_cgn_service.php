<?php

class Cgn_Service {


	function processEvent($e,&$sys,&$t) {
		$eventName = $e.'Event';
		$this->$eventName($sys,$t);
	}

}
?>
