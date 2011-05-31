<style type="text/css">
.content_wrapper .entry .blog_entry_date {
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
	<div class="blog_entry_date">
		<span style="font-size:90%;">
		<?=$published['month'];?>
		</span>
		<br/>
		<span style="font-size:150%;">
		<?=$published['date'];?>
		</span>
	</div>
<?

	echo '<div style="float:left;"><h2 style="margin:.4em 0 .4em 0;">'.$entry->title.'</h2>';
	if ($entry->caption) {
			echo '<h5 style="margin:0 0 0 1em;">'.$entry->caption.'</h5>';
	}
	echo '</div>';
	echo '<br style="clear:both;"/><p>'.$entry->content.'</p>';

?>

	<br/>
	<br/>
</div>


<!-- social book marks -->
<?php
	if (count($t['social_bookmarks'])) { ?>
<div class="sociable links">
<span class="sociable_tagline">
<strong>Share and Enjoy:</strong>
	<span>These icons link to social bookmarking sites where you can share and discover new web sites.</span>

</span>

<div class="sociable">
<ul> 
<?php foreach ($t['social_bookmarks'] as $bookmark) { ?>
<li><a rel="nofollow" target="_blank" href="<?= $bookmark['url'];?>" title="<?=$bookmark['title'];?>"><img src="<?=cgn_url().'media/'.$bookmark['icon'];?>" title="<?=$bookmark['title'];?>" alt="<?=$bookmark['title'];?>" class="sociable-hovers"></a></li>
<?php } ?>
</ul>
</div>
</div>

<?php
	} ?>
<!-- end content wrapper -->
</div>


<h4 id="hdr_comments">Comments on &quot;<?=$entry->title;?>&quot;</h4>
<?
	foreach ($t['commentList'] as $commentObj) {
		echo '<div style="background-color:#EEF;">';
		if ($commentObj->spam_rating > 0) {
			$spam = '(spam rating:'.$commentObj->spam_rating.')';
		} else {
			$spam = '';
		}

		if (strlen($commentObj->user_name) ) {
			if (strlen($commentObj->user_url) ) {
				$url = $commentObj->user_url;
				if (strpos($url, 'http') !== 0) {
					$url = 'http://'.$url;
				}
				echo '<b><a href="'.$url.'" rel="nofollow">'.$commentObj->user_name.'</a></b> '.$spam;
			} else {
				echo '<b>'.$commentObj->user_name.'</b> '.$spam;
			}
		} else {
			echo "<b>Anonymous</b> ".$spam;
		}
		echo '</div>';
?>
		<?= nl2br(trim($commentObj->content)); ?>
		<br/>
		<span class="content_cmt_sep" style="margin-bottom:1em;">&nbsp;</span>
<?php
	}
?>

<?php
	if (empty($t['commentList'])):
?>
	<p>Be the first to leave a comment.</p>
<?php
	endif;
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

<?php
	if ($t['commentForm']):
		echo $t['commentForm']->toHtml();
	else:
?>
<h3>Add a comment</h3>
	<form action="<?= cgn_appurl('blog','entry','comment', array('id'=>$entry->cgn_blog_entry_publish_id));?>" method="POST">
		<label for="user_name">Your Name:</label> <input type="text" name="user_name" id="user_name" value="<?=$t['userName'];?>" style=""/>
		<br/>
		<label for="home_page">Your Homepage:</label> <input type="text" name="home_page" id="home_page" value="<?=$t['homePage'];?>" style=""/>
		<br/>
	<div style="float:right;margin-right:1em;"><? Cgn_Template::parseTemplateSection('nav.idprovider.badge');?> </div>
		<label for="comment">Comment:</label>
		<textarea rows="19" cols="50" name="comment"></textarea>
		<br/>
		<input type="submit" name="submit_bt" value="Submit"/>
	</form>
<?php
	endif;
?>

