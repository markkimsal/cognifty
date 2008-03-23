<style type="text/css">
.content_wrapper .entry .entry_date {
text-align:center;
float:left;
padding:.2em .5em .2em .5em;
margin:.5em .5em .5em 0;
background-color:#EEE;
}
</style>

<div class="content_wrapper">
<?

$entry = $t['entryObj'];
	$published = explode(' ',date('F d Y',$entry->posted_on));
	$published['month'] = $published[0];
	$published['date'] = $published[1];
	$published['year'] = $published[2];
?>
<div class="entry">
	<div class="entry_date">
		<span style="font-size:90%;">
		<?=$published['month'];?>
		</span>
		<br/>
		<span style="font-size:150%;">
		<?=$published['date'];?>
		</span>
	</div>
<?

	echo '<div style="float:left;"><h3 style="margin:.4em 0 .4em 0;">'.$entry->title.'</h3>';
	if ($entry->caption) {
			echo '<h5 style="margin:0 0 0 1em;">'.$entry->caption.'</h5>';
	}
	echo '</div>';
	echo '<br style="clear:both;"/><p>'.$entry->content.'</p>';

?>

	<br/>
	<br/>
</div>
</div>

<h4>Comments</h4>
<?

	foreach ($t['commentList'] as $commentObj) {
		echo '<div style="background-color:#EEF;">';
		if (strlen($commentObj->user_name) ) {
			echo '<b>'.$commentObj->user_name.'</b>';
		} else {
			echo "<b>Anonymous</b>";
		}
		echo '</div>';
?>
		<?= nl2br(trim($commentObj->content)); ?>
		<br/>
		<span class="content_cmt_sep" style="margin-bottom:1em;">&nbsp;</span>
<? 
		if ($commentObj->spam_rating > 0) {
			echo 'spam rating = '.$commentObj->spam_rating;
			echo "<p>&nbsp;</p>\n";
		}
	}
?>

<!-- trackback hiding
    <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
             xmlns:dc="http://purl.org/dc/elements/1.1/"
             xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
    <rdf:Description
	rdf:about="<?= $t['permalink'];?>"
        dc:identifier="<?= $t['permalink'];?>"
		dc:title="<?= htmlspecialchars($entry->title);?>"
        trackback:ping="<?= cgn_appurl('blog','entry','trackback', array('id'=>$entry->cgn_blog_entry_publish_id));?>" />
    </rdf:RDF>
-->
<p>&nbsp;</p>
<h3>Add a comment</h3>
	<form action="<?= cgn_appurl('blog','entry','comment', array('id'=>$entry->cgn_blog_entry_publish_id));?>" method="POST">
		Comment: <br/>
		<textarea style="width:100%" rows="10" cols="70" name="comment"></textarea>
		<br/>
		<input type="submit" name="submit_bt" value="Submit"/>
	</form>

