<?php


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

		$db->query('
			select ip_addr,url from cgn_log_visitor ORDER BY recorded_on DESC LIMIT 5'
		);
		$t['lastActivity'] = $db->fetchAll();

		$db->query('
			select title, sub_type from cgn_content ORDER BY created_on DESC LIMIT 5'
		);
		$t['lastContent'] = $db->fetchAll();



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

		/*
		//$db = Cgn_Db_Connector::getHandle('default');
		$tables = $db->getTables();
		foreach($tables as $table) { 
			$info[$table] = $db->getTableColumns($table);
		}
		$t['info'] = ($info);
		// */
	}
}

?>
