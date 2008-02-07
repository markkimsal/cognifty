<?php

/**
 * PHP 4/5 Xml DOM Parser.
 *
 * Usage: 
 *
 *	$chunk = '<?xml version="1.0"?>
 *	<itemList type="foo">
 *	<item>1</item>
 *	<item>2</item>
 *	<item>3</item>
 *	<item>N</item>
 *	</itemList>';
 * 	$p = Mf_Xml_Parser::createParser();
 * 	$d = $p->parseXml($chunk);
 * 	echo $d->asText();	//debug structure
 *	echo $d->rootNode->item[0]->_charData;	// 1
 *	echo $d->rootNode->item[3]->_charData;	// N
 *	echo $d->rootNode->_attrs['type'];	// foo
 *
 */
class Mf_Xml_Parser {

	function createParser() {
		if (intval(PHP_VERSION) == 4) {
			return new Mf_Xml_Parser4();
		} else {
			return new Mf_Xml_Parser5();
		}
	}

	function &parseXmlFile($filename) {
		$xml = file_get_contents($filename);
		return $this->parseXml($xml);
	}
}


class Mf_Xml_Parser5 extends Mf_Xml_Parser4 {
}

class Mf_Xml_Parser4 extends Mf_Xml_Parser {

	var $_resource;
	var $_xml;
	var $_document;
	var $_tagStack = array();

	function Mf_Xml_Parser4() {
	}

	/**
	 * Do the real parsing
	 * returns a Mf_Xml_Document
	 */
	function &parseXml($xmlString) {
		$this->_xml = $xmlString;
		$this->_document = new Mf_Xml_Document();

		$this->_resource = xml_parser_create();
		if (! is_resource($this->_resource)) {
			die ("can't make parser\n");
		}

		//Set the handlers
		xml_set_object($this->_resource, $this);
		xml_parser_set_option( $this->_resource, XML_OPTION_CASE_FOLDING, false );
		xml_set_element_handler($this->_resource, 
			'StartElement', 'EndElement');
		xml_set_character_data_handler($this->_resource, 
			'CharacterData');
		xml_set_external_entity_ref_handler($this->_resource,
			'EntityRef');
		xml_set_default_handler($this->_resource,
			'DefaultHandler');

	        //Error handling
		$res = xml_parse($this->_resource, $this->_xml);

		if (!$res) {
//			$this->error(xml_get_error_code($this->parser), xml_get_current_line_number($this->parser), xml_get_current_column_number($this->parser));
			echo $this->_xml."\n";
			echo xml_error_String(xml_get_error_code($this->_resource))."\n";
			echo "line: ". xml_get_current_line_number($this->_resource)."\n";
			echo "col: ". xml_get_current_column_number($this->_resource)."\n";
		//	xml_get_current_column_number($this->parser
			echo("error creating parser resource\n");
			xml_parser_free($this->_resource);
			return 0;
		}

		//Free the parser
		xml_parser_free($this->_resource);
		return $this->_document;
	}



	function DefaultHandler($parser, $data)
	{
	   if ($data == '&nbsp;') {
		$this->_document->appendCharacterData(' ');
	   }
	   return true;
	}

	function CharacterData($res, $content) {
		//&amps; are strange, the interrupt the stream of
		// char data, but don't call DefaultHandler,
		// like &nbsp; does.  So, we assume there's a space on 
		// each side.
	   if ($content == '&') {
		$this->_document->appendCharacterData(' & ');
		return;
	   }

		$this->_document->appendCharacterData(trim($content," \t"));
	}


	function EntityRef($res, $content, $base, $system,$public) {
		echo $content;
		echo "\n";
		return false;
	}


	function StartElement($res, $name, $attrs) { 
		if ( !$this->_document->hasRootNode() ) {
			$this->_document->setRootNode( new Mf_Xml_Tag($name,$attrs));
		} else {

			$this->_document->appendNode( new Mf_Xml_Tag($name,$attrs));
		}
	}


	function EndElement($res, $name) {
		$this->_document->closeCurrentNode();
       	}
}


class Mf_Xml_Document {

	var $rootNode = null;
	var $nodePointer = null;
	var $stack = array();
	var $nodeCount;

	function hasRootNode() {
		return is_object($this->rootNode);
	}

	function setRootNode(&$n) {
		$this->rootNode =& $n;
		$this->nodePointer =& $n;
	}


