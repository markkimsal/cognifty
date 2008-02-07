<?php


class Cgn_Wiki_Document {

	var $text = '';
	var $parserObj;
	var $renderObj;
	var $_sentinelToken;
	var $_pCurrentToken;
	var $inlineTokens = array();
	var $blockTokens = array();

	function Cgn_Wiki_Document() {
		$this->parserObj = new Cgn_Wiki_Parser();
		$this->renderObj = new Cgn_Wiki_RenderDebug();
//		$this->renderObj = new Cgn_Wiki_Render();
		$this->_sentinelToken = new Cgn_Wiki_BlockToken('root','');
		$this->blockTokens[] = $this->_sentinelToken;
		$this->_pCurrentToken = & $this->_sentinelToken;
	}


	function parse($text='') {
		$this->parserObj->parseWikiText($this->text, $this);
		//print_r($this);
	}


	function toHtml() {
		$html = '';
		//sentinel node is block number 0
		$sentinel = $this->blockTokens[0];
//		$html .= $sentinel->render($this->inlineTokens,$this->blockTokens);
		$this->renderObj->renderToken($sentinel, $this->inlineTokens, $this->blockTokens, $this);

		/*
		foreach ($sentinel->childTokens as $id) {
			if (!is_object($this->inlineTokens[$id]) ) {
				$t = $this->blockTokens[$id];
			} else {
				$t = $this->inlineTokens[$id];
			}
			$this->renderObj->renderToken($t, $this->inlineTokens, $this->blockTokens);
		}
		// */
		$this->renderObj->cleanup();
		return "<pre>\n".$this->renderObj->output."\n</pre>";
//		return $this->renderObj->output;
	}


	function closeToken() {
		$pid = $this->_pCurrentToken->_parentId;

		//close the node
		$this->_pCurrentToken->isOpen = false;
		//overwrite with a copy
		$currentid = $this->_pCurrentToken->_id;
		$this->blockTokens[$currentid] = $this->_pCurrentToken;

		$parent = $this->blockTokens[$pid];
		$this->_pCurrentToken =& $parent;
	}

	function appendToken($n) {
		$n->_id = count($this->blockTokens) + count($this->inlineTokens)+1;

		//don't allow nested p tags
		if ($this->_pCurrentToken->type == 'p'
			&& $n->type == 'p') {
			$this->closeToken();
		}

		if ($this->_pCurrentToken->isOpen) {
			$n->_parentId = $this->_pCurrentToken->_id;
			$this->_pCurrentToken->childTokens[$n->_id] = $n->_id;

			//overwrite with a copy
			$pid = $this->_pCurrentToken->_id;
			$this->blockTokens[$pid] = $this->_pCurrentToken;
		}

		if (!$n->isBlock) {
			$this->inlineTokens[$n->_id] = $n;
		} else {
			$this->blockTokens[$n->_id] = $n;
		}

//			$this->tokens[$n->_id] = $n;
		if ($n->isOpen) {
			$this->_pCurrentToken =& $n;
		//	die('newblock');
//		} else { 
		}
	}
}


class Cgn_Wiki_BlockElement {
}


class Cgn_Wiki_InlineElement {
}


class Cgn_Wiki_Render {

	var $output = '';
	var $lastLi = 0;
	var $pOpen = false;

	function cleanup() {
		/*
		echo "CLEANUP \n";
			echo "last li ";
			echo "\n";
			print_r($this->lastLi);
			echo "\n";
			echo "\n";
		 */

		if ($this->lastLi != 0) {
			$this->output .= "</ul>\n";
			$this->lastLi = 0;
		}
		if ($this->pOpen) {
			$this->output .= "</p>\n";
			$this->pOpen = false;
		}
	}


