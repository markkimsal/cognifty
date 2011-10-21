<?php
Cgn::loadLibrary('Mod::Lib_Cgn_Mod_Mgr');

class Cgn_Service_Main_Main extends Cgn_Service_Admin {

	function Cgn_Service_Main_Main () {

	}


	function mainEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle('default');
		/*
		$db->query('SELECT count(*) as total_rec
			FROM cgn_sess
			WHERE DATE_SUB( FROM_UNIXTIME(saved_on), INTERVAL 1 DAY) <= 1
			');
		 */
		/*
		$db->query('
			select count(*) as total_rec from cgn_sess  WHERE saved_on > UNIX_TIMESTAMP() - (60*60*24)'
		);
		$db->nextRecord();
		$t['todaySessions'] = $db->record['total_rec'];

		$db->query('
			select count(*) as total_rec from cgn_sess  WHERE saved_on > UNIX_TIMESTAMP() - (60*5)'
		);
		$db->nextRecord();
		$t['recentSessions'] = $db->record['total_rec'];
		 */


		//log table
		//

		if(! Cgn_ObjectStore::hasConfig("object://default/handler/log") ) {
			$t['lastActivity'] = array();
			$t['lastActivityWarn'] = 'No Logging Handler Configured (boot/default.ini)';
		} else {
			$db->query('
				select ip_addr,url,recorded_on from cgn_log_visitor ORDER BY recorded_on DESC LIMIT 10'
			);
			$t['lastActivity'] = $db->fetchAll();
		}


		$db->query('
			select cgn_content_id, title, sub_type from cgn_content ORDER BY created_on DESC LIMIT 10'
		);
		$t['lastContent'] = $db->fetchAll();



		/*
		$db->query('
			select count(*) as total_rec from cgn_content'
		);
		$db->nextRecord();
		$t['allContent'] = $db->record['total_rec'];

		$db->query('
			select count(*) as total_rec from cgn_content where type = "text"'
		);
		$db->nextRecord();
		$t['textContent'] = $db->record['total_rec'];

		$db->query('
			select count(*) as total_rec from cgn_content where type = "file"'
		);
		$db->nextRecord();
		$t['fileContent'] = $db->record['total_rec'];
		 */

		/*
		//$db = Cgn_Db_Connector::getHandle('default');
		$tables = $db->getTables();
		foreach($tables as $table) { 
			$info[$table] = $db->getTableColumns($table);
		}
		$t['info'] = ($info);
		// */
		$this->_loadModulePortals($t);
	}

	public function _loadModulePortals(&$t) {
		$myHandler =& Cgn_ObjectStore::getObject("object://adminSystemHandler");
		//array_push($myHandler->ticketList, $tick);

		$modList = Cgn_Module_Manager::getInstalledModules();
		foreach ($modList as $modInfo) {
			if (!$modInfo->isAdmin) { continue; }
			if ($modInfo->isInstalled) { 
				if (isset($modInfo->config['dashboard_portal_mse'])) {
					$t['modDashboardList'][] = $modInfo;
					$mse = explode('.', $modInfo->config['dashboard_portal_mse']);
					$newTicket = new Cgn_SystemTicket($mse[0], @$mse[1], @$mse[2]);
//					Cgn_SystemRunner_Admin::stackTicket($newTicket);
					$myHandler->runCogniftyTicket($newTicket);
				}
			}
		}
	}
}
