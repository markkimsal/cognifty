<?php

class Cgn_Mvc_TreeItem  {

	var $children = array();
	var $id = 0;
	var $root = false;

	function Cgn_Mvc_TreeItem($data='') {
		$this->data = $data;
	}

	/*
	function appendNode($node) {
		$this->_siblingPointer = $node->getId();
	}
	 */

	function getId() {
		return $this->id;
	}

}


/**
 * How this class works.
 * This class is intended to provide an interface between two systems:
 *  Mvc_Views by passing around ModelIndex objects
 *  TreeItem objects to provide the actual hierarchy
 *
 * To avoid recursion problems with PHP4, this class will hold
 * all indexes and data globally and only pass around array indexes as a 
 * type of "pointer".
 *
 * When a new ModelIndex is created with a specified parent, the parent object 
 * will be another ModelIndex.
 */
class Cgn_Mvc_TreeModel extends Cgn_Mvc_DefaultItemModel {

	function Cgn_Mvc_TreeModel() {
		$x = new Cgn_Mvc_ModelNode('');
		$x->root = true;
		$this->setRootNode($x); 

		$y = new Cgn_Mvc_TreeItem('');
		$y->root = true;
		$this->trackItem($y);
		$this->rootItem = $y;
	}

	function &root() {
		return $this->_rootNode;
	}

	/**
	 * Transform an array into a tree list of model nodes
	 */
	function appendChild(&$treeItem, $parentItem) {
		$this->trackItem($treeItem);

		if ($parentItem == null) {
	//		$parentItem =& $this->_rootNode;
			$this->rootItem->children[] = $treeItem->getId();
		} else {
			//get the global reference
			$parentItem =& $this->getItem($parentItem);
	//		$parentItem->appendChild($treeItem);
			$parentItem->children[] = $treeItem->getId();
			$treeItem->_parentPointer = $parentItem->getId();
		}
	}

	function trackItem(&$treeItem) {
		$treeItem->id = count($this->itemList)+1;
		$this->itemList[$treeItem->getId()] = &$treeItem;
	}

	function &getItem($treeItem) {
		return $this->itemList[$treeItem->getId()];
	}

	function &getParent($treeItem) {
//		cgn::debug($treeItem);
		return $this->itemList[$treeItem->_parentPointer];
	}

	function &getChild($treeItem) {
		$pointer = $treeItem->children[0];
		//return $this->itemList[$treeItem->_childPointer];
		return $this->itemList[$pointer];
	}

	function &getSibling($treeItem) {
		return $this->itemList[$treeItem->_siblingPointer];
	}

	function &findItem($modelNode, $db = false) {
			$stack = array();

if ($db) {
	echo "searching for ..." .$modelNode->row. ', '. $modelNode->col.' under ('.$modelNode->_parentPointer->row.")\n<br>";
//cgn::debug($modelNode); #exit();
}
			while ( !$modelNode->root ) {
				$stack[] = $modelNode;
				$modelNode = $modelNode->_parentPointer;
			}
//			$stack[] = $modelNode;
			$stack = array_reverse($stack);
			//start at the beginning
			$item = $this->rootItem;
			$lastItem = $item;
if ($db) {
//cgn::debug($this->rootNode); #exit();
cgn::debug($stack); #exit();
//cgn::debug($this->itemList[4]); #exit();
//cgn::debug($item); #exit();
}
			//cgn::debug($stack);exit();
			foreach ($stack as $stackNode) {
				$item = $this->itemList[$lastItem->children[$stackNode->row]];
				$lastItem = $item;
if ($db) {
cgn::debug($item->children); #exit();
}

			}
if ($db) {
cgn::debug($item); #exit();
}
			return $item;
	}

	/**
	 * count all the items AT THIS node's current level.
	 *  Go up, then down and sideways to count
	 */
	function getRowCount($modelNode = null) { 
		if ($modelNode == null) {
			//$modelNode = new Cgn_Mvc_ModelNode(0,0,$this->root());
			$modelNode = $this->root();
		} else {
			$parent = $modelNode->_parentPointer;
			$modelNode = $parent;
		}
		$count = 0;
//		echo "get row count \n<br/>\n";
		$child = $this->findItem($modelNode);
		$count = count($child->children);
//		echo "got $count \n<br/>\n";
//		echo "DONE: get row count \n<br/>\n";
		/*
		if ($child) {
			$count++;
		}
		while ($sib = $child->getSibling($child)) {
			$count++;
		}
		 */
//		echo "Row Count ";Cgn::debug($modelNode); //exit();
//		echo "Row Count ";Cgn::debug($child); //exit();
		return $count;
	}


	function hasChildren($modelNode) {
		$item = $this->findItem($modelNode);
		return ( count($item->children) > 0 );
	}

	function getValue($modelNode, $dataRole = null) { 
		if($modelNode->_parentPointer == 0) {
			//$item = $this->itemList[$this->_rootNode->children[$modelNode->row]];
			$item = $this->findItem($modelNode, false);
		} else {
			$item = $this->findItem($modelNode, false);
			/*
			$stack = array();
			while ( $modelNode->_parentPointer ) {
				$stack[] = $modelNode;
				$modelNode = $modelNode->_parentPointer;
			}
			$stack[] = $modelNode;
			rsort($stack);
//			foreach ($stack as $stackNode) {
			$stackNode = $stack[0];
				$item = $this->itemList[$stackNode->children[$stackNode->row]];
//			}
//			cgn::debug($item);
			*/

		}
/*
cgn::debug($item);
cgn::debug($modelNode);
exit();
 */


		/*
		Cgn::debug($item);exit();
		Cgn::debug($modelNode);exit();
		 */
		return $item->data;
		if (is_null($modelNode->col)) {
			return $this->data[$modelNode->row];
		} else {
			return $this->data[$modelNode->row][$modelNode->col];
		}
	}

