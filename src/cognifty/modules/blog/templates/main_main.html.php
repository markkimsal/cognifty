<?

foreach ($t['entries'] as $key=>$entry) {
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
	echo "<a href=\"".cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id))."\">Read More</a>\n";
	echo "<br/>\n";
	echo "<br/>\n";
}
?>
