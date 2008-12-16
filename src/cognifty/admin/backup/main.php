<?php

Cgn::loadLibrary('Html_Widgets::lib_cgn_widget'); //needed for widgets
Cgn::loadLibrary('Html_Widgets::lib_cgn_toolbar'); //tool bar is a subclass of widget
Cgn::loadLibrary('lib_cgn_mvc');    //all tables, lists, etc
Cgn::loadLibrary('lib_cgn_mvc_table'); //table specific MVC pattern


class Cgn_Service_Backup_Main extends Cgn_Service_AdminCrud {

	public $pageTitle = 'System Back-ups';

	public function mainEvent($req, &$t) {
		parent::mainEvent($req, $t);
		$t['backupForm'] = $this->_loadBackupForm();
	}


	public function erEvent($req, &$t) {
		$f = $req->cleanString('f');
		$filename = BASE_DIR.'var/backups/'.$f;
		$res = unlink($filename);
		$this->redirectHome($t);
	}


	public function dlEvent($req, &$t) {
		$t['f'] = $req->cleanString('f');
		$t['filename'] = BASE_DIR.'var/backups/'.$req->cleanString('f');
		$this->presenter = 'self';
	}

	public function doBackupEvent($req, &$t) {
		ini_set('max_execution_time', 0);
		$db = Cgn_Db_Connector::getHandle();
		$this->_writeOutDbConnection($db);

		$u = $req->getUser();
		$u->addSessionMessage('Back-up Saved!');
		$this->redirectHome($t);
	}

	//no toolbar for site backups
	protected function _makeToolBar() {
		return '';
	}

	/**
	 * return non-associative array
	 */
	protected function _makeTableRow($_d) {
		$dl = cgn_adminlink('Download', 'backup', 'main', 'dl', array('f'=>$_d['display_name']));
		$er = cgn_adminlink('Erase',    'backup', 'main', 'er', array('f'=>$_d['display_name']));
		return array($_d['display_name'], $dl, $er);
	}

	protected function _loadListData() {
		$ret = array();
		$this->_tryToMakeDir();
		$d = dir(BASE_DIR.'var/backups');
		if (!$d) {
			return array();
		}
		while ($entry = $d->read()) {
			if ($entry[0] == '.') continue;
			$ret[] = array('display_name'=>$entry);
		}
		$d->close();
		return $ret;
	}

	public function _tryToMakeDir() {
		if (!file_exists(BASE_DIR.'var/backups')) {
			 mkdir(BASE_DIR.'var/backups');
		}
	}

	public function _loadBackupForm() {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_Widgets::lib_cgn_widget');

		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl($this->moduleName, $this->serviceName, 'doBackup');
		$f->label = 'Create New Backup';
		$checkElement = new Cgn_Form_ElementCheck('include_content', 'Include Content?');
		$checkElement->addChoice('yes', 'yes', true);

		$f->appendElement($checkElement);
		$f->setShowCancel(FALSE);
		return $f;
	}

	/**
	 * Return false if the file could not be opened
	 */
	public function _writeOutDbConnection($db) {
		$justthename = 'cgn_backup_'.date('Ymd_Gis').'.sql';
		$filename = BASE_DIR.'var/backups/'.$justthename;
		$f = fopen($filename, 'w');
		if (!$f) {
			return false;
		}
//echo "<pre>";
		$hdr  = "/* Dumped Database: ".date('Y-m-d G:i:s')." */\n";
		$hdr .= "/* Schema Name: ".$db->database." */\n\n";
		fwrite($f, $hdr, strlen($hdr));
//echo $hdr;

		$tables = $db->getTables();
		foreach ($tables as $_tname => $_t) {
			$this->_writeOutTable($_t, $db, $f);
		}

		fwrite($f, "\n\n", strlen("\n\n"));
		fclose($f);

		$output = '';
		$ret = 0;
		if (ini_get('safe_mode') == 1) {
			$f = fopen($filename, 'a+');
			foreach ($tables as $_tname => $_t) {
				$this->_writeOutTableData($_t, $db, $f);
			}
			fclose($f);
			$this->_compressFile($filename, $justthename);
		} else {
			exec("mysqldump -t -u {$db->user} -p{$db->password} -h {$db->host} {$db->database} >> $filename", $output, $ret);
			exec("gzip $filename");
		}
	}


