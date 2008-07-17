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
    var $colRndr = array();
	var $cssPrefix = 'grid_1';

	function Cgn_Mvc_TableView(&$model) {
		$this->setModel($model);
	}

	function setColRenderer($colIdx, &$obj) {
		$this->colRndr[$colIdx] = $obj;
	}

	function setColWidth($colIdx, $width) {
		$this->colAttrs[$colIdx]['width'] = $width;
	}

	function setColAlign($colIdx, $align) {
		$this->colAttrs[$colIdx]['align'] = $align;
	}

	function getColAlign($colIdx) {
		if (isset($this->colAttrs[$colIdx]) &&
		isset($this->colAttrs[$colIdx]['align'])) {
			return ' align="'.$this->colAttrs[$colIdx]['align'].'" ';
		} else {
			return '';
		}
	}

	function getColWidth($colIdx) {
		if (isset($this->colAttrs[$colIdx]) &&
		isset($this->colAttrs[$colIdx]['width'])) {
			return ' width="'.$this->colAttrs[$colIdx]['width'].'" ';
		} else {
			return '';
		}
	}

	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}

	function isEmpty() {
		return $this->_model->isEmpty();
	}

	function toHtml($id='') {
		$html  = '';
		$html .= $this->printOpen();
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

		//do table headers
		$headers = $this->_model->headers;
		if ($headCount = count($headers)) { 
			$html .= '<tr class="'.$this->cssPrefix.'_tr_h">'."\n";
			for($y=0; $y < $headCount; $y++) {
				$datum = $this->_model->getHeaderAt(null,$y);
				$colWidth = $this->getColWidth($y);
				$colAlign = $this->getColAlign($y);
				$html .= '<th class="'.$this->cssPrefix.'_th" '.$colWidth.' '.$colAlign.'>'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		for($x=0; $x < $rows; $x++) {
			//row-cell issues
			if ($x%2==0) {
				$rowclass = $this->cssPrefix.'_tr even_row';
				$cellclass = $this->cssPrefix.'_td even_cell';
			} else {
				$rowclass = $this->cssPrefix.'_tr odd_row';
				$cellclass = $this->cssPrefix.'_td odd_cell';
			}
			$html .= '<tr class="'.$rowclass.'">'."\n";
			for($y=0; $y < $cols; $y++) {
				//column issues
				$colAlign = $this->getColAlign($y);

				//x,y data
				$datum = $this->_model->getValueAt($x,$y);
                if (isset ($this->colRndr[$y]) &&
                    $this->colRndr[$y] instanceof Cgn_Mvc_Table_ColRenderer) {
                        $datum = $this->colRndr[$y]->getRenderedValue($datum, $x, $y);
                 }
				$html .= '<td class="'.$cellclass.'" '.$colAlign.'>'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		if ($rows < 1) {
			$html .= '<tr class="'.$rowclass.'"><td colspan="'.$headCount.'" class='.$cellclass.'><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}

    function __destruct() {
        foreach ($this->colRndr as $idx => $obj) {
            unset($obj);
            unset($this->colRndr[$idx]);
        }
    }
}

/**
 * Class to render values a certain way for an entire column.
 *
 * @abstact
 */
class Cgn_Mvc_Table_ColRenderer {
    function getRenderedValue($val, $x, $y) {
        return $val;
    }
}

/**
 * Class to render values a certain way for an entire column.
 *
 * @abstact
 */
class Cgn_Mvc_Table_DateRenderer extends Cgn_Mvc_Table_ColRenderer {

	var $format;

	function Cgn_Mvc_Table_DateRenderer($fmt) {
		$this->format = $fmt;
	}

    function getRenderedValue($val, $x, $y) {
		return date($this->format,$val);
    }
}

class Cgn_Mvc_Table_MoneyRenderer extends Cgn_Mvc_Table_ColRenderer {

	var $format;
	var $locale;

	function Cgn_Mvc_Table_MoneyRenderer($locale=NULL) {
		$this->format = '%.2f';
		$this->locale = $locale;
	}

    function getRenderedValue($val, $x, $y) {
		return '$'.sprintf($this->format,$val);
    }
}

class Cgn_Mvc_Table_YesNoRenderer extends Cgn_Mvc_Table_ColRenderer {

	var $format;
	var $locale;

	function Cgn_Mvc_Table_YesNoRenderer() {
	}

    function getRenderedValue($val, $x, $y) {
		if (!$val) { return 'No'; }
		if ($val) { return 'Yes'; }
    }
}


class Cgn_Mvc_AdminTableView extends Cgn_Mvc_TableView {

	var $classes = array('grid_adm');
	var $attribs = array('width'=>'100%','border'=>0,'cellspacing'=>'1');
	var $style = array('border'=>'0px solid gray', 'background-color'=>'silver');
    var $colAttrs = array();

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
			$this->style['background-color'] = 'transparent';
		}

		$html .= $this->printOpen();

		//do table headers
		$headers = $this->_model->headers;
		if ($headCount = count($headers)) { 
			$html .= '<tr class="grid_adm_tr_h">'."\n";
			for($y=0; $y < $headCount; $y++) {
//				if ($x%2==0) {$class = 'grid_td_1';} else {$class = 'grid_td_1';}
				$datum = $this->_model->getHeaderAt(null,$y);
				$colWidth = $this->getColWidth($y);
				$html .= '<th class="grid_adm_th_1" '.$colWidth.'>'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}

		for($x=0; $x < $rows; $x++) {
			if ($x%2==0) {$class = 'o';} else {$class = 'e';}
			if ($x==0) {$class = '1';}
			$html .= '<tr class="grid_adm_tr_'.$class.'">'."\n";
			for($y=0; $y < $cols; $y++) {
				//x,y data
				$datum = $this->_model->getValueAt($x,$y);
                if (isset ($this->colRndr[$y]) &&
                    $this->colRndr[$y] instanceof Cgn_Mvc_Table_ColRenderer) {
                        $datum = $this->colRndr[$y]->getRenderedValue($datum, $x, $y);
                 }
				$html .= '<td class="grid_adm_td_'.$class.'">'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		if ($rows < 1) {
			$html .= '<tr class="grid_adm_tr_1"><td colspan="'.$headCount.'"><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}
}
?>
