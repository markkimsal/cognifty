<?php

class Cgn_Mvc_TableModel extends Cgn_Mvc_DefaultItemModel {
	var $data    = array();
	var $columns = array();
	var $headers = array();
	var $assoc   = FALSE;
	var $arkeys  = NULL;

	var $totalCount;

	function Cgn_Mvc_TableModel() {
		$x = new Cgn_Mvc_ModelNode();
		$this->setRootNode($x); 
	}

	function getValue($modelNode, $dataRole = NULL) { 
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

	/**
	 * Get a count of this model's $data array
	 *
	 * @return int  count of $this->data
	 */
	function getRowCount() { 
		return count($this->data);
	}

	/**
	 * Used if this model represents a limited view
	 * of a larger selection of data.
	 *
	 * @param int $c  count of all records, even if they're not in this model
	 */
	function setUnlimitedRowCount($c) { 
		$this->totalCount = $c;
	}

	/**
	 * Used if this model represents a limited view
	 * of a larger selection of data.
	 *
	 * @return int  value of $this->totalCount
	 */
	function getUnlimitedRowCount() { 
		if (!empty($this->totalCount)) {
			return $this->totalCount;
		} else {
			return count($this->data);
		}
	}

	/**
	 * Returns the size of the first array in the this model
	 */
	function getColumnCount() { 
		if (count($this->columns) ) {
			return count($this->columns);
		}
		//work with zero based arrays or with assoc arrays
		$first = @each($this->data);
		@reset($this->data);
		return intval(count($first['value']));
	}

	/**
	 * Sets the name of the array keys of the data list
	 */
	function setColKeys($keyArray) {
		$this->columns = $keyArray;
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

	/**
	 * Returns a string with any HTML required before the open table tag
	 *
	 * @return String   any HTML needed before the start of the table
	 */
	function printBefore() {
		return '';
	}

	/**
	 * Returns a string with any HTML required after the close table tag
	 *
	 * @return String   any HTML needed after the end of the table
	 */
	function printAfter() {
		return '';
	}

	/**
	 * Returns a string with an HTML table THEAD
	 */
	function printHeaders() {
		$html = '';
		$headers = $this->_model->headers;
		if ($headCount = count($headers)) { 
			$html .= '<thead><tr class="'.$this->cssPrefix.'_tr_h">'."\n";
			for($y=0; $y < $headCount; $y++) {
				$datum = $this->_model->getHeaderAt(NULL, $y);
				$colWidth = $this->getColWidth($y);
				$colAlign = $this->getColAlign($y);
				$html .= '<th class="'.$this->cssPrefix.'_th" '.$colWidth.' '.$colAlign.'>'.$datum.'</th>'."\n";
			}
			$html .= '</tr></thead>'."\n";
		}
		return $html;
	}

	function toHtml($id='') {
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

		$html  = '';
		$html .= $this->printBefore();
		$html .= $this->printOpen();

		//do table headers
		$html .= $this->printHeaders();


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
						$datum = $this->colRndr[$y]->getRenderedValue($datum, $x, $y, $this->_model);
				}
				$html .= '<td class="'.$cellclass.'" '.$colAlign.'>'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		if ($rows < 1) {
			$headCount = count($this->_model->headers);
			$html .= '<tr class="'.$rowclass.'"><td colspan="'.$headCount.'" class='.$cellclass.'><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		$html .= $this->printAfter();
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
 * A table view that can handle paginated data
 */
class Cgn_Mvc_TableView_Paged extends Cgn_Mvc_TableView {

	var $tagName = 'table';
	var $type    = 'table';
	var $classes = array('grid_1');
	var $attribs = array('border'=>2);
	var $colRndr = array();
	var $cssPrefix = 'grid_1';

	var $rpp     = 100;
	var $curPage = 1;

	function Cgn_Mvc_TableView_Paged(&$model, $curPage = 1) {
		$this->setModel($model);

		$this->setRpp($model->getRowCount());
		$this->curPage = $curPage;
	}

	/**
	 * Returns a string with HTML to show navigation links for a paginated table
	 *
	 * @return String   any HTML needed before the start of the table
	 */
	public function printBefore() {
		$html  = '<div class="data_table_pager">';
		$html .= '<form method="GET" action="'.$this->getBaseUrl().'" style="display:inline;">';
		$html .= '<a href="'.$this->getPrevUrl().'">';
		$html .= '<img height="12" src="'.cgn_url().'media/icons/default/arrow_left_24.png" border="0"/>';
		$html .= '</a> ';
		$html .= 'Page <input type="text" name="p" size="1" value="'.$this->curPage.'" style="width:1.5em;height:1em;"/> of  '. $this->getPageCount(). ' ';
		$html .= '<a href="'.$this->getNextUrl().'">';
		$html .= '<img height="12" src="'.cgn_url().'media/icons/default/arrow_right_24.png" border="0"/>';
		$html .= '</a>  | ';
		$html .= 'Showing '. $this->_model->getRowCount().' records | Total records found: '.sprintf($this->_model->getUnlimitedRowCount());
		$html .= '</form></div>';
		return $html;
	}

	/**
	 * Return the total number of pages displayable for this model.
	 */
	public function getPageCount() {
		$totalRows = $this->_model->getUnlimitedRowCount();

		$rpp = $this->getRpp();
		$totalPages = sprintf('%d', $totalRows/$rpp);
		//leftovers?
		if ($totalRows%$rpp) {
			$totalPages+=1;
		}
		return $totalPages;
	}

	/**
	 * Results, rows, records, per page
	 */
	public function getRpp() {
		return $this->rpp;
	}

	/**
	 * Results, rows, records, per page
	 */
	public function setRpp($rpp) {
		$this->rpp = $rpp;
	}

	public function setNextUrl($url) {
		$this->urlNext = $url;
	}

	public function setPrevUrl($url) {
		$this->urlPrev = $url;
	}

	public function setBaseUrl($url) {
		$this->urlBase = $url;
	}

	public function getNextUrl() {
		return sprintf(urldecode($this->urlNext), ($this->curPage+1));
	}

	public function getPrevUrl() {
		if ($this->curPage-1)
		return sprintf(urldecode($this->urlPrev), ($this->curPage-1));
	}

	public function getBaseUrl() {
		return $this->urlBase;
	}
}

/**
 * Class to render values a certain way for an entire column.
 *
 * @abstact
 */
class Cgn_Mvc_Table_ColRenderer {
	function getRenderedValue($val, $x, $y, $model=NULL) {
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
		if ($val == 0) {
			return '&nbsp;';
		}
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

class Cgn_Mvc_Table_CheckboxRenderer extends Cgn_Mvc_Table_ColRenderer {

	var $inputCssClass = 'data_table_check';

	/**
	 * Create a new Checkbox Column Renderer, optionally specify 
	 * the CSS class for all checkbox input elements.
	 */
	function Cgn_Mvc_Table_CheckboxRenderer($cssClass=NULL) {
		if ($cssClass != NULL)
			$this->inputCssClass = $cssClass;
	}

	function getRenderedValue($val, $x, $y) {
		return '<input class="'.$this->inputCssClass.'" type="checkbox" value="'.sprintf('%d',$val).'">';
	}
}

class Cgn_Mvc_AdminTableView extends Cgn_Mvc_TableView {

	var $classes = array('grid_adm');
	var $attribs = array('width'=>'100%','border'=>0,'cellspacing'=>'1');
	var $style = array('border'=>'0px solid gray', 'background-color'=>'silver');
	var $colAttrs = array();
	var $cssPrefix = 'grid_adm';

	function Cgn_Mvc_TableView(&$model) {
		$this->setModel($model);
	}

	function setModel(&$m) {
		//fire data changed event
		$this->_model =& $m;
	}



	/**
	 * Returns a string with an HTML table THEAD
	 *
	 * @return String   HTML representing a THEAD element
	 */
	function printHeaders() {
		$html = '';
		$headers = $this->_model->headers;
		if ($headCount = count($headers)) { 
			$html .= '<thead><tr class="'.$this->cssPrefix.'_tr_h">'."\n";
			for($y=0; $y < $headCount; $y++) {
				$datum = $this->_model->getHeaderAt(NULL, $y);
				$colWidth = $this->getColWidth($y);
				$colAlign = $this->getColAlign($y);
				$html .= '<th class="'.$this->cssPrefix.'_th_1" '.$colWidth.' '.$colAlign.'>'.$datum.'</th>'."\n";
			}
			$html .= '</tr></thead>'."\n";
		}
		return $html;
	}

	function toHtml($id='') {
		$html  = '';
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();


		if ($rows < 1) {
			$this->style['border'] = '1px dashed silver';
			$this->style['background-color'] = 'transparent';
		}

		$html .= $this->printBefore();
		$html .= $this->printOpen();

		//do table headers
		$html .= $this->printHeaders();

		for($x=0; $x < $rows; $x++) {
			if ($x%2==0) {$class = 'o';} else {$class = 'e';}
			if ($x==0) {$class = '1';}
			$html .= '<tr class="grid_adm_tr_'.$class.'">'."\n";
			for($y=0; $y < $cols; $y++) {
				//x,y data
				$datum = $this->_model->getValueAt($x,$y);
				if (isset ($this->colRndr[$y]) &&
					$this->colRndr[$y] instanceof Cgn_Mvc_Table_ColRenderer) {
						$datum = $this->colRndr[$y]->getRenderedValue($datum, $x, $y, $this->_model);
				}
				$html .= '<td class="grid_adm_td_'.$class.'">'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		if ($rows < 1) {
			$headCount = count($this->_model->headers);
			$html .= '<tr class="grid_adm_tr_1"><td colspan="'.$headCount.'"><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}
}

class Cgn_Mvc_TableView_Admin_Paged extends Cgn_Mvc_TableView_Paged {

	var $classes = array('grid_adm');
	var $attribs = array('width'=>'100%','border'=>0,'cellspacing'=>'1');
	var $style = array('border'=>'0px solid gray', 'background-color'=>'silver');
	var $colAttrs = array();
	var $cssPrefix = 'grid_adm';

	/**
	 * Returns a string with an HTML table THEAD
	 *
	 * @return String   HTML representing a THEAD element
	 */
	function printHeaders() {
		$html = '';
		$headers = $this->_model->headers;
		if ($headCount = count($headers)) { 
			$html .= '<thead><tr class="'.$this->cssPrefix.'_tr_h">'."\n";
			for($y=0; $y < $headCount; $y++) {
				$datum = $this->_model->getHeaderAt(NULL, $y);
				$colWidth = $this->getColWidth($y);
				$colAlign = $this->getColAlign($y);
				$html .= '<th class="'.$this->cssPrefix.'_th_1" '.$colWidth.' '.$colAlign.'>'.$datum.'</th>'."\n";
			}
			$html .= '</tr></thead>'."\n";
		}
		return $html;
	}

	function toHtml($id='') {
		$html  = '';
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();


		if ($rows < 1) {
			$this->style['border'] = '1px dashed silver';
			$this->style['background-color'] = 'transparent';
		}

		$html .= $this->printBefore();
		$html .= $this->printOpen();

		//do table headers
		$html .= $this->printHeaders();

		for($x=0; $x < $rows; $x++) {
			if ($x%2==0) {$class = 'o';} else {$class = 'e';}
			if ($x==0) {$class = '1';}
			$html .= '<tr class="grid_adm_tr_'.$class.'">'."\n";
			for($y=0; $y < $cols; $y++) {
				//x,y data
				$datum = $this->_model->getValueAt($x,$y);
				if (isset ($this->colRndr[$y]) &&
					$this->colRndr[$y] instanceof Cgn_Mvc_Table_ColRenderer) {
						$datum = $this->colRndr[$y]->getRenderedValue($datum, $x, $y, $this->_model);
				}
				$html .= '<td class="grid_adm_td_'.$class.'">'.$datum.'</td>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		if ($rows < 1) {
			$headCount = count($this->_model->headers);
			$html .= '<tr class="grid_adm_tr_1"><td colspan="'.$headCount.'"><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}

}
?>
