<?

$entry = $t['entryObj'];
	$published = explode(' ',date('F d Y',$entry->posted_on));
	$published['month'] = $published[0];
	$published['date'] = $published[1];
	$published['year'] = $published[2];
?>
	<div style="text-align:center;float:left;margin-top:0.5em;padding-right:1.5em;">
		<span style="font-size:90%;">
		<?=$published['month'];?>
		</span>
		<br/>
		<span style="font-size:150%;">
		<?=$published['date'];?>
		</span>
	</div>
<?
	echo '<h3>'.$entry->title.'</h3>';
	echo "<hr/>\n";
	echo $entry->content;
	echo "<br/>\n";
	echo "<br/>\n";

?>
<h4>Comments</h4>
<?

	foreach ($t['commentList'] as $commentObj) {
		if (strlen($commentObj->user_name) ) {
			echo '<b>'.$commentObj->user_name.'</b>';
		} else {
			echo "<b>Anonymous</b>";
		}
?>
	<br/>
		<?= nl2br(trim($commentObj->content)); ?>
		<p>&nbsp;</p>
<?
	}
?>

<p>&nbsp;</p>
<h3>Add a comment</h3>
	<form action="<?= cgn_appurl('blog','entry','comment', array('id'=>$entry->cgn_blog_entry_publish_id));?>" method="POST">
		Comment: <br/>
		<textarea style="width:100%" rows="10" cols="70" name="comment"></textarea>
		<br/>
		<input type="submit" name="submit_bt" value="Submit"/>
	</form>