<h3><?= $t['content']->title;?></h3>
<p>
<?php
echo $t['toolbar']->toHtml();
?>
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
