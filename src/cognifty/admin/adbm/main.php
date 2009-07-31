<?php

Cgn::loadLibrary('Html_Widgets::lib_cgn_widget'); //needed for widgets
Cgn::loadLibrary('Html_Widgets::lib_cgn_toolbar'); //tool bar is a subclass of widget
Cgn::loadLibrary('lib_cgn_mvc');    //all tables, lists, etc
Cgn::loadLibrary('lib_cgn_mvc_table'); //table specific MVC pattern


class Cgn_Service_Adbm_Main extends Cgn_Service_AdminCrud {

	public $pageTitle = 'Manage Database Connections';
	public $tableHeaderList    = array('DSN Name', 'Host', 'Schema (DB)', 'Upload SQL File');

	public function mainEvent($req, &$t) {
		parent::mainEvent($req, $t);
	}


	/**
	 * Return a list of DSNs from the object store
	 */
	protected function _loadListData() {
		$objStore = Cgn_ObjectStore::$singleton;
		$dsnList = $objStore->objStore['dsn'];
		$data = array();
		foreach ($dsnList as $_n => $_d) {
			list($name, $uri) = explode('.', $_n);
			$dsnParts = @parse_url($_d);
			if (!$dsnParts) continue;
			$data[] = array('name'=>$name, 'host'=>$dsnParts['host'], 'database'=> ltrim($dsnParts['path'], '/'));
		}
		return $data;
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

	public function executeUploadEvent($req, &$t) {
		ini_set('max_execution_time', 0);
		$handle = $req->cleanString('dsn_idx');
		$db = Cgn_Db_Connector::getHandle($handle);

		if (isset($_FILES['sql_file'])
			&& $_FILES['sql_file']['error'] == UPLOAD_ERR_OK) {
			$fullQueries = file_get_contents($_FILES['sql_file']['tmp_name']);
		} else {
			trigger_error('file not uploaded properly ('.$_FILES['sql_file']['error'].')');
			return false;
		}

		$queryList = $this->_splitMlQuery($fullQueries);
		$qcount = 0;
		$badList = array();;
		foreach ($queryList as $_q) {
			$_x = $db->exec($_q);
			if ($_x) {
				$qcount++;
			} else {
				$badList[] = $_q .' ['.$db->getLastError().']';
			}
		}

		$u = $req->getUser();
		$u->addSessionMessage('Successfully Executed ('.sprintf('%d', $qcount).') Queries!');
		foreach ($badList as $_bad) {
			$u->addSessionMessage('Query Failed ('.sprintf('%s', $_bad).')', 'msg_warn');
		}
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
		$form = $this->_makeUploadForm($_d);
		return array($_d['name'], $_d['host'], $_d['database'], $form->toHtml());
	}

	/**
	 * Make a file upload form
	 */
	public function _makeUploadForm($dsn) {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_Widgets::lib_cgn_widget');
		$f = new Cgn_FormAdmin('content_'.$dsn['name'], '', 'POST', 'multipart/form-data');
		$f->width="100%";
		$f->action = cgn_adminurl($this->moduleName, $this->serviceName, 'executeUpload');
		$f->labelSubmit = 'Execute';

		$fileElement = new Cgn_Form_ElementFile('sql_file', 'Plain Text SQL File');
		$f->appendElement($fileElement);

		$idElement = new Cgn_Form_ElementHidden('dsn_idx');
		$f->appendElement($idElement, $dsn['name']);

		$f->setShowCancel(FALSE);
		return $f;
	}


	/**
	 * Split a multi-line query into multiple single queries.
	 *
	 * @return Array
	 */
	public function _splitMlQuery($mlQuery) {

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