	function renderToken($t, $inlineTokens, $blockTokens, $wikiDoc) {

		//stack lis for ul counting
		if ($t->type == 'li') {
			if ($this->lastLi < $t->extra ) {
				$this->output .= "<ul>\n";
			}
			if ($this->lastLi > $t->extra ) {
				$this->output .= "</ul>\n";
			}
			$this->lastLi = $t->extra ;
			/*
			print_r($t->extra);
			echo "\n";
			echo "last li ";
			echo "\n";
			print_r($this->lastLi);
			echo "\n";
			 */
		} else {
			if ($t->type != 'text') {
			while ($this->lastLi > 0) {
				$this->output .= "</ul>\n";
				$this->lastLi -= 1;
			}
			}
		}


		if ($t->type == 'p') {
			if ($this->pOpen) {
				$this->output .= "</p>\n<p>";
				$this->pOpen = true;
			} else {
				$this->output .= "<p>";
				$this->pOpen = true;
			}
		}

		if ($t->isBlock) {
//			$this->output  .= '<div>'."\n";

			foreach ($wikiDoc->parserObj->inlineSyntaxObjs as $syntax) {
				$tokens = array_merge($tokens,$syntax->parseLine($t->content));
				$t->content = $syntax->parseLine($t->content);
			}

			$this->output .= $t->getStartTag();
			$this->output .= $t->content;

			foreach ($t->childTokens as $id) {
				if (!is_object($inlineTokens[$id]) ) {
					$this->renderToken($blockTokens[$id], $inlineTokens,$blockTokens,$wikiDoc);
				} else {
					$this->renderToken($inlineTokens[$id],$inlineTokens,$blockTokens,$wikiDoc);
				}
			}
			$this->output .= $t->getCloseTag();
//			$this->cleanup();
//			$this->output  .= '</div>'."\n";
		} else {
			if ($t->type == 'text') {
//				$this->output .= $t->content;
				$tokens = array();
				if (!$t->isBlock) {
					foreach ($wikiDoc->parserObj->inlineSyntaxObjs as $syntax) {
						while($syntax->parseLine($t->content)) {
							if ($killcount++ > 10 ) { echo "kill counter"; break; }
						}

						//$tokens = array_merge($tokens,$syntax->parseLine($t->content));
//						$syntax->parseLine($txt_output);
					}
					$this->output .= $t->content;
					/*
//					print_r($tokens);
					if (is_array($tokens) && count($tokens) > 0 ) {
						foreach ($tokens as $tok) {
							$this->renderToken($tok,$inlineTokens, $blockTokens, $wikiDoc);
//							$this->output .= $t->render();
						}
					}
					 */
				}
			} else {
				//$this->output .= '<'.$t->type.' '.$t->extra.'>'.$t->content."</".$t->type.$t->extra.">\n";
					foreach ($wikiDoc->parserObj->inlineSyntaxObjs as $syntax) {
						while($syntax->parseLine($t->content)) {
							if ($killcount++ > 10 ) { echo "kill counter"; break; }
						}

//
//						$tokens = array_merge($tokens,$syntax->parseLine($t->content));
//						$t->content = $syntax->parseLine($t->content);
					}
				$this->output .= $t->render();
			}
		}
	}
}


class Cgn_Wiki_Parser {
	var $inlineSyntaxObjs = array();
	var $blockSyntaxObjs = array();

	function Cgn_Wiki_Parser() {
//		$this->inlineSyntaxObjs[] = new Cgn_Wiki_SyntaxHeading();
//		$this->inlineSyntaxObjs[] = new Cgn_Wiki_SyntaxList();
		$this->inlineSyntaxObjs[] = new Cgn_Wiki_SyntaxAnchor();
		$this->inlineSyntaxObjs[] = new Cgn_Wiki_SyntaxBold();
		$this->inlineSyntaxObjs[] = new Cgn_Wiki_SyntaxItalic();
		$this->blockSyntaxObjs[] = new Cgn_Wiki_SyntaxHeading();
		$this->blockSyntaxObjs[] = new Cgn_Wiki_SyntaxParagraph();
		$this->blockSyntaxObjs[] = new Cgn_Wiki_SyntaxList();
	}

