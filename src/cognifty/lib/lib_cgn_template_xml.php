<?php

include(Cgn_ObjectStore::getString("config://cgn/path/lib")."/lib_cgn_template.php");

class Cgn_Template_XML extends Cgn_Template {


	var $templateName = 'default';
	var $templateStyle = 'index';



/**
 * force http charset to UTF-8
 * because the PHP DOM seems to only deal in UTF-8
 */
	function parseTemplate() {

		header("Content-type: text/html; charset=UTF-8");
		$x = Cgn_Db_Connector::getHandle("default");

		$t = Cgn_ObjectStore::getArray("template://variables/");
		$templateName = Cgn_ObjectStore::getString("config://template/default/name");
		$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");

		$globalFilters = '';
		if (Cgn_ObjectStore::hasConfig("config://template/filters")) { 
			$globalFilters =& Cgn_ObjectStore::getConfig("config://template/filters");
		}

		ob_start();
		include( $baseDir. $templateName.'/index.html.php');
		$content = ob_get_contents();
		ob_end_clean();

		$newContent = $this->parseFilters($content, $globalFilters);
		//if the XML parsing fails we'll get false
		if ($newContent == false) { echo $content; }
		else { echo $newContent; }

	}



/**
 * take the content and scan it for 'filter' attributes on divs
 * 
 * run those filter modules and modify the passed in content 
 * accordingly
 * 
 * plugins should be run innermost to outermost based on location 
 * in the supplied html
 * 
 * @param string html content
 * @param string string of global filters to be applied to this content, 
 *			in addition to parsing filters from the content
 * @return string content modified by the filter processes
 */

	function parseFilters($content, $globalFilters = '') { 

		static $filterIdCount = 0;

		$hasDocType = false;
		$docType = '';

/**
 * trying to grab the doctype for later reuse
 * this can obviously be refactored!
 */

		preg_match("/\<!DOCTYPE(.*)\>/isU",$content,$matches);
		if(@$matches[0]!='') { 
			$docType = $matches[0];
			$hasDocType = true;
				$content = str_replace($docType,"",$content);
		}


		if ($globalFilters!='') { 
			$content = "<filterwrapper id=\"_cgn\" filter=\"$globalFilters\">$content</filterwrapper>";
		} else { 
			$content = "<filterwrapper id=\"_cgn\">\n$content</filterwrapper>";
		}


/**
 * entity list to deal with crappy php4 dom 
 * the entity list is appended to the top of the document
 * which we've stripped of its original doctype
 * this htmlentitylist comes with a doctype of its own
 * suitable for parsing most of the standard html entity things
 * like &copy; and so on
 * we keep it in a variable because we make sure its not in the content 
 * we send back
 */
		$top = htmlEntityList();
		$content = trim($top.$content);

/**
 * open the snippet as a dom doc
 */
		$doc = domxml_open_mem($content,(DOMXML_LOAD_PARSING|DOMXML_LOAD_SUBSTITUTE_ENTITIES), $e);
		if (!$doc) {
//			print_r($e);
//			echo nl2br(htmlentities($content));
			//bad HTML, just return
			return false;
		} //echo $content;}

/*
 // uncomment for debugging, dealing with errors
		if(count($e)>0) {
			ob_end_flush();
			print_r($e);
			$x = explode("\n",$content);
			print_r($x);
			echo $x[$e[0]['nodename']]."<BR/>";
			die($content);
		}
*/

		$xpath = $doc->xpath_new_context();


/**
 * PLUGIN
 */
		$obj = $xpath->xpath_eval("//*[@plugin]"); // 
		$nodeset =&$obj->nodeset;

/**
 * reverse the set to get all inner nodes first - parse from the inside out
 */
		$nodeset = array_reverse($nodeset);
		foreach($nodeset as $k=>$node) {
			$pluginName = $node->get_attribute('plugin');
			if (trim($pluginName)=='') { 
				continue;
			}
			$id = $node->get_attribute('id');
			if ($id=='') { 
				$id = '_cgn_id_'.$filterIdCount;
				$filterIdCount++;
				$node->set_attribute('id', $id);
			}
			$att = $node->attributes();
			$params = array();
			foreach($att as $k=>$v) {
				$params[$v->name] = $v->value;
			}

			if(is_object($node)){  
				list($name,$method) = split('/', $pluginName);	
				@$temp =&Cgn_ObjectStore::getObjectbyConfig("plugins://default/$name");
				$content = $temp->$method($params);
				$newnode = replace_content($node, $content);
				#$nodeset[$k]->replace_node($newnode);
				#$node->replace_node($newnode);
				$idnodequery= $xpath->xpath_eval_expression("//*[@id='$id']"); // 
				$idnode =&$obj->nodeset[0];
				$idnode =&$newnode;
			}
		}








/**
 * run xpath to grab all nodes with 'filter' attributes
 */

		$obj = $xpath->xpath_eval_expression("//*[@filter]"); // 
		$nodeset =&$obj->nodeset;

/**
 * reverse the set to get all inner nodes first - parse from the inside out
 */
		$nodeset = array_reverse($nodeset);
		foreach($nodeset as $k=>$node) {
			$function = $node->get_attribute('filter');
			if (trim($function)=='') { 
				continue;
			}
			$id = $node->get_attribute('id');
			if ($id=='') { 
				$id = '_cgn_id_'.$filterIdCount;
				$filterIdCount++;
				$node->set_attribute('id', $id);
			}
			$allfunctions = explode(" ",$function);
			if(is_object($node)){  
				$content = getContentAsString($node);
				$originalcontent = $content;
// loop through all the functions we have
				foreach($allfunctions as $function) { 
// original content
					if (trim($function)!='') { 
						$params = split('/', $function);
						$obj = $params[0];
						$method = $params[1];

						@$temp =&Cgn_ObjectStore::getObjectByConfig("filters://default/$obj");
						$newvalue = $temp->$method($content, $node, $params);
						if(is_string($newvalue)) {
							$content = $newvalue;
						} else { 
							$content = getContentAsString($node);
						}
					}
				}
				if ($content != $originalcontent) { 
					$newnode = replace_content($node, $content);
					#$nodeset[$k]->replace_node($newnode);
				}
				$idnodequery= $xpath->xpath_eval_expression("//*[@id='$id']"); // 
				$idnode =&$obj->nodeset[0];
				$idnode =&$newnode;
			}
		}



		$obj = $xpath->xpath_eval_expression("//filterwrapper[@id='_cgn']"); // the wholeko
		$masternode = $obj->nodeset[0];


/**
 * if we have a DOCTYPE, assume we have been parsing/processing 
 * and entire document, so we add the doc type back on 
 * 
 */

		$output = $docType.getContentAsString($masternode);

// Can we get rid of getContentAsString and use dump_node instead?  
// It escapes html entities though!
		#$output = $docType.$doc->dump_node($masternode);
		$output = str_replace($top,"",$output);

		unset($doc);

		return $output;
	}

}


