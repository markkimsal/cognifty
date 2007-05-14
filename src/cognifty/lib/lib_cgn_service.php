<?php

class Cgn_Service {

	var $presenter = 'default';

	function processEvent($e,&$req,&$t) {
		$eventName = $e.'Event';
		$this->$eventName($req,$t);
	}

}
?>
