<?php

/**
 * Designed to work with the Cgn_DataItem class
 */
class Cgn_Content {
	var $dataItem;

}


/**
 * Help publish content to the article table
 */
class Cgn_Article extends Cgn_Content {
	var $contentItem;
}


/**
 * Help publish content to the blog entry table
 */
class Cgn_BlogEntry extends Cgn_Content {
	var $contentItem;
}


/**
 * Help publish content to the news item table
 */
class Cgn_NewsItem extends Cgn_Content {
	var $contentItem;
}


/**
 * Help publish content to the image table
 */
class Cgn_Image extends Cgn_Content {
	var $contentItem;
}


/**
 * Help publish content to the generic asset table.
 * This is supposed to be things like flash plugins, PDFs, 
 * other embedded items, or things that need plugin players.
 */
class Cgn_Asset extends Cgn_Content {
	var $contentItem;
}

?>