	public function _writeOutTable($_t, $db, $f) {
		$colDef = '`%s` %s %s %s';

		$line = "\n/* Table Structure for: ".$_t." */\n";
		$line .= "DROP TABLE IF EXISTS `".$_t."`;\n";
		$line .= "Create TABLE `".$_t."` (\n";

		fwrite($f, $line, strlen($line));
//echo $line;

		$_tstruct = $db->getTableColumns($_t);
		$_idx     = $db->getTableIndexes($_t);

		$names = $_tstruct['name'];
		$types = $_tstruct['type'];
		$lens  = $_tstruct['len'];
		$flags = $_tstruct['flags'];
		$def   = $_tstruct['def'];

		$colLines = array();
		foreach ($names as $_key => $_val) {
			if ($lens[$_key] > 0) {
				$thisLen = '('.$lens[$_key].')';
			} else {
				$thisLen = '';
			}
			$thisFlags = $this->_getFlags($flags[$_key]);
			$thisFlags .= $this->_getDefault($def[$_key]);
			$colLines[] = sprintf($colDef, $_val, $types[$_key], $thisLen, $thisFlags);
		}

		//if there's a Primary key index, add it to the table defintion
		if (isset($_idx['PRIMARY'])) {
			$pkey = $_idx['PRIMARY'][1]['column'];
			$colLines[] = "PRIMARY KEY (`".$pkey."`)";
			unset($_idx['PRIMARY']);
		}

		$line = "   ".implode(",\n   ", $colLines)."\n";
			fwrite($f, $line, strlen($line));
//echo $line;

		$line = ");\n";
		$line .= "ALTER TABLE `".$_t."` COLLATE utf8_general_ci;\n";
		fwrite($f, $line, strlen($line));
//echo $line;


		$this->_writeOutIndex($_idx, $_t, $f);
	}


	/**
	 * Write out special indexes as alter tables
	 */
	public function _writeOutIndex($idx, $_t, $f) {
		$idxDef = 'CREATE %s INDEX `%s` ON `%s` (`%s`);';
		$colLines = array();

		foreach ($idx as $_idname => $_idst) {
			$colNames = array();
			foreach ($_idst as $_idcol) {
				if ($_idcol['unique'] == '0') {
					$unq = 'UNIQUE';
				} else {
					$unq = '';
				}
				$colNames[] = $_idcol['column'];
			}
			$colNames = implode('`,`', $colNames);
			$colLines[] = sprintf($idxDef, $unq, $_idname, $_t, $colNames);
		}
		$line = implode("\n", $colLines)."\n";

		fwrite($f, $line, strlen($line));
//echo $line;
	}

	public function _writeOutTableData($_t, $db, $f) {
		$rowDef = "INSERT INTO `%s` \n  VALUES (%s);\n";

		$hdr  = "/* Data for table: $_t */\n";
		fwrite($f, $hdr, strlen($hdr));

		$db->query('SELECT * FROM '.$_t);
		while ($db->nextRecord()) {
			$item = new Cgn_DataItem($_t);
			$item->row2Obj($db->record);
			$item->_isNew = TRUE;

			$insert = $item->buildInsert('').";\n";
			fwrite($f, $insert, strlen($insert));
		}
	}

	public function _getDefault($def) {
		if ($def === NULL)
			return '';

		if (is_numeric($def) || $def === "0") {
			return 'DEFAULT '.$def;
		}
		return 'DEFAULT "'.$def.'"';
	}

	public function _getFlags($f) {
		$flg = '';

		if (strpos($f, 'unsigned') !== FALSE) {
			$flg .= 'unsigned ';
		}

		if (strpos($f, 'not_null') !== FALSE) {
			$flg .= 'NOT NULL ';
		} else {
			$flg .= 'NULL ';
		}

		if (strpos($f, 'auto_increment') !== FALSE) {
			$flg .= 'auto_increment ';
		}

		return $flg;
		$f = trim(str_replace('blob', '', $f));
		$f = trim(str_replace('binary', '', $f));
		return trim(str_replace('multiple_key', '', $f));
	}


	public function output($req, $t) {

		$f = fopen($t['filename'], 'r');
		if (!$f) {
			echo 'cannot open backup file.';
			return;
		}
		header('Content-type: application/x-octet-stream');
		header('Content-disposition: attachment;filename='.$t['f']);
		rewind($f);
		while (!feof($f)) {
			echo fread($f, 1024);
			ob_flush();
		}

		//for some reason fpassthru can cause segfaults... ?
//		fpassthru($f);
		fclose($f);
	}

	public function _compressFile($filename, $justname) {
		if (function_exists('zip_open')) {
			$zip = new ZipArchive();
			$zipname = $filename.".zip";

			if ($zip->open($zipname, ZIPARCHIVE::CREATE)!==TRUE) {
				return false;
			}
			$zip->addFile($filename ,$justname);
			$zip->close();
			unlink($filename);
			return true;
		}

		return false;
	}
}
