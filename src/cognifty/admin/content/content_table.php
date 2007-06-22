<?php

class Cgn_Mvc_ContentTableView extends Cgn_Mvc_TableView {

	var $classes = array('grid_adm');
	var $attribs = array('width'=>'100%','border'=>0,'cellspacing'=>'0');
	var $style = array('border'=>'1px dashed silver');

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
			$html .= '<tr class="grid_adm_tr_h">'."\n";
			for($y=0; $y < $cols; $y++) {
//				if ($x%2==0) {$class = 'grid_td_1';} else {$class = 'grid_td_1';}
				$datum = $this->_model->getHeaderAt(null,$y);
				$html .= '<th class="grid_adm_th_1">'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		for($x=0; $x < $rows; $x++) {
			$html .= '<tr class="grid_adm_tr_1">'."\n";
			for($y=0; $y < $cols; $y++) {
				if ($x%2==0) {$class = 'grid_adm_td_1';} else {$class = 'grid_td_2';}
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
?>
