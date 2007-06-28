<h3><?= $t['content']->title;?></h3>
<p>
<?php
echo cgn_adminlink('Edit','content','edit','', array('id'=>$t['content']->cgn_content_id));
echo "&nbsp;|&nbsp;";
echo cgn_adminlink('Publish','content','publish','',array('id'=>$t['content']->cgn_content_id));?>
</p>


<?php
echo $t['sectionForm']->toHtml();
?>

<?php
if (is_object($t['useForm'])) {
	echo $t['useForm']->toHtml();
}
?>


<p>&nbsp;</p>

Preview:
<br/>
<iframe name="prevframe" height="600" width="700" src="<?= cgn_adminurl('content','preview','show',array('id'=>$t['content']->cgn_content_id));?>"></iframe>