	function parseWikiText($text, &$document) {

		$newlineText = preg_replace('/\r\n|\r/', "\n", $text); 
		$lines = explode("\n",$newlineText);
//		array_shift($lines);
//		print_r($lines);exit();
		foreach($lines as $line) {
			$foundBlock = false;
			foreach ($this->blockSyntaxObjs as $syntax) {
				$tokens = $syntax->parseLine($line);
				if (is_array($tokens) && count($tokens) > 0 ) {
					$foundBlock = true;
					foreach ($tokens as $tok) {
						$document->appendToken($tok);
					}
//					$document->tokens = array_merge($document->tokens,$tokens);
				} else {
				}
			}

			if (!$foundBlock) {
//			print_r($line);
//			print "\n\n";
				//add straight text block
				$document->appendToken(new Cgn_Wiki_InlineToken('text',$line));
			}

			/*
			foreach ($this->inlineSyntaxObjs as $syntax) {
				$tokens = $syntax->parseLine($line);
				if (count($tokens) > 0 ) {
					foreach ($tokens as $tok) {
						$document->appendToken($tok);
					}
					//$document->tokens = array_merge($document->tokens,$tokens);
				}
			}
			 */
	//		echo $line. "<br/>\n";
		}
//		$document->tokens[] = new Cgn_Wiki_Token('divclose','',true);

//		echo "<pre>\n";print_r($document);echo "</pre>\n";
//		exit();

	}
}

class Cgn_Wiki_BlockToken extends Cgn_Wiki_InlineToken {
	var $isBlock = true;
	var $isOpen = true;
	var $childTokens = array();
	var $_id = 0;

	function Cgn_Wiki_BlockToken($type, $content='', $extra='') {
		$this->type = $type;
		$this->content = $content;
		$this->extra = $extra;
	}
}

class Cgn_Wiki_InlineToken {

	var $childTokens = array();
	var $isBlock = false;
	var $isOpen = false;
	var $_id = 0;

	function Cgn_Wiki_InlineToken($type, $content='', $extra='') {
		$this->type = $type;
		$this->content = $content;
		if ($type == 'li' ) {
			$this->isOpen = false;
		}
		$this->extra = $extra;
	}

//'<'.$t->type.' '.$t->extra.'>'.$t->content."</".$t->type.$t->extra.">\n"
	function render() {
		switch ($this->type) {
			case 'text':
				return $this->content."\n";
			case 'h':
			return '<h'.$this->extra.'>'.$this->content."</h".$this->extra.">\n";
			/*
			case 'div':
				$s = '<div>'."\n";
				foreach ($this->childTokens as $id) {
					if (!is_object($inlineTokens[$id]) ) {
//						print_r($blockTokens[$id]);
					$s .= $blockTokens[$id]->render($inlineTokens,$blockTokens);
					} else {
					$s .= $inlineTokens[$id]->render($inlineTokens,$blockTokens);
					}
				}
				return $s.'</div>';
				break;
			case 'divclose':
			return '</div>'."\n";
			 */

			case 'li':
			return '<li>'.$this->content."</li>\n";

			/*
			case 'ul':
				$s = '<ul>'."\n";
				foreach ($this->childTokens as $id) {
					if (!is_object($inlineTokens[$id]) ) {
//						print_r($blockTokens[$id]);
					$s .= $blockTokens[$id]->render($inlineTokens,$blockTokens);
					} else {
					$s .= $inlineTokens[$id]->render($inlineTokens,$blockTokens);
					}
				}
				$s .= '</ul>'."\n";
				return $s;
			 */

			case 'a':
				return '<a href="'.$this->extra.'">'.$this->content."</a>\n";

				/*
			case 'p':
				return "</p><p>\n";
				 */
		}
	}


	function getStartTag() {
		if ($this->type == 'root') { return null; }
		return '<'.$this->type.'>';
	}

	function getCloseTag() {
		if ($this->type == 'root') { return null; }
		return '</'.$this->type.'>';
	}


}

class Cgn_Wiki_SyntaxHeading extends Cgn_Wiki_BlockSyntax {

	var $regex = '/^(={1,6}) *(.*?) *=*$/m';

	function parseLine($textLine) {
		$matches = array();
		if ( substr($textLine,0,1) != '=') { return $textLine; }

		preg_match($this->regex,$textLine,$matches);
		$tokens = array();
		$tokens[] = new Cgn_Wiki_InlineToken('h',$matches[2],'2');
//		$tokens[] = new Cgn_Wiki_Token('text', $matches[2]);
//		$tokens[] = new Cgn_Wiki_CloseToken('h1');
		return $tokens;
	}
}


