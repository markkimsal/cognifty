<h3><?= $t['content']->title;?></h3>
<br/>
Type:  <?= $t['content']->type;?>
<br/>
Used as:  <?= $t['content']->sub_type;?>
<br/>
Version:  <?= $t['content']->version;?>

<p>
<?= cgn_adminlink('Edit this content.','content','edit','', array('id'=>$t['content']->cgn_content_id));?>
</p>

<p>
<?= cgn_adminlink('Publish this content.','content','publish','',array('id'=>$t['content']->cgn_content_id));?>
</p>

<?php
if (is_object($t['useForm'])) {
	echo $t['useForm']->toHtml();
}
?>


<p>&nbsp;</p>

Preview:
<br/>
<iframe name="prevframe" height="600" width="700" src="<?= cgn_adminurl('content','preview','show',array('id'=>$t['content']->cgn_content_id));?>"></iframe>
