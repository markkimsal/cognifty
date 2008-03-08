<?php
class Cgn_Signal_Test {
	function echoSignal($sig) {
		echo "Signal called: ".$sig->getName()."<br/>";
		echo "Called from class: ".$sig->getClass()."<br/>";
	}
}
