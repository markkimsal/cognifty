<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Metro 01: <?php echo Cgn_Template::getPageTitle();?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<link href="<?php echo cgn_url();?>media/shared_css/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo cgn_url();?>media/shared_css/form.css" rel="stylesheet" type="text/css" />


<link rel="stylesheet" href="<?php cgn_templateurl();?>960min/reset.css" />
<link rel="stylesheet" href="<?php cgn_templateurl();?>960min/text.css" />
<link rel="stylesheet" href="<?php cgn_templateurl();?>960min/960.css" />

<link href="<?php cgn_templateurl();?>css/blueblog01.css" rel="stylesheet" type="text/css" />

<link rel="shortcut icon"
   href="/media/favicon.ico"
   type="image/ico" />
</head>
<body>
<?php
	Cgn_Template::showErrors();
?>

<div id="wrap">

	
	<div id="nav-top"  style="width:100%;">
		<div class="container_16" >
			<div id="nav-links" class="grid_16 alpha omega">
			<?php Cgn_Template::showMenu('menu.main'); ?>
			</div>
		</div>
	</div>

	
	<div id="nav-middle" style="width:100%;">
		<div class="container_16" >
			<div id="site-title" class="grid_16 alpha omega">
			<h1 class="title"><?= Cgn_Template::siteName();?></h1>
			<h3 class="tag-line"><?= Cgn_Template::siteTagline();?></h3>
			</div>
		</div>
	</div>
		
	<div id="main-content" class="container_16">
		<div id="main-content" class="alpha omega grid_16">
		<?php Cgn_Template::showSessionMessages();  ?>
		<?php Cgn_Template::showBreadCrumbs(); ?>
		<?php Cgn_Template::parseTemplateSection('content.main'); ?>
		</div>
	</div>


</div> <!-- id=wrap -->

	<div id="site-footer" style="width:100%">
	<div class="container_16">

	<div class="grid_4 prefix_4 alpha omega">
		<div class="site-footer-list">
		<h3>Site Links</h3>
		<?php Cgn_Template::showMenu('menu.main'); ?>
		</div>
	</div>
	<div class="grid_4 alpha omega">
		<div class="site-footer-list">
		<h3>Site Links</h3>
		<?php Cgn_Template::showMenu('menu.main'); ?>
		</div>
	</div>
	<div class="grid_4 alpha omega">
		<div class="site-footer-list">
		<h3>Site Links</h3>
		<?php Cgn_Template::showMenu('menu.main'); ?>
		</div>
	</div>


	<div style="clear"></div>

	<div class="grid_16 alpha omega">
	<hr/>
		&copy; <?=cgn_copyrightname();?> | Design by Mark Kimsal
	</div>
	</div>
	</div>


</body>
</html>
