<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Metro 01</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="<?php cgn_templateurl();?>metro01.css" rel="stylesheet" type="text/css" />
<link href="<?php cgn_templateurl();?>menu.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php cgn_templateurl();?>expandingMenu.js"></script>
<link rel="shortcut icon"
   href="/favicon.ico"
   type="image/ico" />
</head>
<body>
<div id="wrap">
	<div id="wrap_top"></div>
	<div id="main">
	
		<div filter="text/hexColor/f00" id="head">
			<ul class="navbar">
				<li><a href="<?=cgn_appurl('main');?>">Home</a></li>
				<li><a href="<?=cgn_appurl('tutorial');?>">Tutorial</a></li>
				<li><a href="#">About</a></li>
				<li><a href="#">Contacts</a></li>
			</ul>
			<h1 class="title">Metro 01</h1>
		</div>
			
		<div id="main_content">

			<?php Cgn_Template::parseTemplateSection('content.main'); ?>

		</div>
		
		<div id="rightbar">
			<div class="box" id="desc">
			<?php Cgn_Template::parseTemplateSection('content.side'); ?>
			This is some extra content, it can be used for news, links, updates, or anything else.
			</div>
			<!--
			<div class="box">
				<h2>Project services by:</h2>
				<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=197717&am
				p;type=2" width="125" height="37" border="0" alt="SourceForge.net Logo" /></a>
			</div>
			-->

			<div class="box">
				<h2>Links</h2>
				<ul>
					<li><a href="http://sourceforge.net/projects/niftyphp/">Project Services</a></li>
					<li><a href="http://biz.metrofindings.com/">Open Source Consulting</a></li>
					<li><a href="http://sourceforge.net/projects/logicampus/">Distance Learning LMS</a></li>
				</ul>
			</div>
			
			<div class="box">
				<?php Cgn_Template::parseTemplateSection('box.links'); ?>
<!--
				<h2>Links</h2>
				<ul>
					<li><a href="#">Site 1</a></li>
					<li><a href="#">Site 2</a></li>
					<li><a href="#">Site 3</a></li>
				</ul>
-->
			</div>
		</div>
		
		<div id="footer">
			&copy; Name | Design by <a href="http://www.drugo.biz">Carlo Forghieri</a>
		</div>
	</div>
</div>

<!--
<p><div align="center">
<font face="arial, helvetica" size"-2">Free JavaScripts provided<br>
by <a href="http://javascriptsource.com">The JavaScript Source</a></font>
</div><p>

-->

</body>
</html>
