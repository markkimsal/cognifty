<?php

/**
 * override default layout and add Javascript tool bar buttons above the text area
 */
class Cgn_Form_WikiLayout extends Cgn_Form_Layout {

	var $mime = 'html';    //either html, or wiki (or text/wiki or text/html)

	function renderForm($form) {

		$html = '
        <script type="text/javascript">
            $(function() {
                $(\'#container-1 ol\').tabs(1);
			});
		</script>
<style type="text/css">
#container-1 div {margin-left:87px;}
#container-1 div div {margin-left:1em;}
#container-1 div div div {margin-left:0;}
</style>
';



		$html .= '<div style="padding:1px;background-color:#FFF;border:1px solid silver;width:'.$form->width.';">';
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
				$html .= '
    <div id="container-1">
        <ol style="float:left">
            <li><a href="#fragment-1"><span>Edit</span></a></li>
            <li><a href="#fragment-4"><span>Excerpt</span></a></li>
            <li><a href="#fragment-2" onclick="updatePreview();return false;"><span>Preview</span></a></li>
			</ol>
			<div id="fragment-1">
			';

				$html .= $this->getTagsForMime();
				$html .= '<br/>'."\n";
				$html .= '<textarea class="forminput" name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" style="font-size:120%;" WRAP="OFF">'.htmlentities($e->value,ENT_QUOTES, 'UTF-8').'</textarea>';
				$textareaId = $e->name;
				$html .= '<br/><input class="formbutton" type="button"  value="+wider+" onclick="document.getElementById(\''.$textareaId.'\').cols +=20;"/>';
				$html .= '<input class="formbutton" type="button"  value="-thinner-" onclick="document.getElementById(\''.$textareaId.'\').cols -=10;"/>';

				$html .= '</div>


					<div id="fragment-2">
					<iframe name="prevframe" id="prevframe" height="600" width="900" src=""></iframe>';
				$html .= '<br/><input class="formbutton" type="button"  value="+wider+" onclick="document.getElementById(\'prevframe\').width = parseInt(document.getElementById(\'prevframe\').width) + 50;"/>';
				$html .= '<input class="formbutton" type="button"  value="-thinner-" onclick="document.getElementById(\'prevframe\').width = parseInt(document.getElementById(\'prevframe\').width) - 25;"/>';

				$html .= '</div>';

		$html .= '
			<div id="fragment-4">
			';
				$html .= '<textarea class="forminput" name="'.$e->name.'_ex" id="'.$e->name.'_ex" rows="'.$e->rows.'" cols="'.$e->cols.'" style="font-size:120%;" WRAP="OFF">'.htmlentities($e->excerpt,ENT_QUOTES, 'UTF-8').'</textarea>';
				$textareaId = $e->name.'_ex';
				$html .= '<br/><input class="formbutton" type="button"  value="+wider+" onclick="document.getElementById(\''.$textareaId.'\').cols +=20;"/>';
				$html .= '<input class="formbutton" type="button"  value="-thinner-" onclick="document.getElementById(\''.$textareaId.'\').cols -=10;"/>';
				$html .= '</div>';


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
		$html .= '<div style="width:90%;text-align:right;">';
		$html .= "\n";
		$html .= '<input class="submitbutton" type="submit" name="'.$form->name.'_submit" value="Save"/>';
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

		//add hidden link panel
		$html .= '
					<div id="embedpanel" style="padding:12px;border:7px solid #777; background-color:#FFF;display:none;position:absolute;">
<H4>Link Other Content Items</H4>
<a href="'.cgn_adminurl('content','preview','browsePages').'" onclick="document.getElementById(\'browseframe\').style.display = \'block\'"; target="browseframe">Browse Web Pages</a>&nbsp;|&nbsp;
<a href="'.cgn_adminurl('content','preview','browseImages').'" onclick="document.getElementById(\'browseframe\').style.display = \'block\'"; target="browseframe">Browse Web Images</a>&nbsp;|&nbsp;
<a href="'.cgn_adminurl('content','preview','browseArticles').'" onclick="document.getElementById(\'browseframe\').style.display = \'block\'"; target="browseframe">Browse Articles</a>&nbsp;|&nbsp;
<a href="'.cgn_adminurl('content','preview','browseFiles').'" onclick="document.getElementById(\'browseframe\').style.display = \'block\'"; target="browseframe">Browse Files</a>&nbsp;|&nbsp;
<a href="#" onclick="closeEmbedPanel();return false;">Close [X]</a>
<br/>
<iframe style="display:block;" id="browseframe" name="browseframe" height="340" width="700" src=""></iframe>

<br/><input class="formbutton" type="button"  value="+wider+" onclick="document.getElementById(\'browseframe\').width = parseInt(document.getElementById(\'browseframe\').width) + 15;"/>
<input class="formbutton" type="button"  value="-thinner-" onclick="document.getElementById(\'browseframe\').width = parseInt(document.getElementById(\'browseframe\').width) - 15;"/>
</div><script language="Javascript">function showEmbedPanel() {var editpos = $("#fragment-1").position();$("#embedpanel").css( {top:editpos.top,left:"200px"});$("#embedpanel").show();} 
function closeEmbedPanel() { $("#embedpanel").hide(); $(\'#content\').focus();}</script>';  // END OF link panel ---- END OF </DIV>


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
			$html .= '<input class="formbutton" type="button" onclick="insertTags(\'{{pagebreak:\',\'}}\',\'Title of new page\');return false" value=" Break"/> ';

		}
		//both HTML and wiki use the same link button
		$html .= '<input class="formbutton" type="button" onclick="showEmbedPanel();return false" value="Link Other Items"/> ';
		return $html;
	}

}
?>
