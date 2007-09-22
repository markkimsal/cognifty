<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php cgn_sitename();?> Control Center</title>
    <link href="<?php cgn_templateurl();?>admin01-screen.css" rel="stylesheet" type="text/css" />
    <script language="JavaScript" src="<?=cgn_templateurl();?>menu.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_templateurl();?>wiki.js" type="text/javascript"></script>
</head>

<body>
<?php
	Cgn_Template::showErrors();
?>
<div id="topbar">

<div class="topstuff">

	<form action="<?=cgn_templateurl('adm','main','dispatch');?>">
	<!--    <label for="dropdown">Module:</label> -->
	<select name="action">
		<option>
		Choose a Module:
		</option>
	<? for ($x=0; $x < count($t['installedModules']); $x++) { 
		echo '
		<option value="'.$t['installedModules'][$x]['mid'].'">
		'.ucfirst($t['installedModules'][$x]['display_name']).'
		</option>
		';
	 } ?>
	</select>
	<input type="submit" value="go"/>
	</form>
	</div>
	<div class="toptitle"><?php cgn_sitename();?> Control Center<!--<img src="<?=cgn_templateurl();?>/images/title.gif" width="543" height="44" alt="LogiCreate Control Center" />--></div>
</div>

<div id="navbar">
<ul style="width:100%;">
	<li <?if (@$t['selectedTab'] == 'mods') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'mods') echo 'class="current"'; ?>href="<?=cgn_adminurl('mods');?>">Modules</a>
	</li>
	<li onmouseover="showMenuDrop(this.offsetWidth);" <?if (@$t['selectedTab'] == 'users') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'users') echo 'class="current"'; ?> href="<?=cgn_adminurl('users');?>">Users</a>
	</li>
	<!-- <li <?if (@$t['selectedTab'] == 'email') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'email') echo 'class="current"'; ?> href="<?=cgn_adminurl('email');?>">Email</a>
	     </li>
	     <li>
		<a href="#" style="text-decoration:line-through;">Settings</a>
	     </li>
	     <li>
		<a href="#" style="text-decoration:line-through;width:100px;">Jobs</a>
	     </li> -->
	<li class="menu_last">
		<a href="<?=cgn_url();?>" target="_blank">View Site</a>
	</li>
</ul>

<!--   I PREFER THE NEW TOOL BAR EFFECT 
<div id="menu_drop"  style="position:absolute; left:10em; display:none;" onmouseout="closeMenuDrop();">
	<? 
		//echo '<img border="0" align="middle" width="16" height="16" src="'.IMAGES_URL.'cross_logo.png"/>';
		//echo '<a onmouseover="showMenuDrop();" href="'.cgn_adminurl('users','main').'">List</a><br/>';
		//echo '<a onmouseover="showMenuDrop();" href="'.cgn_adminurl('users','groups').'">Groups</a><br/>';
	?>
</div>
-->
<div class="clearer"></div>

</div>

<br/>

<div id="tcontent">
	<?php
		/*
	if ( is_array($ccObj->menu) ) { ?>
	<div id="subnav">
	<ul>
	<? foreach ( $ccObj->menu as $blank=>$m) { ?>
	    <li class="current"><a href="<?=cgn_url($m->module,$m->service,$m->event);?>" class="current"><?=$m->linkText;?></a></li>
	<? } ?>
	</ul>
	</div>
	<? }
	*/
	?>

	<div class="clearer"></div>

	<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr><td width="120" valign="top">
		<div id="contentmenu">
<?php
include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_panel.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_menu.php');

$list = new Cgn_Mvc_ListModel();
$list->data = array(
	0=> array('Pages',cgn_adminurl('content','web')),
	1=> array('Articles',cgn_adminurl('content','articles')),
	2=> array('Images',cgn_adminurl('content','images')),
	3=> array('Assets',cgn_adminurl('content','assets')),
	4=> array('New Content',cgn_adminurl('content','main')),
);
$p = new Cgn_HtmlWidget_Menu('<h3>CMS</h3>',$list);
echo $p->toHtml();


$list2 = new Cgn_Mvc_ListModel();
$list2->data = array(
	0=> array('Menus',cgn_adminurl('menus')),
	1=> array('Site Areas',cgn_adminurl('site','area')),
	2=> array('Site Structure',cgn_adminurl('site','structure')),
	3=> array('Message Queue',cgn_adminurl('mxq')),
//	4=> array('Stats','#')
);
$p = new Cgn_HtmlWidget_Menu('<h3>Site</h3>',$list2);
echo $p->toHtml();

/*
$list3 = new Cgn_Mvc_ListModel();
$list3->data = array(
	0=> array('Sources','#'),
	1=> array('Test','#'),
	2=> array('Stats','#')
);
$p = new Cgn_HtmlWidget_Menu('<h3>Data</h3>',$list3);
echo $p->toHtml();
 */
?>

		</div>
		</td><td valign="top">
		<div id="contentcontent">
			<?php Cgn_Template::showMessages(); ?>
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
