<?php

/**
 * various methods to change text
 *
 */

class Cgn_Plugin_TextChange {

/**
 * background color  
 * 
 * change the background color of the incoming node
 *
 * @param string Incoming node data (HTML)
 * @param object Incoming DOM node
 * @param array Parameter stack
 * @return null DOM node is changed by reference
 */

	function hexColor($data, &$node, $params) { 
		$node->set_attribute("style","background-color: #".$params[2]);
	}


/**
 * uc
 * 
 * change the incoming data to uppercase
 *
 * @param string Incoming node data (HTML)
 * @param object Incoming DOM node
 * @param array Parameter stack
 * @return string HTML in UPPERCASE
 */

	function uc($data, &$node, $params) { 
		return strtoupper($data);
	}

/**
 * lc
 * 
 * change the incoming data to lowercase
 *
 * @param string Incoming node data (HTML)
 * @param object Incoming DOM node
 * @param array Parameter stack
 * @return string HTML in lowercase
 */

	function lc($data, &$node, $params) { 
		return strtolower($data);
	}


}
?>
