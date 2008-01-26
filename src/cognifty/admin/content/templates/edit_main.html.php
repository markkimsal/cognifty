<table width="100%">
<tr><td>
<a name="top"></a>
<?php
echo $t['form']->toHtml();
?>
</td>
</tr>
</table>
<!-- 
<fieldset>
<legend>Tags...</legend>
<input type="text" size="15"/>
<input type="submit" value="add"/>
<p style="font-size:80%;">Enter new tags, separated by commas</p>
<p style="overflow-y:auto;overflow-x:hidden;height:5em;">
<input type="checkbox"/> Tag 1
<br/>
<input type="checkbox"/> Tag 2
<br/>
<input type="checkbox"/> Tag 3
<br/>
<input type="checkbox"/> Tag 4
<br/>
<input type="checkbox"/> Tag 5
<br/>
<input type="checkbox"/> Tag 6
<br/>
<input type="checkbox"/> Tag 7
</p>
</fieldset>
 -->

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
<a href="<?=cgn_adminurl('content','preview','browsePages');?>" onclick="document.getElementById('browseframe').style.display = 'block'"; target="browseframe">Browse Web Pages</a>&nbsp;|&nbsp;
<a href="<?=cgn_adminurl('content','preview','browseImages');?>" onclick="document.getElementById('browseframe').style.display = 'block'"; target="browseframe">Browse Web Images</a>&nbsp;|&nbsp;
<a href="<?=cgn_adminurl('content','preview','browseArticles');?>" onclick="document.getElementById('browseframe').style.display = 'block'"; target="browseframe">Browse Articles</a>&nbsp;|&nbsp;
<a href="<?=cgn_adminurl('content','preview','browseFiles');?>" onclick="document.getElementById('browseframe').style.display = 'block'"; target="browseframe">Browse Files</a>
<br/>
<iframe style="display:none;" id="browseframe" name="browseframe" height="140" width="600" src=""></iframe>
</fieldset>


<fieldset>
<legend>Preview this content</legend>
<input type="button" class="formbutton" value="Update Preview" onclick="updatePreview();return false;"/>
<br/>
<iframe name="prevframe" id="prevframe" height="600" width="700" src=""></iframe>
<br/>
<input class="formbutton" type="button"  value="+wider+" onclick="document.getElementById('prevframe').width = parseInt(document.getElementById('prevframe').width) + 15;"/>
<input class="formbutton" type="button"  value="-thinner-" onclick="document.getElementById('prevframe').width = parseInt(document.getElementById('prevframe').width) - 15;"/>

</fieldset>

<script language="javascript">
function updatePreview() {
	document.getElementById('content_01').target='prevframe';
	document.getElementById('content_01').action='<?=cgn_adminurl('content','preview','show',array('m'=>urlencode($t['mime'])));?>';
	document.getElementById('content_01').submit();
	document.getElementById('content_01').target='_self';
	document.getElementById('content_01').action='<?=cgn_adminurl('content','edit','save');?>';

}
/**
 * wrapper for either HTML or Wiki links to call insertTags
 */
function insertImage(link, id) {
<?php
if ($t['mime'] == 'wiki' || $t['mime'] == 'text/wiki') {
?>
	insertTags('{{img:' + link, '?cgnid='+id+'}}','');
<?php
} else {
?>
	insertTags('<img id="cgn_id|'+id+'|" src="<?= cgn_appurl('main','content','image');?>' + link, '">','');
<?php
}
?>
scrollUp();
}

/**
 * wrapper for either HTML or Wiki links to call insertTags
 */
function insertPage(link, text, id) {
<?php
if ($t['mime'] == 'wiki' || $t['mime'] == 'text/wiki') {
?>
	insertTags('[[web:' + link, '?cgnid='+id+'|'+text+']]','');
<?php
} else {
?>
	insertTags('<a id="cgn_id|'+id+'|" href="<?= cgn_appurl('main','page');?>' + link + '">','</a>',text);
<?php
}
?>
scrollUp();
}

/**
 * wrapper for either HTML or Wiki links to call insertTags
 */
function insertArticle(link, text, id) {
<?php
if ($t['mime'] == 'wiki' || $t['mime'] == 'text/wiki') {
?>
	insertTags('[[' + link, ']]','');
<?php
} else {
?>
	insertTags('<a id="cgn_id|'+id+'|" href="<?= cgn_appurl('main','content');?>' + link + '">','</a>',text);
<?php
}
?>
scrollUp();
}

/**
 * wrapper for either HTML or Wiki links to call insertTags
 */
function insertFile(link, text, id) {
<?php
if ($t['mime'] == 'wiki' || $t['mime'] == 'text/wiki') {
?>
	insertTags('[[' + link, ']]','');
<?php
} else {
?>
	insertTags('<a id="cgn_id|'+id+'|" href="<?= cgn_appurl('main','asset');?>' + link + '">','</a>',text);
<?php
}
?>
scrollUp();
}

function scrollUp() {
 if (document.body && document.body.scrollTop) 
    document.body.scrollTop = 100; 
 if (document.documentElement && document.documentElement.scrollTop) 
    document.documentElement.scrollTop = 100; 
 if (window.pageYOffset) 
    window.pageYOffset = 100; 
}

</script>
