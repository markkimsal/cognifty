<?php

class Cgn_Mvc_TableModel extends Cgn_Mvc_DefaultItemModel {
	var $data = array();
	var $columns = array();
	var $headers = array();

	function Cgn_Mvc_TableModel() {
		$x = new Cgn_Mvc_ModelNode();
		$this->setRootNode($x); 
//		$this->addColumn();
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
		$html .= $this->printOpen();
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

		//do table headers
		$headers = $this->_model->headers;
		if (count($headers) > 0) { 
			$html .= '<tr class="grid_tr_h">'."\n";
			for($y=0; $y < $cols; $y++) {
				$datum = $this->_model->getHeaderAt(null,$y);
				$html .= '<th class="grid_th_1">'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		for($x=0; $x < $rows; $x++) {
			$html .= '<tr class="grid_tr_1">'."\n";
			for($y=0; $y < $cols; $y++) {
				if ($x%2==0) {$class = 'grid_td_1';} else {$class = 'grid_td_2';}
				$datum = $this->_model->getValueAt($x,$y);
				$html .= '<td class="'.$class.'">'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		if ($rows < 1) {
			$html .= '<tr class="grid_tr_1"><td><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}
}



class Cgn_Mvc_AdminTableView extends Cgn_Mvc_TableView {

	var $classes = array('grid_adm');
	var $attribs = array('width'=>'650','border'=>0,'cellspacing'=>'1');
	var $style = array('border'=>'0px solid gray', 'background-color'=>'silver');

	function Cgn_Mvc_TableView(&$model) {
		$this->setModel($model);
	}


	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}


	function toHtml($id='') {
		$html  = '';
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();


		if ($rows < 1) {
			$this->style['border'] = '1px dashed silver';
			$this->style['background-color'] = 'none';
		}

		$html .= $this->printOpen();

		//do table headers
		$headers = $this->_model->headers;
		if (count($headers) > 0) { 
			$html .= '<tr class="grid_adm_tr_h">'."\n";
			for($y=0; $y < $cols; $y++) {
//				if ($x%2==0) {$class = 'grid_td_1';} else {$class = 'grid_td_1';}
				$datum = $this->_model->getHeaderAt(null,$y);
				$html .= '<th class="grid_adm_th_1">'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		for($x=0; $x < $rows; $x++) {
			if ($x%2==0) {$class = 'o';} else {$class = 'e';}
			if ($x==0) {$class = '1';}
			$html .= '<tr class="grid_adm_tr_'.$class.'">'."\n";
			for($y=0; $y < $cols; $y++) {
				$datum = $this->_model->getValueAt($x,$y);
				$html .= '<td class="grid_adm_td_'.$class.'">'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		if ($rows < 1) {
			$html .= '<tr class="grid_adm_tr_1"><td><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}
}
?>