class Cgn_Wiki_SyntaxBold extends Cgn_Wiki_InlineSyntax {

	var $regex = '/(\*\*)(.*?)(\*\*)/m';

	function parseLine(&$textLine) {

		$boldtag = strpos($textLine, '**');
		//this might be a bold if there's no space after a star
		if ($boldtag === false) {return false;}

		$matches = array();
		preg_match($this->regex,$textLine,$matches);
		//expensive regex check rules out ** in LIs
		if (count($matches) < 1) { return false;} 

		$textLine = str_replace($matches[0],'<strong>'.$matches[2].'</strong>', $textLine);
		return true;
	}
}

class Cgn_Wiki_SyntaxItalic extends Cgn_Wiki_InlineSyntax {

	var $regex = '/(\/\/)(.*?)(\/\/)/m';


	function parseLine(&$textLine) {
//		if ( substr($textLine,0,1) != '*') { return $textLine; }

		$boldtag = strpos($textLine, '//');
		//this might be a bold if there's no space after a star
		if ($boldtag === false) {return false;}

		$matches = array();
		preg_match($this->regex,$textLine,$matches);

//		print_r($textLine);
//		print_r($matches);echo "<br>\n";//exit();
		if (count($matches) < 1) { return false; }
		//probably a url
		if ( strpos($matches[0] ,'://')) { return false; }

		$textLine = str_replace($matches[0],'<em>'.$matches[2].'</em>', $textLine);
		return true;
	}
}



class Cgn_Wiki_SyntaxList extends Cgn_Wiki_BlockSyntax {

	//note: this should probably be unlimited
	var $regex = '/^(\*{0,10})\ /m';

	function parseLine($textLine) {
		if ( substr($textLine,0,1) != '*') { return $textLine; }

		//this might be a bold if there's no space after a star
		$matches = array();
		preg_match($this->regex, $textLine, $matches);
		if (count($matches) < 1) { /* echo "textline = $textLine"; */ return $textLine;}

		$space = strpos($textLine, ' ');

		$level = substr_count(substr($textLine,0,$space), '*');
		/*
		print $level."<br/>\n";
		print $space."<br/>\n";
		print $textLine."<br/>\n";
		 */

		$content = substr($textLine,($space+1));

		$tokens = array();
		$tokens[] = new Cgn_Wiki_BlockToken('li',$content,$level);
		return $tokens;
	}
}


class Cgn_Wiki_SyntaxAnchor extends Cgn_Wiki_InlineSyntax {


	function parseLine(&$textLine) {

//	$this->regex = '/((?:\[\[((?:http:\/\/|https:\/\/|ftp:\/\/|mailto:|\/)[^\|\]\n ]*)(\|([^\]\n]*))?\]\])|((http:\/\/|https:\/\/|ftp:\/\/|mailto:)[^\'\"\n '."\xFF".']*[A-Za-z0-9\/\?\=\&\~\_]))/';
	$this->regex = "/(\[\[)(.+)(\]\])/";
//	$this->regex = "/(\[)(.+)(\])/";
		$matches = array();
//		if ( substr($textLine,0,1) != '[') { return null; }

		preg_match($this->regex,$textLine,$matches);
		if (count($matches)<1) { return false; }
		
		/*
echo "<pre>";	
print_r($textLine);
print_r($matches);
echo "</pre>";	
		 //*/

		$content = $matches[1];
		$href = trim($matches[1]);
		$text = trim($matches[4]);
		$rawurl = $matches[5];

		if (count($matches) > 4) {
			$textLine = str_replace('['.$matches[2].']', '<a href="'.$matches[1].'">'.$matches[1].'</a>',$textLine);
		} else {
			$textLine = str_replace('[['.$matches[2].']]', '<a href="'.$matches[2].'">'.$matches[2].'</a>',$textLine);
		}

		return true;
		/*
		$tokens = array();
		$tokens[] = new Cgn_Wiki_Token('a',$content,false,$href);
		return $tokens;
		 */
	}
}



class Cgn_Wiki_BlockSyntax {
}

class Cgn_Wiki_InlineSyntax {
}

class Cgn_Wiki_SyntaxParagraph extends Cgn_Wiki_BlockSyntax {

