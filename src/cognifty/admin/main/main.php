<?php


class Cgn_Service_Main_Main extends Cgn_Service_Admin {

	function Cgn_Service_Main_Main () {

	}


	function mainEvent(&$req, &$t) {
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
