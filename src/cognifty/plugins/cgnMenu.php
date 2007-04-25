<?php

/**
 *    menu plugin 
 *
 */
/**
 * example
      <div>
        <p class="sideBarTitle">Navigate this page</p>
        <ul>
          <li><a href="#introduction">&rsaquo; Introduction</a></li>
          <li><a href="#cross-browser" title="Improved cross-browser compatibility">&rsaquo; Cross-browser</a></li>
          <li><a href="#stylesheets" title="Modified stylesheets">&rsaquo; Stylesheets</a></li>
          <li><a href="#accessibility" title="Improved accessibility">&rsaquo; Accessibility</a></li>
        </ul>
      </div>
      <div>
        <p class="sideBarTitle">Sample menu</p>
        <ul>
          <li><a href="./index.html">&rsaquo; Sidebar</a></li>
          <li><span class="thisPage">&raquo; Links</span></li>
          <li><a href="./index.html">&rsaquo; Go</a></li>
          <li><a href="./index.html">&rsaquo; Here</a></li>
          <li><a href="http://www.oswd.org/email.phtml?user=haran">&rsaquo; Submit comments</a></li>
        </ul>
      </div>
 */
 
class cgnMenu {

/**
 * show the menu
 * 
 * 
 * @return string HTML
 */
	function show($params) { 
		
		$menu = parse_ini_file(CGN_BOOT_DIR."menu.ini", TRUE);
		ob_start();
		foreach($menu as $section=>$links) { 
			"<div>\n";
			echo "<p class='sideBarTitle'>$section</p>\n";
			echo "<ul>\n";
			foreach($links as $name=>$link) {
				echo "<li><a href='http://".Cgn_SystemRequest::url($link)."' alt='$name'>$name</a></li>\n";
			}
			echo "</ul>\n";
			echo "</div>\n";
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
		
	}


}