	function appendNode(&$n) {
//echo "*** appending to : [".$this->nodePointer->_tagName."]\n";
//if ($this->nodePointer->_tagName == "td" ) {
//	print_r($this->nodePointer->_childTags);
//}
//echo "*** appending node: [".$n->_tagName."]\n";
//echo "***\n\n";
		$this->nodeCount++;
		if (is_null($this->nodePointer)) {
			$this->nodePointer =& $n;
			$childName = $n->_tagName;
//			$this->rootNode->_childTags[$childName] = $childName;
//			$this->rootNode->{$childName}[] =& $n;

			if ( is_array($this->rootNode->{$childName}) ) {
				$childPos = (int)count($this->rootNode->{$childName});
			} else {
				$childPos = '0';
			}
if ($childPos =='') die("$childPos\n wrong\n\n\n");
			$this->rootNode->_childTags[] = $childName.",".$childPos;
			$this->rootNode->{$childName}[$childPos] =& $n;

			$this->stack[] =& $n;
		} else {
//			if ($this->nodePointer->isOpen) {
				$currentPointer =& $this->nodePointer;
				$childName = $n->_tagName;

				if ( is_array($currentPointer->{$childName}) ) {
					$childPos = (int)count($currentPointer->{$childName});
				} else {
					$childPos = '0';
				}
if ($childPos =='') die("$childPos\n wrong\n\n\n");
				$currentPointer->_childTags[] = $childName.",".$childPos;
				$currentPointer->{$childName}[$childPos] =& $n;
				$this->nodePointer =& $n;
				$this->stack[] =& $n;
//			}
		}
		$n->setDepth(count($this->stack));
	}


	function appendCharacterData($content) {
//		echo "***: '".$content."'\n";
//		if ( trim($content) ) {
			$this->nodePointer->_charData[] = $content;
//		}
	}

	function setCharacterData($content) {
		if ( trim($content) ) {
			$this->nodePointer->_charData = array($content);
		}
	}


	function closeCurrentNode() {

//echo "*** closing to : [".$this->nodePointer->_tagName."]\n";
if ($this->nodePointer->_tagName == 'b') {
	$showParent = true;
	}
/*
echo "*** closing node: [".$this->nodePointer->_tagName."]\n";
print_r(count($this->stack));
print_r($this->stack[(count($this->stack)-2)]);
print_r($this->stack);
//*/
		//clean up inner content as a whole
		//$this->nodePointer->_charData = trim($this->nodePointer->_charData);

		if (count($this->stack) < 2) {
			$this->nodePointer =& $this->rootNode;
			$this->stack = array();
		} else {
			$this->nodePointer =& $this->stack[(count($this->stack)-2)];
		}
		if ($showParent) {
//		echo "*** new parent : [".$this->nodePointer->_tagName."]\n";
		}
		//clean up stack
		for($x =0; $x < count($this->stack)-1; ++$x) {
			$newStack[] =& $this->stack[$x];
		}
		$this->stack = $newStack;
//print_r($this->nodePointer);
//echo "*** \n\n";
	}


	function asText() {

		$string = "[Doc] : encoding => unknown; node count => ".$this->nodeCount." \n";
//		$string .= "[Root]: ".$this->rootNode->_tagName."\n";
		$string .= $this->rootNode->asText();

		return $string;
	}


	function asXml() {

		$string = '<?xml version="1.0" encoding="UTF-8"?>';
		$string .= $this->rootNode->asXml(0);

		return $string;
	}
}



class Mf_Xml_Tag {

	var $_tagName;
	var $_attrs = array();
	var $_childTags = array();
	var $_depth = null;
	var $_charData = array();
	var $_needsCdata = false;
	var $_needsBinEsc = false;


	function Mf_Xml_Tag($name,$attrs = array()) {
		$this->_tagName = $name;
		$this->_attrs = $attrs;
	}

	function setDepth($d) {
		$this->_depth = $d;
	}


	function asText($count=0) {
		$string = "[Node]: ".str_repeat("    ",$this->_depth). $this->_tagName."[".$count."]";

		if ( count($this->_childTags) > 0) {
			$string .="-> (".count($this->_childTags).")";
		}
		if ( count($this->_charData) > 0) {
			$string .= " (".substr(trim($this->_charData[0]),0,25)."...)";
		}
		$string .= "\n";
		foreach($this->_childTags as $blank => $childName ) {
			list($childTagName,$pos) = explode(",",$childName);
			//foreach($this->{$childName} as $pos => $individual ) {
				$string .= $this->{$childTagName}[$pos]->asText($pos);
			//}
		}
		return $string;
	}


	function asXml($count=0) {

		$string = "\n".str_repeat("    ",$this->_depth). '<'.$this->_tagName.'';
		foreach ($this->_attrs as $key=>$val) {
			$string .= ' '.$key.'="'.$val.'"';
		}
		if ( count($this->_childTags) > 0 || count($this->_charData) > 0) {
			$needsClose = true;
			$string .=">";
		} else {
			$needsClose = false;
			$string .="/>\n";
		}
		if ( is_array($this->_charData) and count($this->_charData) > 0 ) {
			if ($this->_needsCdata) {
				$string .= "<![CDATA[";
			}
			$string .= implode('',$this->_charData)."";
			if ($this->_needsCdata) {
				$string .= "]]>";
			}
		}

		foreach($this->_childTags as $blank => $childName ) {
//			foreach($this->{$childName} as $pos => $individual ) {
//print				var_dump($individual);
				list($childTagName,$pos) = explode(",",$childName);
			//	$string .= $individual->asXml();
				$string .= $this->{$childTagName}[$pos]->asXml($pos);
//			}
		}

		if ($needsClose) {

			if ( count($this->_childTags) > 0 ) {
			$string .= str_repeat("    ",$this->_depth);
			}
			$string .= '</'.$this->_tagName.">\n";
		}
		return $string;
	}

	
	function getCharData() {
		return implode('',$this->_charData);
	}
}


?>
