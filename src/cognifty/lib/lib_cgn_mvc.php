<?php



class Cgn_Mvc_AbstractItemModel {

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
	 * Returns the model node for the cell at columnIndex and rowIndex.
	 */
	function nodeAt($rowIndex, $columnIndex) { }

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


class Cgn_Mvc_DefaultItemModel extends Cgn_Mvc_AbstractItemModel {

	/**
	 * setRootNode
	 */
	function setRootNode(&$modelNode) { 
		//fire event updateLayout
		$modelNode->root = true;
		$this->_rootNode =& $modelNode;
	}


	function getValueAt($rowIndex, $columnIndex) { 
		return $this->getValue( new Cgn_Mvc_ModelNode($rowIndex,$columnIndex) );
	}


	function getValueAs($rowIndex, $columnIndex, $dataRole=null) { 
		return $this->getValue( new Cgn_Mvc_ModelNode($rowIndex,$columnIndex) , null, $dataRole);
	}


	function nodeAt($rowIndex, $columnIndex, $parentNode=0) { 
		if ($parentNode == 0) {
			return new Cgn_Mvc_ModelNode($rowIndex,$columnIndex, null);
		} else {
			return new Cgn_Mvc_ModelNode($rowIndex,$columnIndex, $parentNode);
		}
	}


	function getHeaderAt($rowIndex, $columnIndex) { 
		return $this->getHeader( new Cgn_Mvc_ModelNode($rowIndex,$columnIndex) );
	}


	function getHeaderAs($rowIndex, $columnIndex, $dataRole=null) { 
		return $this->getHeader( new Cgn_Mvc_ModelNode($rowIndex,$columnIndex) , null, $dataRole);
	}

	function addColumn($title='') { 
		$this->columns[] = $title;
	}
}


/**
 * Represents either 2D or hierarchical tree index of data
 * in a model.
 */
class Cgn_Mvc_ModelNode {

	var $row;
	var $col;
	var $valid = false;
	var $_parentPointer;
	var $_siblingPointer;
	var $_childPointer;
	var $root = false;
	var $id = 0;

	function Cgn_Mvc_ModelNode($row=null, $col=null, $parent=null, $role=null) {
		$this->row = $row;
		$this->col = $col;
		$this->_parentPointer = $parent;
		$this->role = $role;
	}

	/**
	 * Only used for keeping track of nested relationships in the tree model 
	 * for now.
	 */
	function getId() {
		return $this->id;
	}
}


class Cgn_Mvc_ListModel extends Cgn_Mvc_DefaultItemModel {

	var $data = array();
	var $columns = array();

	function Cgn_Mvc_ListModel() {
		$x = new Cgn_Mvc_ModelNode();
		$this->setRootNode($x); 
		$this->addColumn();
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
	}
}


class Cgn_Mvc_TableModel extends Cgn_Mvc_DefaultItemModel {
	var $data = array();
	var $columns = array();
	var $headers = array();

	function Cgn_Mvc_TableModel() {
		$x = new Cgn_Mvc_ModelNode();
		$this->setRootNode($x); 
//		$this->addColumn();
	}

	function getHeader($modelNode, $dataRole = null) { 
//		if (count($this->headers) < 1) { return null; }

		if (is_null($modelNode->col)) {
			return $this->headers[$modelNode->row];
		} else {
			return $this->headers[$modelNode->col];
		}
	}

	function getValue($modelNode, $dataRole = null) { 
		if (is_null($modelNode->col)) {
			return $this->data[$modelNode->row];
		} else {
			if (count($this->columns) > 0) {
				$colName = $this->columns[$modelNode->col];
				return $this->data[$modelNode->row][$colName];
			} else {
				return $this->data[$modelNode->row][$modelNode->col];
			}
		}
	}

	function getRowCount() { 
		return count($this->data);
	}

	function getColumnCount() { 
//		if (count($this->columns) ) {
//			return count($this->columns);
//		}
		return intval(@count($this->data[0]));
	}
}


class Cgn_Mvc_AbstractItemView  extends Cgn_HtmlWidget {
	var $altRowColors = true;
	var $iconSize = 32;
	var $_model;

	var $tagName = 'ul';
	var $type    = 'list';
	var $classes = array('list_1');
}

class Cgn_Mvc_ListView extends Cgn_Mvc_AbstractItemView {

	function Cgn_Mvc_ListView(&$model) {
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



class Cgn_Mvc_TableView extends Cgn_Mvc_AbstractItemView {

	var $tagName = 'table';
	var $type    = 'table';
	var $classes = array('grid_1');
	var $attribs = array('border'=>2);

	function Cgn_Mvc_TableView(&$model) {
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
//				if ($x%2==0) {$style = 'grid_td_1';} else {$style = 'grid_td_1';}
				$datum = $this->_model->getHeaderAt(null,$y);
				$html .= '<th style="grid_th_1">'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		for($x=0; $x < $rows; $x++) {
			$html .= '<tr style="grid_tr_1">'."\n";
			for($y=0; $y < $cols; $y++) {
				if ($x%2==0) {$style = 'grid_td_1';} else {$style = 'grid_td_2';}
				$datum = $this->_model->getValueAt($x,$y);
				$html .= '<td style="'.$style.'">'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		$html .= $this->printClose();
		return $html;
	}
}


?>
