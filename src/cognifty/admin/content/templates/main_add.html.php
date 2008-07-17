Version: <?= $t['version'];?>

<!--
<br/>
<fieldset>
<legend>Insert Other Content</legend>
<a onclick="insertTags('{{img:','}}','Image Title');return false" href="#">Web image</a>
<br/>
<a onclick="insertTags('[[',']]','Article Title');return false" href="#">link to article</a>
<br/>
<a onclick="insertTags('<p style=&quot;page-break-before: always&quot;></p>','','');return false" href="#">Page break</a>
<br/>
<a onclick="insertTags('{{pagebreak:','}}','Title of new page');return false" href="#">Page break</a>
</fieldset>
-->


<fieldset>
<legend>Link Other Content</legend>
<a href="<?=cgn_adminurl('content','preview','images');?>" target="browseframe">Browse Web Images</a>&nbsp;|&nbsp;
<a href="<?=cgn_adminurl('content','preview','articles');?>" target="browseframe">Browse Articles</a>&nbsp;|&nbsp;
<a href="<?=cgn_adminurl('content','preview','files');?>" target="browseframe">Browse Files</a>
<iframe name="browseframe" height="100" width="600" src=""></iframe>
</fieldset>


<fieldset>
<legend>Preview this content</legend>
<input type="button" value="Update Preview" onclick="updatePreview();return false;"/>
<br/>
<iframe name="prevframe" height="600" width="700" src=""></iframe>
</fieldset>

<script language="javascript">
function updatePreview() {
	document.getElementById('content_01').target='prevframe';
	document.getElementById('content_01').action='<?=cgn_adminurl('content','preview','show',array('m'=>$t['mime']));?>';
	document.getElementById('content_01').submit();
	document.getElementById('content_01').target='_self';
	document.getElementById('content_01').action='<?=cgn_adminurl('content','main','save');?>';

}
</script>
