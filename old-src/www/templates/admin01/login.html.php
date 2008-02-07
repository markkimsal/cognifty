<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php cgn_sitename();?> Control Center</title>
    <link href="<?php cgn_templateurl();?>admin01-screen.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="topbar">

	<div class="topstuff">
	</div>
	<div class="toptitle"><?php cgn_sitename();?> Control Center<!--<img src="<?=cgn_templateurl();?>/images/title.gif" width="543" height="44" alt="LogiCreate Control Center" />--></div>
</div>

<div id="navbar">
	<div class="clearer"></div>
</div>
<br/>
<div id="content">

	<div class="clearer"></div>

	<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr><td width="120" valign="top">
		<div id="contentmenu">
		</div>
		</td><td valign="top">
		<div id="contentcontent">
			<?php Cgn_Template::parseTemplateSection('content.main'); ?>
		</div>
		</td></tr>
	</table>
</div>

	<div id="footer">
	&copy; 2006 Mark Kimsal. Design inspired by <a href="http://threadbox.net/">Thread</a>.
	</div>

</body>
</html>
