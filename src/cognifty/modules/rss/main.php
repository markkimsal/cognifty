<?php


class Cgn_Service_Rss_Main extends Cgn_Service {

	var $presenter = 'self';

	/**
	 * Load up blog posts and show in RSS 2.0 format
	 */
	function mainEvent($req, &$t) {

		Cgn::loadModLibrary('Blog::Blog_Util');

		$items = array();
		$blogList = Blog_Util::getAllBlogs();
		$blogPostList = Blog_Util::getRecentPosts(20);
		$this->formatBlogPosts($blogPostList, $items, $blogList);

		$t['channel']    = array();
		$t['channel']['title'] = Cgn_Template::siteName();
		$t['channel']['link']  = cgn_url();
		$t['channel']['ttl'] = 240;
		$t['channel']['items'] = $items;
	}

	/**
	 * This turns blog post data items into an array for output.
	 *
	 * This method uses the posts's excerpt as a description, or the first 
	 * 1000 characters of the post body.
	 * It also translates the parent blog's name into the 'category' value.
	 *
	 */
	function formatBlogPosts(&$blogPostList, &$items, &$blogList) {
		foreach ($blogPostList as $_post) {
			$item = array();
			$item['blog_id'] = $_post->cgn_blog_id;
			$item['title']   = $_post->title;
			$item['link']    = cgn_appurl('blog','entry','', 
				array('id'=>$_post->cgn_blog_entry_publish_id)
				)
				.$_post->link_text;

			if (isset($_post->excerpt) && $_post->excerpt != '') {
				$item['description'] = $_post->excerpt;
			} else {
				$item['description'] = substr( strip_tags($_post->content), 0, 1000);
			}
			$item['published_on'] = $_post->posted_on;
			$item['category'] = $blogList[$_post->cgn_blog_id]->getTitle();
			$items[] = $item;
		}
	}

	/**
	 * Run through an array and return only items with a matching blog_id
	 */
	function getItemsForBlog(&$items, $id) {
		$newItems = array();
		foreach ($items as $_item) {
			if ($_item['blog_id'] == $id) {
				$newItems[] = $_item;
			}
		}
		return $newItems;
	}


	/**
	 * Output 1 RSS channel in RSS 2.0 format
	 *
	 * $t['channel'][0] has 'ttl', client caching of this stream in minutes
	 * $t['channel'][0] has 'title', title of this channel
	 * $t['channel'][0] has 'link', link to the home page of channel
	 * $t['channel'][0] has 'items', items in this channel
	 *
	 * 'items' is an associative array: link, title, description, 
	 *   published_on, and guid
	 *
	 * @param object $req  the request processor
	 * @param array  $t    default template array
	 */
	function output($req, &$t) {
		$today = explode('-', date('m-d-Y'));
		header('Content-type: application/xml');
echo <<<EOXML
<?xml version="1.0"?>
<rss version="2.0">
EOXML;
echo '
   <channel>
      <title>'.htmlentities($t['channel']['title']).'</title>
      <link>'.$t['channel']['link'].'</link>
	  <ttl>'.$t['channel']['ttl'].'</ttl>
      <description>'.Cgn_Template::siteTagLine().'</description>
      <language>en-us</language>
      <pubDate>'.date('r', mktime(4,0,0,$today[0], $today[1], $today[2])).'</pubDate>
      <lastBuildDate>'.date('r', mktime(0,0,0,$today[0], $today[1], $today[2])).'</lastBuildDate>
      <generator>Cognifty '.Cgn_SystemRunner::getReleaseNumber().'</generator>
	  ';

		foreach ($t['channel']['items'] as $_item) {
			echo '
      <item>
			<title>'.$_item['title'].'</title>
			<link>'.$_item['link'].'</link>
			<category>'.$_item['category'].'</category>
			<description>'.$_item['description'].'</description>
			<pubDate>'.date('r', $_item['published_on']).'</pubDate>
			<guid>'.$_item['link'].'</guid>
      </item>
	  ';
	}
		echo '
   </channel>
</rss>';
	}
}
