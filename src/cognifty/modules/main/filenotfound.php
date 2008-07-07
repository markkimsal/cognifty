<?php


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
		header('HTTP/1.0 404 Not Found');
		$t['message'] = '<h2>File Not Found.</h2>';
		$t['message2'] = '<p>Sorry, the requested URL could not be found.</p>';
	}
}

?>
