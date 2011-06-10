<?php
echo $t['headerPage'];
?>
<table width="100%">
<tr><td>
<a name="top"></a>
<?php
echo $t['form']->toHtml();
?>
</td>
</tr>
</table>


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
function insertImage(link, id, caption) {
<?php
if ($t['mime'] == 'wiki' || $t['mime'] == 'text/wiki') {
?>
	insertTags('{{img:' + link, '?cgnid='+id+'}}','');
<?php
} else {
?>
	text = '<img id="cgn_id|'+id+'|" src="<?= cgn_appurl('main','content','image');?>' + link+'"/>';

	insertTags('<div class="wiki_image"><div class="wiki_tinner">', '</a></div><div class="wiki_tcap">'+caption+'</div></div>', text);
<?php
}
?>
scrollUp();
}

function insertImageThm(link, id, caption) {
<?php
if ($t['mime'] == 'wiki' || $t['mime'] == 'text/wiki') {
?>
	insertTags('{{img-thm:' + link, '?cgnid='+id+'}}','');
<?php
} else {
?>
text = "\n"+'<img id="cgn_id|'+id+'|" src="<?= cgn_appurl('main','content','thumb');?>' + link+ '"/>'+"\n";
	insertTags('<div class="wiki_image"><div class="wiki_tinner"><a rel="lightbox" href="<?=cgn_appurl('main','content','image');?>'+link+ '" title="'+caption+'">','</a></div><div class="wiki_tcap">'+caption+'</div></div>', text);
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
	insertTags('[[file:' + link, ']]','');
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
