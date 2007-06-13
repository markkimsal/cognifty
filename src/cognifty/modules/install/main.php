<?php


class Cgn_Service_Install_Main extends Cgn_Service {

	function Cgn_Service_Install_Main () {

	}


	/**
	 * Attempt to load up a published article and show it
	 */
	function mainEvent(&$req, &$t) {
		//check for config writability
		$t['core'] = is_writable('../boot/core.ini');
		$t['default'] = is_writable('../boot/default.ini');
	}

	function writeConfEvent(&$req, &$t) {
		$host   = $req->cleanString('db_host');
		$user   = $req->cleanString('db_user');
		$pass   = $req->cleanString('db_pass');
		$schema = $req->cleanString('db_schema');
		$dsn = "mysql://".$user.":".$pass."@".$host."/".$schema;

		//just open the file and pass through everything except the line that starts with "default.uri"
		$ini = file_get_contents('../boot/core.ini');
		$lines = explode("\n",$ini);
		unset($ini);
		$size = strlen('default.uri');
		$newIni = '';
		foreach ($lines as $line) {
			if (substr($line,0,$size) == 'default.uri') {
				$newIni .= 'default.uri='.$dsn."\n";
			} else {
				$newIni .= $line."\n";
			}
		}
		$newIni = trim($newIni);
		$f = fopen('../boot/core.ini','w');
		fputs($f,$newIni,strlen($newIni));
		fclose($f);
		unset($newIni);

		//open defaults and switch out this installer as the main module.
		$ini = file_get_contents('../boot/default.ini');
		$lines = explode("\n",$ini);
		unset($ini);
		$size = strlen('module');
		$newIni = '';
		foreach ($lines as $line) {
			if (substr($line,0,$size) == 'module') {
				$newIni .= 'module=main'."\n";
			} else {
				$newIni .= $line."\n";
			}
		}
		$newIni = trim($newIni);
		$f = fopen('../boot/default.ini','w');
		fputs($f,$newIni,strlen($newIni));
		fclose($f);
		unset($newIni);
	}


	function insertDataEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle();

		$thisdir = dirname(__FILE__);
		for ($x=1; $x <= 6; $x++) {
			$installTableSchemas = array();
			@include($thisdir.'/sql/schema_'.sprintf('%02d',$x).'.php');
			if (count($installTableSchemas)<1 ) {
				next;
			}
			foreach ($installTableSchemas as $schema) {
				if (trim($schema) == '') { continue;}
				if (!$db->query($schema)) {
					echo "query failed. ($x)\n";
					echo $gdb->errorMessage."\n";
					print_r($schema);
					exit();
					return false;
				}
			}
		}
	}
}

?>
