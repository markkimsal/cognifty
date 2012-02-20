<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<title><?php echo Cgn_Template::siteName();?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
	<link href="<?php echo cgn_templateurl(); ?>css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo cgn_url(); ?>media/shared_css/system.css" rel="stylesheet">
	<link href="<?php echo cgn_url(); ?>media/shared_css/form.css" rel="stylesheet">
	<link href="<?php echo cgn_templateurl(); ?>css/webapp-screen.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
      }
    </style>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
	<link rel="shortcut icon"
		href="<?php echo cgn_templateurl();?>img/favicon.ico"
		type="image/ico" />
  </head>

  <body>

    <div class="topbar">
      <div class="fill">
        <div class="container">
		<a class="brand" href="<?php echo cgn_url();?>"><?php echo Cgn_Template::siteName();?></a>
		<?php echo Cgn_Template::showMenu('menu.top');  ?>
        </div>
      </div>
    </div>

    <div class="container" id="site-wrap">

      <!-- Main hero unit for a primary marketing message or call to action -->

		<?php Cgn_Template::showSessionMessages();  ?>
      <div class="hero-unit">
		<?php echo Cgn_Template::parseTemplateSection('content.main');?>
      </div>

    </div> <!-- /container -->

    <div class="container">
	<div class="site-footer">
		<?php echo Cgn_Template::showMenu('menu.bottom');  ?>
		<p>&copy; <?php echo cgn_copyrightname();?> 2011</p>
	</div>
    </div> <!-- /container -->


  </body>
</html>
