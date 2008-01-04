<div class="content_wrapper">
<?

foreach ($t['entries'] as $key=>$entry) {
	$published = explode(' ',date('F d Y',$entry->posted_on));
	$published['month'] = $published[0];
	$published['date'] = $published[1];
	$published['year'] = $published[2];
?>
<div class="entry">
	<div style="text-align:center;float:left;padding-right:1.5em;">
		<span style="font-size:90%;">
		<?=$published['month'];?>
		</span>
		<br/>
		<span style="font-size:150%;">
		<?=$published['date'];?>
		</span>
	</div>
<?
	echo '<h2>'.$entry->title.'</h2>';
	echo $entry->content;
?>
	<div class="links">
	<a href="<?=cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id));?>">Read More</a>
	</div>
</div>
<?php
}
?>
</div>
