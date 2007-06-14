<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php cgn_sitename();?> Control Center</title>
    <link href="<?php cgn_templateurl();?>admin01-screen.css" rel="stylesheet" type="text/css" />
    <script language="JavaScript" src="<?=cgn_templateurl();?>menu.js" type="text/javascript"></script>
</head>

<body>

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
<ul>
	<li <?if ($t['selectedTab'] == 'mods') echo 'class="current"'; ?>><a <?if ($t['selectedTab'] == 'mods') echo 'class="current"'; ?>href="<?=cgn_adminurl('mods');?>">Modules</a></li><li onmouseover="showMenuDrop();" <?if ($t['selectedTab'] == 'users') echo 'class="current"'; ?>><a <?if ($t['selectedTab'] == 'users') echo 'class="current"'; ?> href="<?=cgn_adminurl('users');?>">Users</a></li><li <?if ($t['selectedTab'] == 'email') echo 'class="current"'; ?>><a <?if ($t['selectedTab'] == 'email') echo 'class="current"'; ?> href="<?=cgn_adminurl('email');?>">Email</a></li><li><a href="#" style="text-decoration:line-through;">Settings</a></li><li><a href="#" style="text-decoration:line-through;">Jobs</a></li>
<li class="menu_last"><a href="<?=cgn_appurl();?>" target="_blank">View Site</a></li>
</ul>
	<div id="menu_drop" style="background-color:#F4F4F4; padding-left:1px;padding-top:3px;padding-bottom:3px; border-top:1px solid #CCC; border-bottom:1px solid #CCC; border-right:1px solid #CCC; border-left:1px solid #F70; width:10em; position:absolute; left:5.5em; display:none;" onmouseout="closeMenuDrop();">
		<? 
			//echo '<img border="0" align="middle" width="16" height="16" src="'.IMAGES_URL.'cross_logo.png"/>';
			echo '<a onmouseover="showMenuDrop();" href="'.cgn_adminurl('users','main').'">List</a><br/>';
			echo '<a onmouseover="showMenuDrop();" href="'.cgn_adminurl('users','groups').'">Groups</a><br/>';
		?>
	</div>



<div class="clearer"></div>
</div>
<br/>
<div id="content">
	<?php
	if ( is_array($ccObj->menu) ) { ?>
	<div id="subnav">
	<ul>
	<? foreach ( $ccObj->menu as $blank=>$m) { ?>
	    <li class="current"><a href="<?=cgn_url($m->module,$m->service,$m->event);?>" class="current"><?=$m->linkText;?></a></li>
	<? } ?>
	</ul>
	</div>
	<? }
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
	0=> array('Articles',cgn_adminurl('content','articles')),
	1=> array('Images',cgn_adminurl('content','images')),
	2=> array('Content',cgn_adminurl('content','main')),
);
$p = new Cgn_Menu('CMS',$list);
echo $p->toHtml();


$list2 = new Cgn_Mvc_ListModel();
$list2->data = array(
	0=> array('Menus','#'),
	1=> array('Configs','#'),
	2=> array('Stats','#')
);
$p = new Cgn_Menu('Site',$list2);
echo $p->toHtml();

$list3 = new Cgn_Mvc_ListModel();
$list3->data = array(
	0=> array('Sources','#'),
	1=> array('Test','#'),
	2=> array('Stats','#')
);
$p = new Cgn_Menu('Data',$list3);
echo $p->toHtml();
?>

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
