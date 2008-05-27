<?php

class Cgn_Mvc_TreeItem  {

	var $children = array();
	var $id = 0;
	var $root = false;
	var $_expanded = false;
	var $_parentPointer = NULL;
	var $data           = NULL;

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

	var $itemList = array();
	var $columns  = array();

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
			if ($treeItem->_expanded) {
				$this->expandBranch($parentItem);
			}
	//		$parentItem->appendChild($treeItem);
			$parentItem->children[] = $treeItem->getId();
			$treeItem->_parentPointer = $parentItem->getId();
		}
	}

	function expandBranch($treeItem) {
		$treeItem->_expanded = true;
		$grandParent = $this->getParent($treeItem);
		while (!$grandParent->root) {
			$grandParent->_expanded = true;
			$grandParent = $this->getParent($grandParent);
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
		if($treeItem->_parentPointer == '') {
			return $this->_rootNode;
		}
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
			if ($modelNode->root !== true && !is_object($modelNode->_parentPointer) ) {
				return false;

			}
			$c =0;
			while ( !$modelNode->root ) {
				$stack[] = $modelNode;
				if (++$c > 100 ) {die('tree is too big, over 100 levels.'); }
				$modelNode = $modelNode->_parentPointer;
			}
//			$stack[] = $modelNode;
			$stack = array_reverse($stack);
			//start at the beginning
			$item = $this->rootItem;
			$lastItem = $item;
if ($db) {
//cgn::debug($this->rootNode); #exit();
//cgn::debug($stack); #exit();
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

	function getExpand($modelNode, $dataRole = null) { 
		$item = $this->findItem($modelNode, false);
		return $item->_expanded === true;
	}

	function getValue($modelNode, $dataRole = null) { 
		if( !is_object($modelNode->_parentPointer)) {
			//$item = $this->itemList[$this->_rootNode->children[$modelNode->row]];
			$item = $this->findItem($modelNode, false);
		} else {
			$item = $this->findItem($modelNode, false);
		}
/*
cgn::debug($item);
cgn::debug($modelNode);
exit();
 */
		if (is_array($item->data)) {
			if (is_null($modelNode->col)) {
				return $item->data;
			} else {
				return $item->data[$modelNode->col];
			}
		} else {
			return $item->data;
		}
	}


	function getColumnCount() { 
		if (count($this->columns) ) {
			return count($this->columns);
		} else {
			return 1;
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

/**
 * Display as a table.  Support multi-column trees
 */
class Cgn_Mvc_TreeView extends Cgn_Mvc_DefaultItemView {

	var $tagName = 'table';
	var $type    = 'table';
	var $classes = array('grid_adm');
	var $attribs = array('width'=>'650','border'=>0,'cellspacing'=>'1');
	var $style = array('border'=>'.5px solid gray', 'background-color'=>'silver');



	function Cgn_Mvc_TreeView(&$model) {
		$this->setId();
		$this->setModel($model);
	}


	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}


	function toHtml($id='') {
		$html  = '';
		$html .= $this->printOpen();
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

		//do table headers
		$headers = $this->_model->headers;
		if (count($headers) > 0) { 
			$html .= '<tr class="grid_adm_tr_h">'."\n";
			for($y=0; $y < $cols; $y++) {
				$datum = $this->_model->getHeaderAt(null,$y);
				$html .= '<th class="grid_adm_th_1">'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		$x = 0;
		$dx = 0;
		$row = 0;
		for($x=0; $x < $rows; $x++) {
			if (($row++)%2==0) {$class = 'o';} else {$class = 'e';}
			if ($x==0) {$class = '1';}

			$lastIndex = new Cgn_Mvc_ModelNode($x,0,$this->_model->root());
			$html .= '<tr class="grid_adm_tr_'.$class.'">'."\n";
			$style = 'grid_adm_td_'.$class;

			for($y=0; $y < $cols; $y++) {
				$thisIndex = new Cgn_Mvc_ModelNode($x,$y,$this->_model->root());
				$datum = $this->_model->getValue($thisIndex);
				$colRend = $this->getColRenderer($y);
				if ($colRend !== null) {
					$datum = $colRend->renderData($datum);
				}
				$html .= '<td class="'.$style.'">'.$datum.'</td>'."\n";
			}

			$html .= '</tr>'."\n";
			//*
			if ($this->_model->hasChildren($lastIndex)) {
				$subIndex = new Cgn_Mvc_ModelNode(0,0,$lastIndex);
				$subRows = $this->_model->getRowCount($subIndex);
				for($dx=0; $dx < $subRows; $dx++) {

				if (($row++)%2==0) {$class = 'o';} else {$class = 'e';}

				$html .= '<tr class="grid_adm_tr_'.$class.'">'."\n";
				for($y=0; $y < $cols; $y++) {

					$style = 'grid_adm_td_'.$class;
					$subIndex = new Cgn_Mvc_ModelNode($dx,$y,$lastIndex);
					$datum = $this->_model->getValue($subIndex);
					$colRend = $this->getColRenderer($y);
					if ($colRend !== null) {
						$datum = $colRend->renderData($datum);
					}

					//only move in the first column
					if ($y == 0) {
						$datum = 
						str_repeat('&nbsp;&nbsp;', $this->_model->getIndent($subIndex))
						.$datum;
					}
					$html .= '<td class="'.$style.'">'.$datum.'</td>'."\n";
				}

				$html .= '</tr>'."\n";
				}
			}
			// */
		}
		$html .= $this->printClose();
		return $html;
	}
}


class Cgn_Mvc_TreeView2 extends Cgn_Mvc_AbstractItemView {

	var $tagName = 'div';
	var $type    = 'menu';
	var $classes = array('box', 'mvc_tree2');
	var $listId  = '';

	function Cgn_Mvc_TreeView2(&$model) {
		static $num=0;
		$this->setId();
		$num++;
		$this->setModel($model);
		$this->listId = 'tree'.sprintf('%03d',$num);
	}

	function setListId($htmlId) {
		$this->listId = $htmlId;
	}


	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}


	function toHtml($id='') {
		if ($id) { $this->id = $id; }
		$html  = '';
		$html .= $this->printOpen();
		$html .= '<ul id="'.$this->listId.'" class="'. implode(' ',$this->classes).'">'."\n";
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

//		$topIndex = new Cgn_Mvc_ModelNode(0,0,$this->_model->root());
		for($x=0; $x < $rows; $x++) {
			$lastIndex = new Cgn_Mvc_ModelNode($x,0,$this->_model->root());
			if ($x%2==0) {$class = ' grid_td_1';} else {$class = ' grid_td_2';}
//cgn::debug($lastIndex);
			$datum    = $this->_model->getValue($lastIndex);
			$expanded = $this->_model->getExpand($lastIndex);

			$html .= '<li class="'.$class.'" style="display:block;">'.$datum."\n";
			//*
			if ($this->_model->hasChildren($lastIndex)) {
				//if the parent element is "open", set this OL to display=block
				if ($expanded) {
					$olStyle = ' style="display:block;"';
				} else {
					$olStyle = '';
				}

				$html .= '<ol'.$olStyle.'>'."\n";
				$subIndex = new Cgn_Mvc_ModelNode(0,0,$lastIndex);
				$subRows = $this->_model->getRowCount($subIndex);
				for($dx=0; $dx < $subRows; $dx++) {
				$subIndex = new Cgn_Mvc_ModelNode($dx,0,$lastIndex);
				$datum = $this->_model->getValue($subIndex);
//				$padding = str_repeat('&nbsp;&nbsp;', $this->_model->getIndent($subIndex));
//				$html .= '<li class="'.$class.'">'.$padding.$datum.'</li>'."\n";
				$html .= '<li class="'.$class.'">'.$datum.'</li>'."\n";
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



/**
 * Display as a table.  Support multi-column trees
 */
class Cgn_Mvc_YuiTreeView extends Cgn_Mvc_DefaultItemView {

	var $tagName = 'table';
	var $type    = 'table';
	var $classes = array('grid_adm');
	var $attribs = array('width'=>'650','border'=>0,'cellspacing'=>'1');
	var $style = array('border'=>'.5px solid gray', 'background-color'=>'silver');



	function Cgn_Mvc_YuiTreeView(&$model) {
		$this->setId();
		$this->setModel($model);
	}


	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}


	function toHtml($id='') {
		$html  = '';

		$html .= '
<div id="treeDiv1">loading dynamic tree...</div>
<!-- Dependency source files -->  
<script src = "'.cgn_url().'media/js/yui/build/yahoo/yahoo-min.js" ></script> 
<script src = "'.cgn_url().'media/js/yui/build/event/event-min.js" ></script> 

<!-- TreeView source file -->  
<script src = "'.cgn_url().'media/js/yui/build/treeview/treeview-min.js" ></script> 

<script type="text/javascript" language="javascript">
var tree; 
function treeInit() { 
   tree = new YAHOO.widget.TreeView("treeDiv1"); 
   var root = tree.getRoot(); 

';
		$x = 0;
		$dx = 0;
		$row = 0;

//		$rows = $this->_model->getRowCount();
//		$cols = $this->_model->getColumnCount();
		$lastIndex = new Cgn_Mvc_ModelNode($x,0,$this->_model->root());

		/*
	$debugIndex = new Cgn_Mvc_ModelNode(1,0,$lastIndex);
	$item = $this->_model->findItem($debugIndex, false);
	cgn::debug($item);
	exit();
		 */


		$this->showNode($lastIndex, $html);

		/*
		for($x=0; $x < $rows; $x++) {
			$lastIndex = new Cgn_Mvc_ModelNode($x,0,$this->_model->root());

			$datum = $this->_model->getValue($lastIndex);
			$href = $this->_model->getValue(
			new Cgn_Mvc_ModelNode($x,1,$this->_model->root())
				);
			$html .= 'var tmpNode = new YAHOO.widget.TextNode("'.htmlentities($datum).'", hpNode, false);'."\n";
			$html .= 'tmpNode.href = "'.$href.'";'."\n";

			//*
			$parentName = 'tmpNode';
			$indexStack = array();
			if ($this->_model->hasChildren($lastIndex)) {
				do {
				$subIndex = new Cgn_Mvc_ModelNode(0,0,$lastIndex);
				$subRows = $this->_model->getRowCount($subIndex);
				for($dx=0; $dx < $subRows; $dx++) {

				if (($row++)%2==0) {$class = 'o';} else {$class = 'e';}

				$subIndex2 = new Cgn_Mvc_ModelNode($dx,0,$lastIndex);
				$datum = $this->_model->getValue($subIndex2);
				$href = $this->_model->getValue(
					new Cgn_Mvc_ModelNode($dx,1,$lastIndex)
					);

				$html .= 'var subNode = new YAHOO.widget.TextNode("'.htmlentities($datum).'", '.$parentName.', false);'."\n";
				$html .= 'subNode.href = "'.$href.'";'."\n";
				$lastIndex = $subIndex2;
				$parentName = 'subNode';
				}
				}  while ($this->_model->hasChildren($lastIndex));
			}
			// */
			 /*
		}
		 */

		$html .= '

   tree.draw(); 
			/*
		tree.subscribe("labelClick", function(node) { 
			node.labelStyle = "icon-ppt"; 
			this.draw();
	   }); 
		 */
} 
treeInit();
</script>
	';


		return $html;
	}

	function showNode($nodeIndex, &$html, $parent='root') {
		$thisRows = $this->_model->getRowCount($nodeIndex);

		for($dx=0; $dx < $thisRows; $dx++) {
			$thisIndex = new Cgn_Mvc_ModelNode($dx,0,$nodeIndex->_parentPointer);
			$datum = $this->_model->getValue(
				new Cgn_Mvc_ModelNode($dx,0,$nodeIndex->_parentPointer)
			);

			$q = new Cgn_Mvc_ModelNode($dx,1,$nodeIndex->_parentPointer);
			$thisItem = $this->_model->findItem($nodeIndex);
			$id = $thisItem->id;
			$href = $this->_model->getValue(
				$q
				);
			$nodeName = 'tmpNode'.$dx.'_'.$id;
			$html .= 'var tmpNode'.$dx.'_'.$id.' = new YAHOO.widget.TextNode("'.htmlentities($datum).'", '.$parent.', false);'."\n";
			$html .= 'tmpNode'.$dx.'_'.$id.'.href = "'.$href.'";'."\n";

			if ($thisItem->_expanded) {
				$html .= 'tmpNode'.$dx.'_'.$id.'.expand();'."\n";
			}


			if ($this->_model->hasChildren($thisIndex)) {
//				$i = $this->_model->findItem($thisIndex);
				$subIndex = new Cgn_Mvc_ModelNode(0,0,$thisIndex);

				$this->showNode($subIndex, $html, $nodeName);
			}
		}
	}
}
?>
