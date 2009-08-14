<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php cgn_sitename();?> Control Center</title>
    <link href="<?php cgn_templateurl();?>admin01-screen.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="outterwrapper">
<div id="topbar">

	<div class="topstuff">
		<a href="<?=cgn_url();?>" target="_blank">View Site</a>
	</div>
	<div class="toptitle">Cognifty Control Center</div>
	<div class="topsitename"><?php cgn_sitename();?> &mdash; <?= cgn_sitetagline();?></div>
</div>

<div id="navbar">
	<div class="clearer"></div>
</div>
<br/>
<div id="content">

	<div class="clearer"></div>

	<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr><td width="120" valign="top">
		</td><td valign="top">
			<?php Cgn_Template::showSessionMessages();  ?>
		<div id="contentcontent">
			<?php Cgn_Template::parseTemplateSection('content.main'); ?>
		</div>
		</td></tr>
	</table>
</div>
</div>

	<div id="footer">
	&copy; 2006 Mark Kimsal. Design inspired by <a href="http://threadbox.net/">Thread</a>.
	</div>

</body>
</html>
