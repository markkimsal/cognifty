<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-AU">
  <head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    <meta name="author" content="haran" />
    <meta name="generator" content="author" />

    <!-- Navigational metadata for large websites (an accessibility feature): -->
    <link rel="top"      href="./index.html" title="Homepage" />
    <link rel="up"       href="./index.html" title="Up" />
    <link rel="first"    href="./index.html" title="First page" />
    <link rel="previous" href="./index.html" title="Previous page" />
    <link rel="next"     href="./index.html" title="Next page" />
    <link rel="last"     href="./index.html" title="Last page" />
    <link rel="toc"      href="./index.html" title="Table of contents" />
    <link rel="index"    href="./index.html" title="Site map" />

    <link rel="stylesheet" type="text/css" href="<?php cgn_templateurl();?>sinorca-screen.css" media="screen" title="Sinorca (screen)" />
    <link rel="stylesheet alternative" type="text/css" href="<?php cgn_templateurl();?>sinorca-screen-alt.css" media="screen" title="Sinorca (alternative)" />
    <link rel="stylesheet" type="text/css" href="<?php cgn_templateurl();?>sinorca-print.css" media="print" />

    <title><?=cgn_sitename();?></title>
  </head>

  <body>
    <!-- For non-visual user agents: -->
      <div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

    <!-- ##### Header ##### -->

    <div id="header">
<!--
      <div class="superHeader">
        <div class="left">
          <span class="doNotDisplay">Related sites:</span>
          <a href="./index.html">Link 1</a> |
          <a href="./index.html">Link 2</a>
        </div>
        <div class="right">
          <span class="doNotDisplay">More related sites:</span>
          <a href="./index.html">Link 3</a> |
          <a href="./index.html">Link 4</a> |
          <a href="./index.html">Link 5</a> |
          <a href="./index.html">Link 6</a> |
          <a href="./index.html">Link 7</a>
        </div>
      </div>
-->

      <div class="midHeader">
    	<h1 class="headerTitle"><?=cgn_sitename();?></h1>
      </div>

      <div class="subHeader">
	<?=cgn_sitetagline();?>
      </div>
    </div>

    <!-- ##### Side Bar ##### -->

    <div id="side-bar" plugin="menu/show">
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

   
    </div>

    <!-- ##### Main Copy ##### -->

    <div id="main-copy">

			<?php Cgn_Template::parseTemplateSection('content.main'); ?>
    </div>
    
    <!-- ##### Footer ##### -->

    <div id="footer">
      <div class="left">
        E-mail:&nbsp;<a href="./index.html" title="Email webmaster">webmaster@your.company.com</a><br />
        <a href="./index.html" class="doNotPrint">Contact Us</a>
      </div>

      <br class="doNotDisplay doNotPrint" />

      <div class="right">
        This design is (almost) public domain.<br />
        <a href="./index.html" class="doNotPrint">This is a footer link</a>
      </div>
    </div>
  </body>
</html>
