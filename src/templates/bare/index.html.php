<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo Cgn_Template::getPageTitle();?></title>
<link href="<?php echo cgn_url();?>media/shared_css/system.css" rel="stylesheet" type="text/css" />
<?= Cgn_Template::getSiteCss();?>
<?= Cgn_Template::getSiteJs();?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>
	<?php Cgn_Template::parseTemplateSection('content.main'); ?>
</body>
</html>