	/** 
	 * return the level of indentation for this index
	 */
	function getIndent($modelNode, $dataRole = null) { 
		$indent = 0;
		while ( !$modelNode->root ) {
			$indent++;
			$modelNode = $modelNode->_parentPointer;
		}
		return $indent;
	}

}


class Cgn_Mvc_TreeView extends Cgn_Mvc_AbstractItemView {

	var $tagName = 'table';
	var $type    = 'table';
	var $classes = array('grid_1');
	var $attribs = array('border'=>2);

	function Cgn_Mvc_TreeView(&$model) {
		$this->setModel($model);
	}


	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}


	function toHtml($id='') {
		$html  = '';
//		$html .= '<ul style="list_1" id="'.$id.'">'."\n";
		$html .= $this->printOpen();
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

		//do table headers
		$headers = $this->_model->headers;
		if (count($headers) > 0) { 
			$html .= '<tr style="grid_tr_h">'."\n";
			for($y=0; $y < $cols; $y++) {
				if ($x%2==0) {$style = 'grid_td_1';} else {$style = 'grid_td_1';}
				$datum = $this->_model->getHeaderAt(null,$y);
				$html .= '<th style="'.$style.'">'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		$topIndex = new Cgn_Mvc_ModelNode(0,0,$this->_model->root());
		for($x=0; $x < $rows; $x++) {
			$lastIndex = new Cgn_Mvc_ModelNode($x,0,$this->_model->root());
			$html .= '<tr style="grid_tr_1">'."\n";
			if ($x%2==0) {$style = 'grid_td_1';} else {$style = 'grid_td_2';}
//cgn::debug($lastIndex);
			$datum = $this->_model->getValue($lastIndex);
			$html .= '<td style="'.$style.'">'.$datum.'</td>'."\n";
			$html .= '</tr>'."\n";
			//*
			if ($this->_model->hasChildren($lastIndex)) {
				$subIndex = new Cgn_Mvc_ModelNode(0,0,$lastIndex);
				$subRows = $this->_model->getRowCount($subIndex);
				for($dx=0; $dx < $subRows; $dx++) {
				$subIndex = new Cgn_Mvc_ModelNode($dx,0,$lastIndex);
				$datum = $this->_model->getValue($subIndex);
				$padding = str_repeat('&nbsp;&nbsp;', $this->_model->getIndent($subIndex));
				$html .= '<tr style="grid_tr_1">'."\n";
				$html .= '<td style="'.$style.'">'.$padding.$datum.'</td>'."\n";
				$html .= '</tr>'."\n";
				}
			}
			// */

			/*
			for($y=0; $y < $cols; $y++) {
				if ($x%2==0) {$style = 'grid_td_1';} else {$style = 'grid_td_2';}
				$datum = $this->_model->getValue($lastIndex);
				$html .= '<td style="'.$style.'">'.$datum.'</td>'."\n";
			}
			 */
//		$lastIndex = new Cgn_Mvc_ModelNode($x,$y,$lastIndex);
		}
//		$html .= '</ul>';
		$html .= $this->printClose();
		return $html;
	}
}


class Cgn_Mvc_TreeView2 extends Cgn_Mvc_AbstractItemView {

	var $tagName = 'div';
	var $type    = 'list';
	var $classes = array('box');
	var $htmlId  = 'menu01';

	function Cgn_Mvc_TreeView2(&$model) {
		$this->setModel($model);
	}


	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}


	function toHtml($id='') {
		if ($id) { $this->htmlId = $id; }
		$html  = '';
		$html .= $this->printOpen();
		$html .= '<ul id="'.$this->htmlId.'">'."\n";
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

		$topIndex = new Cgn_Mvc_ModelNode(0,0,$this->_model->root());
		for($x=0; $x < $rows; $x++) {
			$lastIndex = new Cgn_Mvc_ModelNode($x,0,$this->_model->root());
			if ($x%2==0) {$style = 'grid_td_1';} else {$style = 'grid_td_2';}
//cgn::debug($lastIndex);
			$datum = $this->_model->getValue($lastIndex);
			$html .= '<li class="'.$style.'">'.$datum."\n";
			//*
			if ($this->_model->hasChildren($lastIndex)) {
				$html .= '<ol>'."\n";
				$subIndex = new Cgn_Mvc_ModelNode(0,0,$lastIndex);
				$subRows = $this->_model->getRowCount($subIndex);
				for($dx=0; $dx < $subRows; $dx++) {
				$subIndex = new Cgn_Mvc_ModelNode($dx,0,$lastIndex);
				$datum = $this->_model->getValue($subIndex);
//				$padding = str_repeat('&nbsp;&nbsp;', $this->_model->getIndent($subIndex));
				$html .= '<li class="'.$style.'">'.$padding.$datum.'</li>'."\n";
				}
				$html .= '</ol>'."\n";
			}
			$html .= "\n".'</li>'."\n";
			// */
		}
		$html .= '</ul>'."\n";
		$html .= $this->printClose();
		return $html;
	}
}

?>
