<style type="text/css">
.content_wrapper .entry .entry-date {
text-align:center;
float:left;
padding-right:1.5em;
}
</style>
<div class="content_wrapper">
<?

foreach ($t['entries'] as $key=>$entry) {
	$published = explode(' ',date('F d Y',$entry->posted_on));
	$published['month'] = $published[0];
	$published['date'] = $published[1];
	$published['year'] = $published[2];
?>
<div class="entry">
	<div class="entry-date">
		<span style="font-size:90%;">
		<?=$published['month'];?>
		</span>
		<br/>
		<span style="font-size:150%;">
		<?=$published['date'];?>
		</span>
	</div>
<?
	echo '<div style="float:left;"><h2 style="margin:.5em;">'.$entry->title.'</h2>';
	if ($entry->caption) {
			echo '<h5 style="margin:0;">'.$entry->caption.'</h5>';
	}
	echo '</div>';
	echo '<p style="clear:both;">'.substr(strip_tags($entry->content),0,1000).'</p>';
?>
	<div class="links">
	<a href="<?=cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id));?>">Read More</a>
	</div>
</div>
<?php
}
?>
</div>
