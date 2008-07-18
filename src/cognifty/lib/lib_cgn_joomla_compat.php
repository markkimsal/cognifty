<?php



class Cgn_JoomlaCompat_SystemRunner {

	/**
	 * list of tickets to run
	 */
	var $ticketList = array();


	function parseUrl($url) {
		/*
		sample url like this
		index.php?option=com_content&task=blogsection&id=0&Itemid=9
		*/

		//take off the first / so we can explode cleanly
		if ( isset($_SERVER['PATH_INFO']) ) {
			$bytes = explode('/', substr($_SERVER['PATH_INFO'],1));
		} else {
			$bytes = array('','','');
		}

		$x = new Cgn_JoomlaCompat_SystemTicket(@$_GET['option'],@$_GET['task']);
		//Cgn::debug($x);
		//Cgn::debug($x->vars);
		$this->ticketList[] = $x;
	}


	function runTickets() {
		define('_VALID_MOS',true);
		global $mainframe, $database, $mosConfig_secret, $mosConfig_sitename;
		$mosConfig_secret = 'FBVtggIk5lAzEU9H'; //Change this to something more secure
		$mosConfig_dbprefix='';
		$mosConfig_debug='';
		$mosConfig_user='';
		$mosConfig_password='';
		$mosConfig_sitename='Cognifty';

		include("../../joomla/language/english.php");
		include("../../joomla/includes/sef.php");

		//create database
		$mosConfig_absolute_path = '../../joomla';
		include("../../joomla/includes/database.php");
		$database = new database();

		//create mainframe
		include("../../joomla/includes/joomla.php");
		$mainframe = new mosMainFrame( $database, NULL, '.' );
		$mainframe->initSession();
		$mainframe->set( 'menu', new mosMenu($database) );

		$joomlaPath = Cgn_ObjectStore::getConfig('config://joomla/path');
		foreach ($this->ticketList as $t) {
			include($joomlaPath.'/'.$t->component.'/'.$t->filename.'.html.php');
			include($joomlaPath.'/'.$t->component.'/'.$t->filename.'.php');
		}
	}
}



class Cgn_JoomlaCompat_SystemTicket extends Cgn_SystemTicket {

	var $component;
	var $task;
	var $module;
	var $filename;

	function Cgn_JoomlaCompat_SystemTicket($c='com_front', $t='', $m='') {
		$this->component = $c;
		$this->task = $t;
		$this->module = $m;
		$this->filename = substr($c,4);
	}
}





?>
