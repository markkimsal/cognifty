<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php cgn_sitename();?> Control Center</title>
    <link href="<?php cgn_templateurl();?>admin01-screen.css" rel="stylesheet" type="text/css" />
    <script language="JavaScript" src="<?=cgn_templateurl();?>menu.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_templateurl();?>wiki.js" type="text/javascript"></script>
</head>

<body>
<?php
	Cgn_Template::showErrors();
?>

		<div id="contentcontent">
			<?php Cgn_Template::parseTemplateSection('content.main'); ?>
		</div>
</body>
</html>
