<?php



class Cgn_AbstractItemModel {

	var $_rootNode;

	/**
	 * Returns the most specific superclass for all the cell values in the column.
	 */
	function getColumnType($columnIndex) { }

	/**
	 * Returns the number of columns in the model.
	 */
	function getColumnCount() { }

	/**
	 * Returns a default name for the column using spreadsheet conventions: A, B, C, 
	 */
	function getColumnName($columnIndex) { }

	/**
	 * Returns the number of rows in the model.
	 */
	function getRowCount() { }

	/**
	 * Returns the value for the given index.
	 */
	function getValue($modelNode, $dataRole=null) { }

	/**
	 * Returns the value for the cell at columnIndex and rowIndex.
	 */
	function getValueAt($rowIndex, $columnIndex) { }

	/**
	 * Returns the value for the cell at columnIndex and rowIndex.
	 */
	function getValueAs($rowIndex, $columnIndex, $dataRole=null) { }

	/**
	 * Returns the value for the given index.
	 */
	function getHeaderValue($numeral, $dataRole=null) { }

	/**
	 * Returns the value for the cell at columnIndex and rowIndex.
	 */
	function getRowHeaderValue($numeral, $dataRole=null) { }

	/**
	 * Returns the value for the cell at columnIndex and rowIndex.
	 */
	function getHeaderValueAt($numeral, $orientation='col', $dataRole=null) { }

	/**
	 * Returns true if the cell at rowIndex and columnIndex is editable.
	 */
	function isCellEditable($rowIndex, $columnIndex) { }

	/**
	 * Sets the value in the cell at columnIndex and rowIndex to aValue.
	 */
	function setValueAt($val, $rowIndex, $columnIndex) { }

	/**
	 * Returns true if there is more data available for the parent, otherwise false
	 */
	function canFetchMore($parentNode) { }

	/**
	 * setRootNode
	 */
	function setRootNode($modelNode) { }
}


class Cgn_DefaultItemModel extends Cgn_AbstractItemModel {

	/**
	 * setRootNode
	 */
	function setRootNode(&$modelNode) { 
		//fire event updateLayout
		$modelNode->root = true;
		$this->_rootNode =& $modelNode;
	}


	function getValueAt($rowIndex, $columnIndex) { 
		return $this->getValue( new Cgn_ModelNode($rowIndex,$columnIndex) );
	}


	function getValueAs($rowIndex, $columnIndex, $dataRole=null) { 
		return $this->getValue( new Cgn_ModelNode($rowIndex,$columnIndex) , null, $dataRole);
	}
}


/**
 * Represents either 2D or hierarchical tree index of data
 * in a model.
 */
class Cgn_ModelNode {

	var $row;
	var $col;
	var $row;
	var $valid = false;
	var $_parentPointer;
	var $_siblingPointer;
	var $_childPointer;
	var $root = false;

	function Cgn_ModelNode($row=null, $col=null, $parent=null, $role=null) {
		$this->row = $row;
		$this->col = $col;
		$this->_parentPointer = $parent;
		$this->role = $role;
	}
}


class Cgn_ListModel extends Cgn_DefaultItemModel {

	var $dataList = array();
	var $columns = array();

	function Cgn_ListModel() {
		$x = new Cgn_ModelNode();
		$this->setRootNode($x); 
		$this->addColumn();
	}

	function addColumn($title='') { 
		$this->columns[] = $title;
	}

	function getValue($modelNode, $dataRole = null) { 
		if (is_null($modelNode->col)) {
			return $this->data[$modelNode->row];
		} else {
			return $this->data[$modelNode->row][$modelNode->col];
		}
	}

	function getRowCount() { 
		return count($this->data);
	}

	function getColumnCount() { 
		if (count($this->columns) ) {
			return count($this->columns);
		}
//		return count($this->dataList[0]);
	}
}


class Cgn_TreeModel extends Cgn_AbstractItemModel {

}


class Cgn_TableModel extends Cgn_AbstractItemModel {

}


class Cgn_AbstractItemView  extends Cgn_HtmlWidget {
	var $altRowColors = true;
	var $iconSize = 32;
	var $_model;

	var $tagName = 'ul';
	var $type    = 'list';
	var $classes = array('list_1');
}

class Cgn_ListView extends Cgn_AbstractItemView {

	function Cgn_ListView(&$model) {
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
		for($x=0; $x < $rows; $x++) {
			$datum = $this->_model->getValueAt($x,null);
			$html .= '<li style="list_li_1">'.$datum.'</li>'."\n";
		}
		$html .= '</ul>';
		$html .= $this->printClose();
		return $html;
	}
}

?>
