<?php


class Blog_Util {


	function showRecentPosts() {
		$entry = new Cgn_DataItem('cgn_blog_entry_publish');
		$entry->orderBy('posted_on DESC');
		$entry->limit(5);
		$posts = $entry->find();
		foreach ($posts as $entry) {
			echo '<li><a href="'.cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id)).$entry->link_text.'">'.$entry->title.'</a></li>';
		}
	}
}
