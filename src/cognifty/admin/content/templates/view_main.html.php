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

<p>
<h3>Related to...</h3>
<?php
foreach ($t['relObjs'] as $obj) {
	echo "<b>".$obj->type."</b>: ".$obj->title. "<br/>\n";
}
?>
</p>


<?php
if ($t['showPreview'] ) {
?>
<p>&nbsp;</p>


<input type="button" class="formbutton" value="Show Preview" onclick="updatePreview();return false;"/>
<br/>
<iframe id="prevframe" name="prevframe" height="600" width="700" style="display:none;" src=""></iframe>
<?php
}
?>


<script language="javascript">
function updatePreview() {
	document.getElementById('prevframe').style.display = 'block';
	document.getElementById('prevframe').src='<?= cgn_adminurl('content','preview','show',array('id'=>$t['content']->cgn_content_id));?>';

}

</script>
