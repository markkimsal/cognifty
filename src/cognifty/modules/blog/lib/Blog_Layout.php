<?php

class Cgn_Blog_Layout {

	public static function showTagsAsLi($sectionId, $templateMgr) {
		$output = '';
		$finder = new Cgn_DataItem('cgn_blog_entry_tag');
		$res = $finder->find();
		foreach ($res as $_r) {
			$output .= '<li><a href="'.cgn_appurl('blog', 'entry', 'tag').$_r->get('link_text').'">'.$_r->get('name').'</a></li> ';
		}
		return $output;
	}
}
