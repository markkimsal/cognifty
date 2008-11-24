
<?php
// Cgn::debug($t['toolbar']);
 echo $t['toolbar']->toHtml();

?>

<script type="text/javascript">
	$(function() {
		$('#container-1 ol').tabs(1);
	});
</script>
<style type="text/css">
	#container-1 div {margin-left:87px;}
	#container-1 div div {margin-left:1em;}
	#container-1 div div div {margin-left:0;}
</style>




<div id="container-1">
	<ol style="float:left">
       	<li><a href="#fragment-1"><span>About</span></a></li>
		<li><a href="#fragment-2" onclick="updatePreview();return false;"><span>Preview</span></a></li>
		<li><a href="#fragment-3"><span>Tags</span></a></li>
		<li><a href="#fragment-atr"><span>Attributes</span></a></li>
<?php
if ( !$t['dataList']->isEmpty() ) {
?>
                <li><a href="#fragment-4"><span>Related</span></a></li>
<?php
}
?>

<?php
if (isset($t['sectionForm'])) {
?>
                <li><a href="#fragment-5"><span>Sections</span></a></li>
<?php
}
?>



            </ol>
            <div id="fragment-1">

				<div style="margin-bottom:2em;">
				<h2><?= $t['content']->title;?></h2>
				(Link text:  <?= $t['content']->link_text;?>)
				<br/>

				Type:  <?= $t['content']->type;?> &mdash; <?= $t['content']->sub_type;?>
				<br/>
				Version:  <?= $t['content']->version;?>
				</div>

<?php
if ( isset($t['halfPreview'])) { ?>
	Content:
	<div style="width:600px;background-color:#eee;">
		<?= $t['halfPreview']; ?>
	</div>
<?php
}
?>

            </div>
            <div id="fragment-2">


<?php
if ($t['showPreview'] ) {
?>

<!--
<input type="button" class="formbutton" value="Show Preview" onclick="updatePreview();return false;"/>
<br/>
-->
<iframe id="prevframe" name="prevframe" height="600" width="700" style="margin-left:1em;display:none;" src=""></iframe>
<?php
}
?>




            </div>
            <div id="fragment-3">
Tags not implemented yet.
            </div>

            <div id="fragment-atr">
				<? if (!isset($t['attributeForm'])) { ?>
				No attributes for this content type.
				<? } else { ?>
				<?= $t['attributeForm']->toHtml();?>
				<? } ?>
            </div>

<?php
if ( !$t['dataList']->isEmpty() ) {
?>
            <div id="fragment-4">
				<h3>Related to...</h3>
				<?= $t['dataList']->toHtml();?>
            </div>
<?php
}
?>

<?php
if ( isset($t['sectionForm']) ) {
?>
            <div id="fragment-5">
				<?= $t['sectionForm']->toHtml();?>
            </div>
<?php
}
?>
        </div>
		<br style="clear:both;"/>




<?php
if (is_object($t['useForm'])) {
	echo $t['useForm']->toHtml();
}
?>

<script language="javascript">
function updatePreview() {
	document.getElementById('prevframe').style.display = 'block';
	document.getElementById('prevframe').src='<?= cgn_adminurl('content','preview','show',array('id'=>$t['content']->cgn_content_id));?>';

}

</script>