	function parseLine($textLine) {
		$tokens = array();
//echo $textLine;
		if (trim($textLine) == "") {
			$tokens[] = new Cgn_Wiki_BlockToken('p','');
		} else {
//			$tokens[] = new Cgn_Wiki_Token('text',$textLine, false);
		}
		return $tokens;
	}
}



class Cgn_Wiki_RenderDebug extends Cgn_Wiki_Render {

	var $output = '';
	var $lastLi = 0;
	var $pOpen = false;

	function cleanup() {
		if ($this->lastLi != 0) {
			$this->output .= "</ul>\n";
			$this->lastLi = 0;
		}
		if ($this->pOpen) {
			$this->output .= "</p>\n";
			$this->pOpen = false;
		}
	}


	function renderToken($t, $inlineTokens, $blockTokens, $wikiDoc) {


		/*
		//stack lis for ul counting
		if ($t->type == 'li') {
			//print_r($t->extra);
			if ($this->lastLi < $t->extra ) {
				$this->output .= "<ul>\n";
			}
			if ($this->lastLi > $t->extra ) {
				$this->output .= "</ul>\n";
			}
			$this->lastLi = $t->extra ;
		} else {
			while ($this->lastLi > 0) {
				$this->output .= "</ul>\n";
				$this->lastLi -= 1;
			}
		}


		if ($t->type == 'p') {
			if ($this->pOpen) {
				$this->output .= "</p>\n<p>";
				$this->pOpen = true;
			} else {
				$this->output .= "<p>";
				$this->pOpen = true;
			}
		}
		// */

		if ($t->isBlock) {
			$this->output  .= "<div style=\"border:1px solid black; padding:2px;\">\n";
			$this->output  .= "\n".'Block ('.$t->_id.') ' . $t->type."\n";
			$this->output  .= 'Block Content ' . $t->content."\n";
			$this->output  .= "Children: \n";

			foreach ($t->childTokens as $id) {
				$this->output .= "  ". $id."<br/>\n";
			}

			foreach ($t->childTokens as $id) {
				if (!is_object($inlineTokens[$id]) ) {
					$childT = $blockTokens[$id];
				} else {
					$childT = $inlineTokens[$id];
				}
				$this->renderToken($childT, $inlineTokens,$blockTokens,$wikiDoc);
			}

			$this->cleanup();
			$this->output  .= "\n".'End Block'."\n";
			$this->output  .= "</div>\n";
		} else {
			$this->output  .= "<div style=\"border:1px solid black; padding:2px;\">\n";
			$this->output  .= "\n".'Line ('.$t->_id.') ' . $t->type."\n";
			$this->output  .= 'Line Content ' . $t->content."\n";

			if ($t->type == 'text') {
//				$this->output .= $t->content;
				$tokens = array();
				if (!$t->isBlock) {
//					print_r( $wikiDoc->parserObj->inlineSyntaxObjs );
//					exit();
					foreach ($wikiDoc->parserObj->inlineSyntaxObjs as $syntax) {
						$killcount = 0;
						//returns true if there was a modification.
						//need to keep parsing the content until there are no more
						// inline changes to be made.
						while($syntax->parseLine($t->content)) {
							if ($killcount++ > 10 ) { echo "kill counter"; break; }
						}
					}
					$this->output .= $t->content;
					/*
//					print_r($tokens);
					if (is_array($tokens) && count($tokens) > 0 ) {
						foreach ($tokens as $tok) {
							$this->renderToken($tok,$inlineTokens, $blockTokens, $wikiDoc);
//							$this->output .= $t->render();
						}
					}
					 */
				}
			} else {
				//$this->output .= '<'.$t->type.' '.$t->extra.'>'.$t->content."</".$t->type.$t->extra.">\n";
					foreach ($wikiDoc->parserObj->inlineSyntaxObjs as $syntax) {
					//	$tokens = array_merge($tokens,$syntax->parseLine($t->content));
						while($syntax->parseLine($t->content)) {
							if ($killcount++ > 10 ) { echo "kill counter"; break; }
						}
					}
				$this->output .= $t->render();
			}
			$this->output  .= "</div>\n";
		}
	}
}


?>
