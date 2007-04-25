<?php


class Cgn_Service_Main_Main extends Cgn_Service {

	function Cgn_Service_Main_Main () {

	}


	function mainEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message1','This is the main event!');
/*
		$db = Cgn_DB::getHandle('default');
		$tables = $db->getTables();
		foreach($tables as $table) { 
			$info[$table] = $db->getTableColumns($table);
		}
		print_r($info);
*/
	}
}

?>
