<?php


/**
 * If a specific M/S/E is not found in the URL, nor in the Vanity URL
 * configurations, try to handle it as a site-area URL
 */
class Cgn_Service_Main_Filenotfound extends Cgn_Service {

	function __construct () {
	}

	/**
	 * Show a 404 error page.  Allow for sub-classing or overridding
	 *
	 * Send the header as "404 not found.".  Add an h2 tag to the template 
	 * array under the "message" key.  Handling 404s as a basic service 
	 * allows the end-developer to more easily control the behavior without 
	 * having to override library files.  See the "default.ini" file to change 
	 * which M/S/E is used for 404s.
	 *
	 * This service is slip-streamed into the ticket list if any file or
	 * directory of the current request cannot be found.
	 *
	 * @see default.ini
	 * @see Cgn_SystemRunner
	 */
	function mainEvent(&$req, &$t) {
		//try to find content in the system via the SITE AREAS or SITE STRUCTURE
		if($this->findSiteStructure($req,$t)) {
			return true;
		}
		Cgn_ErrorStack::pullError('php');
		header('HTTP/1.0 404 Not Found');
		$t['message'] = '<h2>File Not Found.</h2>';
		$t['message2'] = '<p>Sorry, the requested URL could not be found.</p>';
	}

	function findSiteStructure($req, &$t) {
		$parts = explode('/', $req->requestedUrl);
		//start at the end, count back until we hit a word or 
		//  run out of parts
		do {
			$end = array_pop($parts);
		} while ($end == '' && count($parts) > 0);
		$structure = new Cgn_DataItem('cgn_site_struct');
		$structure->andWhere('title', $end);
		if (!$structure->load()) {
			return false;
		}
		if ($structure->node_id > 0 ) {
			$web = new Cgn_DataItem('cgn_web_publish');
			$web->andWhere('cgn_content_id', $structure->node_id);
			$web->load();
			array_unshift($req->getvars, $web->link_text);
		}

		$newTicket = new Cgn_SystemTicket('main', 'page');
		$myHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		array_push($myHandler->ticketList, $newTicket);

		return true;
	}
}

?>
