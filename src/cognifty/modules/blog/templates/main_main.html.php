<style type="text/css">
.content_wrapper .entry .entry_date {
text-align:center;
float:left;
padding:.2em .5em .2em .5em;
margin:.5em .5em .5em 0;
background-color:#EEE;
}
.alignright {
	float:right;
}
.alignleft {
	float:left;
}
</style>

<?php if (isset($t['blogTitle'])) { ?>
	<h2><?=$t['blogTitle'];?></h2>
<?php } ?>

<?php if (isset($t['blogDescription'])){ ?>
	<p class="description"><?=$t['blogDescription'];?></p>
<?php } ?>

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
	echo '<p style="clear:both;">'.$entry->content.'</p>';
?>
	<div class="links">
	<?php if ($t['prevStyle'] === 'full') { ?>
	<a href="<?=cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id)).$entry->link_text;?>">Comments</a>
	<?php } else { ?>
	<a href="<?=cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id)).$entry->link_text;?>">Read More</a>
	<?php }  ?>
	</div>


	<!-- social book marks -->
	<?php if (count($t['social_bookmarks'])): ?>
		<div class="sociable links">
		<span class="sociable_tagline">
		<strong>Share and Enjoy:</strong>
			<span>These icons link to social bookmarking sites where you can share and discover new web sites.</span>
		</span>

		<div class="sociable">
		<ul> 
		<?php foreach ($t['social_bookmarks'] as $bookmark) { ?>
		<li><a rel="nofollow" target="_blank" href="<?php echo str_replace('{url}', $entry->permalink, str_replace('{title}', $bookmark['title'], $bookmark['url']));?>" title="<?=$bookmark['title'];?>"><img src="<?=cgn_url().'media/'.$bookmark['icon'];?>" title="<?=$bookmark['title'];?>" alt="<?=$bookmark['title'];?>" class="sociable-hovers"></a></li>
		<?php } ?>
		</ul>
		</div>
		</div>

	<?php endif; ?>
	<!-- end social book marks -->

</div>
<?php
}
?>
</div>

<div class="navigation">
	<?php if($t['prevlink']) { ?>
	<div class="alignleft">
	<a href="<?=$t['prevlink']?>">« Previous Entries</a>
	</div>
	<?php } ?>
	<?php if($t['nextlink']) { ?>
	<div class="alignright">
		<a href="<?=$t['nextlink'];?>">Next Entries »</a>
	</div>
	<?php } ?>
</div>
