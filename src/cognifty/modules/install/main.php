<?php

class Cgn_Service_Install_Main extends Cgn_Service {

	function Cgn_Service_Install_Main () {

	}


	/**
	 * Attempt to load up a published article and show it
	 */
	function mainEvent(&$req, &$t) {
		//check for config writability
		$t['core'] = is_writable(CGN_BOOT_DIR.'local');
		$t['var'] = is_writable(BASE_DIR.'var');

		$t['complete'] = file_exists(CGN_BOOT_DIR.'local/core.ini');
	}

	function writeConfEvent(&$req, &$t) {
		$host   = $req->cleanString('db_host');
		$user   = $req->cleanString('db_user');
		$pass   = $req->cleanString('db_pass');
		$schema = $req->cleanString('db_schema');
		if ($user == '') {
			die('lost the user variable, can\'t write conf file.');
		}
		$dsn = "mysql://".$user.":".$pass."@".$host."/".$schema;

		//just open the file and pass through everything except the line that starts with "default.uri"
		$ini = file_get_contents(CGN_BOOT_DIR.'core.ini');
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
		$f = fopen(CGN_BOOT_DIR.'local/core.ini','w');
		fputs($f,$newIni,strlen($newIni));
		fclose($f);
		unset($newIni);

		//open defaults and switch out this installer as the main module.
		$ini = file_get_contents(CGN_BOOT_DIR.'default.ini');
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
		$f = fopen(CGN_BOOT_DIR.'local/default.ini','w');
		fputs($f,$newIni,strlen($newIni));
		fclose($f);
		unset($newIni);

		//clear the cache
		if (file_exists(CGN_BOOT_DIR.'bootstrap.cache')) {
			unlink(CGN_BOOT_DIR.'bootstrap.cache');
		}
	}


	function insertDataEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle();

		$thisdir = dirname(__FILE__);
		$d = dir($thisdir.'/sql/');
		$totalFiles = 0;
		while (false !== ($entry = $d->read())) {
			if (strstr($entry, 'schema_') !== FALSE) {
				$totalFiles++;
			}
		}
		for ($x=1; $x <= $totalFiles; $x++) {
			$installTableSchemas = array();
			@include($thisdir.'/sql/schema_'.sprintf('%02d',$x).'.php');
			if (count($installTableSchemas)<1 ) {
				next;
			}
			foreach ($installTableSchemas as $schema) {
				if (trim($schema) == '') { continue;}
				if (!$db->query($schema)) {
					if (strstr($db->errorMessage, 'IF EXISTS')) {
						continue;
					}
					echo "query failed. ($x)\n";
					echo $db->errorMessage."\n";
					print_r($schema);
					exit();
					return false;
				}
			}
		}

		//suggest a random password
		$t['pass'] = base_convert( rand(9000000,10000000), 10, 24);
	}


	function setupAdminEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle();

		$user = "INSERT INTO cgn_user
			(username, password, active_on)
			VALUES ('".$req->cleanString('adm_user')."',
				'".Cgn_User::_hashPassword($req->cleanString('adm_pass'))."',
				'".time()."'
			)";
		$group = "INSERT INTO cgn_group
			(code, display_name, active_on)
			VALUES ('admin',
				'Site Admins',
				'".time()."'
			)";
		$link = "INSERT INTO cgn_user_group_link
			(cgn_group_id, cgn_user_id, active_on)
			VALUES (1,
				1,
				'".time()."'
			)";
		if (!$db->query($user)) {
			echo "query failed. ($x)\n";
			return false;
		}
		if (!$db->query($group)) {
			echo "query failed. ($x)\n";
			return false;
		}
		if (!$db->query($link)) {
			echo "query failed. ($x)\n";
			return false;
		}
	}
}

?>
