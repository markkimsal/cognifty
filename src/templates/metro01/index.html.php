<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Metro 01: <?php echo Cgn_Template::getPageTitle();?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="<?php cgn_templateurl();?>metro01.css" rel="stylesheet" type="text/css" />
<link href="<?php cgn_templateurl();?>menu.css" rel="stylesheet" type="text/css" />
<link href="<?php echo cgn_url();?>media/shared_css/system.css" rel="stylesheet" type="text/css" />


<link rel="stylesheet" href="<?php cgn_templateurl();?>css/reset.css" />
<link rel="stylesheet" href="<?php cgn_templateurl();?>css/text.css" />
<link rel="stylesheet" href="<?php cgn_templateurl();?>css/960.css" />


<?php echo Cgn_Template::getSiteCss(); ?>
<script src="<?php echo cgn_url();?>media/js/jquery-1.2.5.min.js" rel="javascript" type="text/javascript" ></script>
<?php echo Cgn_Template::getSiteJs(); ?>


<link rel="shortcut icon"
   href="/media/favicon.ico"
   type="image/ico" />
</head>
<body>
<?php
	Cgn_Template::showErrors();
?>

<div id="wrap"  class="container_16">
	<div id="wrap_top"></div>
	<div id="main">

		<div id="header">
			<ul class="navbar">
				<li><a href="<?=cgn_appurl('main');?>">Home</a></li>
				<li><a href="<?=cgn_appurl('blog');?>">Blog</a></li>
				<li><a href="<?=cgn_appurl('tutorial');?>">Tutorial</a></li>
				<li><a href="<?=cgn_appurl('main','about');?>">About</a></li>
<? $u = Cgn_SystemRequest::getUser();?>
<? if ($u->isAnonymous() ): ?>
				<li><a href="<?=cgn_appurl('login');?>">Sign-in</a></li>
<? else: ?>
				<li><a href="<?=cgn_appurl('account');?>">Account Settings</a>&nbsp;<a href="<?=cgn_appurl('login','main','logout');?>">Not <?=$u->getDisplayName();?>? Sign-out</a></li>
<? endif ?>
			</ul>
			<div class="graphic">
			<h1 class="title"><?= Cgn_Template::siteName();?></h1>
			</div>
		</div>
			
		<div id="main_content" class="grid_13 alpha">
			<?php Cgn_Template::showSessionMessages();  ?>
			<?php Cgn_Template::showBreadCrumbs(); ?>
			<?php Cgn_Template::parseTemplateSection('content.main'); ?>

		</div>
		
		<div id="rightbar" class="grid_3 omega">
			<div class="box" id="desc">
			<?php Cgn_Template::parseTemplateSection('content.side'); ?>
			This is some extra content, it can be used for news, links, updates, or anything else.
			</div>

			<div class="box">
				<h2>Links</h2>
				<ul>
					<li><a href="http://sourceforge.net/projects/niftyphp/">Project Page</a></li>
					<li><a href="http://biz.metrofindings.com/">Open Source Consulting</a></li>
					<li><a href="http://sourceforge.net/projects/logicampus/">Distance Learning LMS</a></li>
				</ul>
			</div>
			<!--
			<div>
				<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=197717&amp;type=3" width="125" height="37" border="0" alt="SourceForge.net Logo" /></a>
			</div>
			-->

			
			<div class="box">
				<?php Cgn_Template::showMenu('main.menu'); ?>
			</div>
			<div class="box">
				<?php Cgn_Template::showMenu('menu.topics'); ?>
			</div>

			<div class="box">
				<?php //Cgn_Template::parseTemplateSection('box.links'); ?>
			</div>
		</div>
		
		<div id="footer">
		&copy; <?=cgn_copyrightname();?> | Design by <a href="http://www.drugo.biz">Carlo Forghieri</a>
		</div>
	</div>
</div>

</body>
</html>
