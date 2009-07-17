<?php echo $t['header'];?>
<?php echo $t['mytoolbar']->toHtml();?>
<?php echo $t['tableView']->toHtml();?>

<br/>
<?php
if (isset($t['readmeLabel'])) {
	echo $t['readmeLabel'];
?>
	<textarea rows="30" cols="75" style="width:650px;" name="nothing"><?php echo $t['readmeContents'];?></textarea>
<?php
}
