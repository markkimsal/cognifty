<?php

/**
 * override default layout and add Javascript tool bar buttons above the text area
 */
class Cgn_Form_WikiLayout extends Cgn_Form_Layout {

	var $mime = 'html';    //either html, or wiki (or text/wiki or text/html)

	function renderForm($form) {
		$html = '<div style="padding:1px;background-color:#FFF;border:1px solid silver;width:'.$form->width.';">';
		$html .= '<div class="cgn_form" style="padding:5px;background-color:#EEE;">';

		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= '>';
		$html .= "\n";
		$html .= '<table border="0" cellspacing="3" cellpadding="3">';
		$textareaId = '';
		foreach ($form->elements as $e) {
			$html .= '<tr><td valign="top" width="10%">';
			$html .= $e->label.'</td><td valign="top">';
			if ($e->type == 'textarea') {
				$html .= '</td></tr><tr><td valign="top" colspan="2">';
				$html .= $this->getTagsForMime();
				$html .= '<br/>'."\n";
				$html .= '<textarea class="forminput" name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" WRAP="OFF">'.htmlentities($e->value,ENT_QUOTES, 'UTF-8').'</textarea>';
				$textareaId = $e->name;
			} else if ($e->type == 'radio') {
				foreach ($e->choices as $cid => $c) {
				$html .= '<input type="radio" name="'.$e->name.'" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.sprintf('%02d',$cid+1).'"/>'.$c.'<br/> ';
				}
			} else if ($e->type == 'label') {
				$html .= $e->toHtml();
			} else {
				$html .= '<input class="forminput" type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>';
			}
			$html .= '</td></tr>';
		}
		$html .= '</table>';
		$html .= '<input class="formbutton" type="button"  value="+wider+" onclick="document.getElementById(\''.$textareaId.'\').cols +=5;"/>';
		$html .= '<input class="formbutton" type="button"  value="-thinner-" onclick="document.getElementById(\''.$textareaId.'\').cols -=5;"/>';
		$html .= '<div style="width:90%;text-align:right;">';
		$html .= "\n";
		$html .= '<input class="submitbutton" type="submit" name="'.$form->name.'_submit" value="Submit"/>';
		$html .= '</div>';

		$html .= "\n";
		foreach ($form->hidden as $e) {
			$html .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'"/>';
		}
		$html .= '</form>';
		$html .= "\n";

		$html .= '</div>';
		$html .= '</div>';
		$html .= "\n";



		return $html;
	}


	function getTagsForMime() {
		$html = '';

		if ($this->mime == 'wiki' || $this->mime == 'text/wiki') {
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'**\',\'**\',\'bold\');return false" value="Bold"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'//\',\'//\',\'italic\');return false" value="Italic"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'__\',\'__\',\'underline\');return false" value="Underline"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'{{img:\',\'}}\',\'Web Image Title\');return false" value="Web Image"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'{{pagebreak:\',\'}}\',\'Title of new page\');return false" value="Page Break"/> ';
		} else {
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'&lt;b&gt;\',\'&lt;/b&gt;\',\'bold\');return false" value="Bold"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'&lt;i&gt;\',\'&lt;/i&gt;\',\'italic\');return false" value="Italic"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'&lt;u&gt;\',\'&lt;/u&gt;\',\'underline\');return false" value="Underline"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'&lt;img title=&quot;image&quot; alt=&quot;image&quot; src=&quot;http://'.Cgn_Template::baseurl().'\',\'&quot;&gt;\',\'Web Image Title\');return false" value="Web Image"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'<br\',\'/>\',\'\');return false" value="Line Break"/> ';
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'{{pagebreak:\',\'}}\',\'Title of new page\');return false" value="Page Break"/> ';

		}
		return $html;
	}

}
?>
