<h3><?= $t['content']->title;?></h3>
<p>
<?php
echo $t['toolbar']->toHtml();
?>
</p>

Type:  <?= $t['content']->type;?>
<br/>
Used as:  <?= $t['content']->sub_type;?>
<br/>
Version:  <?= $t['content']->version;?>
<br/>
Link text:  <?= $t['content']->link_text;?>

<?php
if (is_object($t['useForm'])) {
	echo $t['useForm']->toHtml();
}
?>


<?php
if ($t['showPreview'] ) {
?>
<p>&nbsp;</p>

Preview:
<br/>
<iframe name="prevframe" height="600" width="700" src="<?= cgn_adminurl('content','preview','show',array('id'=>$t['content']->cgn_content_id));?>"></iframe>
<?php
}
?>
