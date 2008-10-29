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

	function askDsnEvent(&$req, &$t) {
	}


	function writeConfEvent(&$req, &$t) {
		$host   = $req->cleanString('db_host');
		$user   = $req->cleanString('db_user');
		$pass   = $req->cleanString('db_pass');
		$schema = $req->cleanString('db_schema');
		$driver = $req->cleanString('db_driver');

		$host2   = $req->cleanString('db2_host');
		$user2   = $req->cleanString('db2_user');
		$pass2   = $req->cleanString('db2_pass');
		$schema2 = $req->cleanString('db2_schema');
		$driver2 = $req->cleanString('db2_driver');
		$uri2    = $req->cleanString('db2_uri');

		if ($user == '') {
			die('lost the user variable, can\'t write conf file.');
		}
		if ($driver == '') {
			$driver = 'mysql';
		}

		$dsn = $driver."://".$user.":".$pass."@".$host."/".$schema;

		//handle extra driver
		if (!empty($uri2)) {
			$uri2line = $uri2.'='.$driver2."://".$user2.":".$pass2."@".$host2."/".$schema2;
		}

		//just open the file and pass through everything except the line that starts with "default.uri"
		$ini = file_get_contents(CGN_BOOT_DIR.'core.ini');
		$lines = explode("\n",$ini);
		unset($ini);
		$size = strlen('default.uri');
		$newIni = '';
		foreach ($lines as $line) {
			if (substr($line,0,$size) == 'default.uri') {
				$newIni .= 'default.uri='.$dsn."\n";
				if (isset($uri2line)) {
					$newIni .= $uri2line."\n";
				}
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
		$d = dir($thisdir.'/sql/mysql');
		$totalFiles = 0;
		$listFiles = array();
		while (false !== ($entry = $d->read())) {
			if (strstr($entry, '.mysql.sql') !== FALSE) {
				$totalFiles++;
				$listFiles[] = $entry;
			}
		}
		$d->close();

//		for ($x=1; $x <= $totalFiles; $x++) {
		foreach ($listFiles as $file) {

			$schema = file_get_contents($thisdir.'/sql/mysql/'.$file);
			$queries = $this->splitMlQuery($schema);

			foreach ($queries as $q) {
				if (!$db->query($q)) {
					if (strstr($db->errorMessage, 'IF EXISTS')) {
						continue;
					}
					if (strstr($db->errorMessage, 'already exists')) {
						continue;
					}

					if (!$db->isSelected) {
						echo "Cannot use the chosen database.  Please make sure the database is created.";
						return false;
						exit();
					}
					echo "query failed. ($x)\n";
					echo $db->errorMessage."\n";
					print_r($q);
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

	/**
	 * Split a multi-line query into multiple single queries.
	 *
	 * @return Array
	 */
	public function splitMlQuery($mlQuery) {

		$queries = array();
		$cleanSchemas = array();
		$mlQuery = str_replace("; \n", ";\n", $mlQuery);
		$queries[] = explode(";\n", $mlQuery);

		foreach ($queries as $_idx => $manyDefs) {
			foreach ($manyDefs as $fullDef) {
				$lines = explode("\n",$fullDef);
				$cleaner = '';
				foreach ($lines as $line) {

					if (trim($line) == '') {continue;}
					if (trim($line) == '--') {continue;}
					if (trim($line) == '#') {continue;}
					if (trim($line) == '# ') {continue;}
					if (preg_match("/^#/",trim($line))) {continue;}
					if (preg_match("/^--/",trim($line))) {continue;}

					$cleaner .= $line."\n";
				}
				if (trim($cleaner) == '') { continue; }
				$cleanSchemas[] = trim($cleaner)."\n";
			}
		}

		return $cleanSchemas;
	}
}

?>
