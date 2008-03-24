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

foreach ($t['entries'] as $key=>$entry) {
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
	echo '<div style="float:left;"><h3 style="margin:.4em 0 .4em 0;">
		<a href="'.cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id)).$entry->link_text.'">
		'.$entry->title.'</a></h3>';
	if ($entry->caption) {
			echo '<h5 style="margin:0 0 0 1em;">'.$entry->caption.'</h5>';
	}
	echo '</div>';
	echo '<p style="clear:both;">'.substr(strip_tags($entry->content),0,1000).'</p>';
?>
	<div class="links">
	<a href="<?=cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id)).$entry->link_text;?>">Read More</a>
	</div>
</div>
<?php
}
?>
</div>