/**
 * function from php.net
 */

function getContentAsString($node) {   
	if(!is_object($node)) {
		return '';
	}
	$st = "";

	foreach ($node->child_nodes() as $cnode) {
		if ($cnode->node_type()==XML_TEXT_NODE) {
			$st .= $cnode->node_value();
		} else if ($cnode->node_type()==XML_ELEMENT_NODE) {

			$st .= "<" . $cnode->node_name();

			if ($attribnodes=$cnode->attributes()) {
				$st .= " ";
				foreach ($attribnodes as $anode) {
					if ($anode->node_name()!='filter') { 
						$st .= $anode->node_name() . "='" .
						$anode->node_value() . "' ";
					}
				}
			}   

			$nodeText = getContentAsString($cnode);

			if (empty($nodeText) && !$attribnodes) {
				$st .= " />";        // unary
			} else {
				$st .= ">" . $nodeText . "</" .
				$cnode->node_name() . ">";
			}
		}
	}
	return $st;
}



/**
 * from php.net
 */
function replace_content( &$node, &$new_content, $node_type='' ) { 
	$dom =& $node->owner_document();
	$dom->create_element('div');
	if ($node_type=='') { 
		$node_type=$node->tagname;
	}
	/**
	 * for some reason PHP 5 doesn't like this version 4 code
	$newnode = $dom->create_element("div");
	 */
	/*
	$newnode->set_content($new_content);
	*/
	$node->set_content($new_content);
	$attributes =& $node->attributes();
	$allowed = array("id","class","style","on");
	foreach ($attributes as $att) {
//
// when making a new replacement node, don't include the cgn-specific 'filter' attribute
// 
		$temp = strtolower($att->name);
		if (in_array($temp, $allowed) || substr($temp,0,2)=='on') {
			$node->set_attribute( $att->name, $att->value );
		}
	}
//	$node->replace_node( $newnode );
#return $newnode;
}


