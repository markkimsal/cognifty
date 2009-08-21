<?php

class Cgn_Service_Install_Main extends Cgn_Service {

	function Cgn_Service_Install_Main () {

	}


	/**
	 * Attempt to load up a published article and show it
	 */
	function mainEvent(&$req, &$t) {
		if (!file_exists(BASE_DIR.'var/search_cache')) {
			@mkdir (BASE_DIR.'var/search_cache');
		}
		//check for config writability
		$t['core'] = is_writable(CGN_BOOT_DIR.'local');
		$t['var'] = is_writable(BASE_DIR.'var');
		$t['search'] = is_writable(BASE_DIR.'var/search_cache');

		$t['complete'] = file_exists(CGN_BOOT_DIR.'local/core.ini');
	}

	function askDsnEvent(&$req, &$t) {
		if ($this->_installComplete() ) {
			header('HTTP/1.1 403 Forbidden');
			exit();
		}
	}


	function writeConfEvent(&$req, &$t) {
		if ($this->_installComplete() ) {
			header('HTTP/1.1 403 Forbidden');
			echo "permission denided.";
			exit();
		}

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
		if ($this->_installComplete() ) {
			header('HTTP/1.1 403 Forbidden');
			exit();
		}

		$db = Cgn_Db_Connector::getHandle();
		if (!$db->isSelected) {
			//try to create the DB
			$db->exec('CREATE DATABASE `'.$db->database.'`');
			var_dump('CREATE DATABASE `'.$db->database.'`');
			if (mysql_select_db($db->database, $db->driverId) ) {
				// __TODO__ perhaps we should throw an error and eat it up somewhere else?
				$this->isSelected = true;
			}
		}

		$thisdir = dirname(__FILE__);
		$d = dir($thisdir.'/sql/mysql');
		$totalFiles = 0;
		$listFiles = array();
		while (false !== ($entry = $d->read())) {
			//avoid old files
			if (substr($entry, -1) === '~') { continue; }
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
						$e = Cgn_ErrorStack::pullError('php');
						continue;
					}
					if (strstr($db->errorMessage, 'already exists')) {
						$e = Cgn_ErrorStack::pullError('php');
						continue;
					}
					if (strstr($db->errorMessage, 'Duplicate')) {
						$e = Cgn_ErrorStack::pullError('php');
						continue;
					}

					if (!$db->isSelected) {
						trigger_error("Cannot use the chosen database.  Please make sure the database is created.");
						return true;
					}
					echo "query failed. ($x)\n";
					echo $db->errorMessage."\n";
					print_r($q);
					exit();
					return false;
				}
			}
		}

		//redirect here to avoid refreshes
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('install', 'main', 'askTemplate');
	}


	/**
	 * Load the template with necessary info to display the
	 * template config screen.
	 */
	function askTemplateEvent($req, &$t) {
		$t['site_name'] = Cgn_Template::siteName();
		$t['site_tag'] = Cgn_Template::siteTagLine();
	}

	function writeTemplateEvent(&$req, &$t) {
		if ($this->_installComplete() ) {
			header('HTTP/1.1 403 Forbidden');
			echo "permission denided.";
			exit();
		}

		$name   = $req->cleanString('site_name');
		$tag    = $req->cleanString('site_tag');
		$ssl    = $req->cleanString('ssl_port');


		$em1   = $req->cleanString('email_contact_us');
		$em2   = $req->cleanString('email_default_from');
		$em3   = $req->cleanString('email_error_notify');

		if ($name == '') {
			die('lost the site name variable, can\'t write conf file.');
		}

		$replaces = array(
			'site.name' => $name,
			'site.tagline'=>$tag,
			'ssl.port' => $ssl);

		//just open the file and pass through everything except the line that starts with "default.uri"
		$this->_rewriteIni('template.ini', $replaces);


		$replaces = array(
			'email.contactus'   => $em1,
			'email.errornotify' => $em3,
			'email.defaultfrom' => $em2);

		//just open the file and pass through everything except the line that starts with "default.uri"
		$this->_rewriteIni('default.ini', $replaces, true);


		//redirect here to avoid refreshes
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('install', 'main', 'askAdmin');
	}

	function askAdminEvent($req, &$t) {
		//suggest a random password
		$t['pass'] = base_convert( rand(9000000,10000000), 10, 24);
	}

	function setupAdminEvent(&$req, &$t) {
		if ($this->_installComplete() ) {
			header('HTTP/1.1 403 Forbidden');
			exit();
		}

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
			Cgn_ErrorStack::throwError("Could not make admin user, installation *not* complete.");
			return false;
		}
		if (!$db->query($group)) {
			Cgn_ErrorStack::throwError("Could not make admin user, installation *not* complete.");
			return false;
		}
		if (!$db->query($link)) {
			Cgn_ErrorStack::throwError("Could not make admin user, installation *not* complete.");
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


	private function _installComplete() {
		$group = new Cgn_DataItem('cgn_group');
		$group->hasOne('cgn_user_group_link', 'cgn_group_id', 'Tgrp');
		$group->andWhere('Tgrp.cgn_user_id',  NULL, 'IS NOT');
		$group->andWhere('cgn_group.code', 'admin' );
		$group->load();
		$e = Cgn_ErrorStack::pullError('php');
		return ($group->cgn_group_id > 0);
	}

	public function _rewriteIni($iniName, $replaces, $useLocal = FALSE) {
		if ($useLocal) 
			$ini = file_get_contents(CGN_BOOT_DIR.'local/'.$iniName);
		else
			$ini = file_get_contents(CGN_BOOT_DIR.$iniName);

		$lines = explode("\n",$ini);
		unset($ini);
		$newIni = '';
		foreach ($lines as $line) {
			$found = FALSE;
			foreach ($replaces as $_replace => $_rwith) {
				$size = strlen($_replace);
				if (substr($line,0,$size) == $_replace) {
					$newIni .= $_replace.'='.$_rwith."\n";
					$found = true;
				}
			}
			if (!$found) {
				$newIni .= $line."\n";
			}
		}

		$newIni = trim($newIni);
		$f = fopen(CGN_BOOT_DIR.'local/'.$iniName,'w');
		fputs($f,$newIni,strlen($newIni));
		fclose($f);
		unset($newIni);
	}
}

?>
