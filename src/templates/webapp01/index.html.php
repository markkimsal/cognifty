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
<!--
      <div class="hero-unit">
        <h1>Hello, world!</h1>
        <p>Vestibulum id ligula porta felis euismod semper. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>
        <p><a class="btn primary large">Learn more &raquo;</a></p>
      </div>
-->

      <?php Cgn_Template::showSessionMessages();  ?>
      <div class="row">
        <div class="span14 offset1">
			<?php echo Cgn_Template::parseTemplateSection('content.main');?>
		</div>
      </div>

      <!-- Example row of columns -->
<!--
      <div class="row">
        <div class="span-one-third">
          <h2>Heading</h2>
          <p>Etiam porta sem malesuada magna mollis euismod. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>
          <p><a class="btn" href="#">View details &raquo;</a></p>
        </div>
        <div class="span-one-third">
          <h2>Heading</h2>
           <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
          <p><a class="btn" href="#">View details &raquo;</a></p>
       </div>
        <div class="span-one-third">
          <h2>Heading</h2>
          <p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
          <p><a class="btn" href="#">View details &raquo;</a></p>
        </div>
      </div>
-->

	<div class="site-footer">
		<?php echo Cgn_Template::showMenu('menu.bottom');  ?>
		<p>&copy; <?php echo cgn_copyrightname();?> 2011</p>
	</div>
    </div> <!-- /container -->

  </body>
</html>
