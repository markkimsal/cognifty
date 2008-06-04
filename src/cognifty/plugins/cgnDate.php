<?php

/**
 *    date plugin - sample
 *
 * all plugin methods will be called as
 * <div plugin='date/show' param1='foo'>sample data</div>
 * 
 * 'date' is registered in config.ini under [plugins] and
 * points to the specific class file to load and instantiate
 *
 * 'show' is the specific method to run
 *
 * all key/value attributes are passed in one associative
 * array to the method.
 *
 * plugins should return HTML code for display within the 
 * calling <div> tag structure, replacing any 'sample data'
 */

class Cgn_Plugin_CgnDate {

/**
 * show the date
 * 
 * use the 'format' aspect of the passed in params
 * for formatting
 *
 * @param array Use the 'format' from the associative array
 * @return string HTML
 */
	function show($params) { 
		return date($params['format']);	
	}
}
