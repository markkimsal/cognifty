<?php

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Main_Sitemap extends Cgn_Service {

	var $crumbs   = NULL;
	var $pages    = array();
	var $articles = array();

	/**
	 * Return an array to be placed into the bread crumb trail.
	 *
	 * @return 	Array 	list of strings.
	 */
	function getBreadCrumbs() {
		return array();
	}


	/**
	 * Export XML of all pages, articles, images, and modules
	 */
	function mainEvent(&$sys, &$t) {

		$this->presenter = 'self';
		$web = new Cgn_DataItem('cgn_web_publish');
		$this->pages =  $web->find();
		$loader = new Cgn_DataItem('cgn_article_publish');
		$loader->sort('published_on','DESC');
		$this->articles = $loader->find();

		Cgn_ErrorStack::pullError('php');

	}

	public function output($req, &$t) {
		header ('Content-type: text/xml');
		echo '<'.'?xml version="1.0" encoding="UTF-8"?>';
		echo "\n";
		echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		foreach ($this->pages as $p) {
			echo " <url>\n";
			echo "  <loc>".cgn_appurl('main', 'page').$p->link_text."</loc>\n";
			echo "  <lastmod>".date('Y-m-d', $p->get('published_on'))."</lastmod>\n";
			echo "  <changefreq>yearly</changefreq>\n";
			echo "  <priority>0.5</priority>\n";
			echo " </url>\n";
		}
		foreach ($this->articles as $p) {
			echo " <url>\n";
			echo "  <loc>".cgn_appurl('main', 'article').$p->link_text."</loc>\n";
			echo "  <lastmod>".date('Y-m-d', $p->get('published_on'))."</lastmod>\n";
			echo "  <changefreq>yearly</changefreq>\n";
			echo "  <priority>0.5</priority>\n";
			echo " </url>\n";
		}
		//blog
		echo " <url>\n";
		echo "  <loc>".cgn_appurl('blog')."</loc>\n";
		echo "  <changefreq>daily</changefreq>\n";
		echo "  <priority>0.6</priority>\n";
		echo " </url>\n";

		echo "</urlset>\n";
	}
}
