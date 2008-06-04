<?php

/**
 * various methods to change text
 *
 */

class Cgn_Plugin_DebugView {

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

	function debugHtml($data, &$node, $params) { 
		debugView::addHtmlDebugStyle($node);
		foreach($node->child_nodes() as $kid) {
			if ($kid->node_type() == XML_ELEMENT_NODE) {
				debugView::addHtmlDebugStyle($kid);
			}
			//add underlines to big words
			if ($kid->node_type() == XML_TEXT_NODE) {
				debugView::stylizeLongWords($kid);
			}
			debugView::debugHtml($data,$kid,$params);

		}
	}


	function stylizeLongWords(&$node) {
		$words = explode(" ", $node->get_content());
		$newWords = '';
		foreach ($words as $w) {
			if (strlen($w) > 5) {
				$newWords .= '<a href="#" style="color:blue">'.$w.'</a> ';
			} else {
				$newWords .= $w.' ';
			}
		}
		$node->set_content($newWords);
	}


	function addHtmlDebugStyle(&$node) {
		switch ($node->node_name()) {
			case 'h2':
				$node->set_attribute("style","border: 1px solid red;");
				break;
			case 'div':
				$node->set_attribute("style","border: 1px dashed black;");
				break;
			case 'p':
				$node->set_attribute("style","border: 1px solid green;");
				break;
		}
	}
}
?>