function htmlEntityList() { 
return '<?xml version="1.0"?>
<!DOCTYPE filterwrapper [ 
<!ENTITY nbsp   "&#160;">
<!ENTITY iexcl  "&#161;">
<!ENTITY cent   "&#162;">
<!ENTITY yuml   "&#255;">
<!ENTITY ndash  "&#8211;">
<!ENTITY mdash  "&#8212;">
<!ENTITY nbsp   "&#160;">
<!ENTITY iexcl  "&#161;">
<!ENTITY cent   "&#162;">
<!ENTITY pound  "&#163;">
<!ENTITY curren "&#164;">
<!ENTITY yen    "&#165;">
<!ENTITY brvbar "&#166;">
<!ENTITY sect   "&#167;">
<!ENTITY uml    "&#168;">
<!ENTITY copy   "&#169;">
<!ENTITY ordf   "&#170;">
<!ENTITY laquo  "&#171;">
<!ENTITY not    "&#172;">
<!ENTITY shy    "&#173;">
<!ENTITY reg    "&#174;">
<!ENTITY macr   "&#175;">
<!ENTITY deg    "&#176;">
<!ENTITY plusmn "&#177;">
<!ENTITY sup2   "&#178;">
<!ENTITY sup3   "&#179;">
<!ENTITY acute  "&#180;">
<!ENTITY micro  "&#181;">
<!ENTITY para   "&#182;">
<!ENTITY middot "&#183;">
<!ENTITY cedil  "&#184;">
<!ENTITY sup1   "&#185;">
<!ENTITY ordm   "&#186;">
<!ENTITY raquo  "&#187;">
<!ENTITY frac14 "&#188;">
<!ENTITY frac12 "&#189;">
<!ENTITY frac34 "&#190;">
<!ENTITY iquest "&#191;">
<!ENTITY Agrave "&#192;">
<!ENTITY Aacute "&#193;">
<!ENTITY Acirc  "&#194;">
<!ENTITY Atilde "&#195;">
<!ENTITY Auml   "&#196;">
<!ENTITY Aring  "&#197;">
<!ENTITY AElig  "&#198;">
<!ENTITY Ccedil "&#199;">
<!ENTITY Egrave "&#200;">
<!ENTITY Eacute "&#201;">
<!ENTITY Ecirc  "&#202;">
<!ENTITY Euml   "&#203;">
<!ENTITY Igrave "&#204;">
<!ENTITY Iacute "&#205;">
<!ENTITY Icirc  "&#206;">
<!ENTITY Iuml   "&#207;">
<!ENTITY ETH    "&#208;">
<!ENTITY Ntilde "&#209;">
<!ENTITY Ograve "&#210;">
<!ENTITY Oacute "&#211;">
<!ENTITY Ocirc  "&#212;">
<!ENTITY Otilde "&#213;">
<!ENTITY Ouml   "&#214;">
<!ENTITY times  "&#215;">
<!ENTITY Oslash "&#216;">
<!ENTITY Ugrave "&#217;">
<!ENTITY Uacute "&#218;">
<!ENTITY Ucirc  "&#219;">
<!ENTITY Uuml   "&#220;">
<!ENTITY Yacute "&#221;">
<!ENTITY THORN  "&#222;">
<!ENTITY szlig  "&#223;">
<!ENTITY agrave "&#224;">
<!ENTITY aacute "&#225;">
<!ENTITY acirc  "&#226;">
<!ENTITY atilde "&#227;">
<!ENTITY auml   "&#228;">
<!ENTITY aring  "&#229;">
<!ENTITY aelig  "&#230;">
<!ENTITY ccedil "&#231;">
<!ENTITY egrave "&#232;">
<!ENTITY eacute "&#233;">
<!ENTITY ecirc  "&#234;">
<!ENTITY euml   "&#235;">
<!ENTITY igrave "&#236;">
<!ENTITY iacute "&#237;">
<!ENTITY icirc  "&#238;">
<!ENTITY iuml   "&#239;">
<!ENTITY eth    "&#240;">
<!ENTITY ntilde "&#241;">
<!ENTITY ograve "&#242;">
<!ENTITY oacute "&#243;">
<!ENTITY ocirc  "&#244;">
<!ENTITY otilde "&#245;">
<!ENTITY ouml   "&#246;">
<!ENTITY divide "&#247;">
<!ENTITY oslash "&#248;">
<!ENTITY ugrave "&#249;">
<!ENTITY uacute "&#250;">
<!ENTITY ucirc  "&#251;">
<!ENTITY uuml   "&#252;">
<!ENTITY yacute "&#253;">
<!ENTITY thorn  "&#254;">
<!ENTITY yuml   "&#255;">
<!ENTITY rsaquo   "&#8250;">
<!ENTITY rsquo   "&#8217;">
<!ENTITY raquo   "&#187;">
]>';

}
?>
