<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta name="Description" content="Information architecture, Web Design, Web Standards." />
<meta name="Keywords" content="your, keywords" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Distribution" content="Global" />
<meta name="Author" content="Erwin Aligam - ealigam@gmail.com" />
<meta name="Robots" content="index,follow" />

<link rel="stylesheet" href="<?=cgn_templateurl();?>images/Refresh.css" type="text/css" />
<link rel="stylesheet" href="<?=cgn_url();?>media/shared_css/system.css" type="text/css" />

<title>Z <?php echo Cgn_Template::getPageTitle();?> :: <? cgn_sitename(); ?></title>
</head>

<body>
<!-- wrap starts here -->
<div id="wrap">
		
		<!--header -->
		<div id="header">			
				
<!--
			<h1 id="logo-text">re<span class="gray">fresh</span></h1>		
-->
				
			<h1 id="logo-text"><a href="<?=cgn_appurl('main');?>"><?= Cgn_Template::siteName();?></a></h1>
			<h2 id="slogan"><?= Cgn_Template::siteTagLine();?></h2>
				
			<form class="search" method="post" action="<?=cgn_appurl('search');?>">
				<p>
	  			<input class="textbox" type="text" name="q" id="search_query" value="" />
	 			<input class="button" type="submit" name="Submit" value="Search" />
				</p>
			</form>			
				
		</div>
		
		<!-- menu -->	
		<div  id="menu">

			<ul>
			<? if ($u->isAnonymous() ): ?>
			<li>
							<a href="<?=cgn_appurl('login');?>">Sign-in</a>

			</li>
			<? else: ?>
						<li><a href="<?=cgn_appurl('login','main','logout');?>">Not <?=$u->getDisplayName();?>? Sign-out</a>
			</li>
			<li>
							<a href="<?=cgn_appurl('account');?>">Account Settings</a>
			</li>

			<? endif ?>
			</ul>

<? Cgn_Template::showMenu('menu.top', array('class'=>'left-box sidemenu'));?>
		</div>					
			
		<!-- content-wrap starts here -->
		<div id="content-wrap">
				
			<div id="sidebar">

<? Cgn_Template::showMenu('menu.main', array('class'=>'left-box sidemenu'));?>

			
				<h1>Wise Words</h1>
				<div class="left-box">
					<p>&quot;To be concious that you are ignorant of the
					facts is a great step to knowledge&quot; </p>
					
					<p class="align-right">- Benjamin Disraeli</p>
				</div>	
				
				<h1>Support Styleshout</h1>
				<div class="left-box">
					<p>If you are interested in supporting my work and would like to contribute, you are
					welcome to make a small donation through the 
					<a href="http://www.styleshout.com/">donate link</a> on my website - it will 
					be a great help and will surely be appreciated.</p>
				</div>
							
				
			</div>
				
			<div id="site-content-main">

			<?php Cgn_Template::showSessionMessages();  ?>
<? Cgn_Template::parseTemplateSection('content.main');?>

			<!-- site-content-main ends here -->	
			</div>
		
		<!-- content-wrap ends here -->	
		</div>
					
		<!--footer starts here-->
		<div id="footer">
			
			<p>

			&copy; <strong><?=cgn_copyrightname();?></strong> | 
			Design by: <a href="http://www.styleshout.com/">styleshout</a> | 
			Valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a> &mdash; 
			<a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
			|
			<a href="http://cognifty.com/">Cognfity</a> rev: 
			<?=Cgn_SystemRunner::getReleaseNumber();?>.<?=Cgn_SystemRunner::getBuildNumber();?>

   		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

			<a href="<?=cgn_url();?>">Home</a>&nbsp;|&nbsp;
<!--
   		<a href="index.html">Sitemap</a>&nbsp;|&nbsp;
-->
	   	<a href="<?=cgn_appurl('rss');?>">RSS Feed</a>
   		</p>
				
		</div>	

<!-- wrap ends here -->
</div>

</body>
</html>
