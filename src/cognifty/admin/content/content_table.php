<?php

class Cgn_Mvc_ContentTableView extends Cgn_Mvc_TableView {

	var $classes = array('grid_adm');
	var $attribs = array('width'=>'100%','border'=>0,'cellspacing'=>'1');
	var $style = array('border'=>'1px solid black', 'background-color'=>'silver');

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
			$html .= '<tr class="grid_tr_1"><td><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}
}
?>
