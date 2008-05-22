<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php cgn_sitename();?> Control Center</title>
    <link href="<?php cgn_templateurl();?>admin01-screen.css" rel="stylesheet" type="text/css" />

    <link href="<?php cgn_templateurl();?>ui.tabs.css" rel="stylesheet" type="text/css" />

    <script language="JavaScript" src="<?=cgn_templateurl();?>menu.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_templateurl();?>wiki.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_url();?>media/js/jquery-1.2.5.min.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_url();?>media/js/superfish.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_url();?>media/js/ui.tabs.js" type="text/javascript"></script>

</head>

<body>
<?php
	Cgn_Template::showErrors();
?>
<div id="topbar">

<div class="topstuff">

<!--
	<form action="<?=cgn_templateurl('adm','main','dispatch');?>">
-->
	<!--    <label for="dropdown">Module:</label> -->
<!--
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
-->
<a href="<?=cgn_adminurl('login','main','logout');?>">logout</a>
	</div>
	<div class="toptitle"><?php cgn_sitename();?> Control Center<!--<img src="<?=cgn_templateurl();?>/images/title.gif" width="543" height="44" alt="LogiCreate Control Center" />--></div>
</div>
<style type="text/css">
/*** ESSENTIAL STYLES ***/
.nav, .nav * {
	margin:0;
	padding:0;
	list-style:none;
}
.nav {
	line-height:1.0;
}
.nav ul {
	position:absolute;
	top:-999em;
}
.nav ul li,
.nav a {
	width: 100%;
	line-height:1.5em;
}
.nav li {
	float:left;
	position:relative;
	z-index:99;

	border-left:1px solid #F70;
	padding-right:1em;
	padding-left:7px;
	padding-bottom:.3em;
	margin:0;
	font-size:9pt;
	width:8em;
	text-align:center;
}
.nav li li {
	float:left;
	position:relative;
	z-index:99;

	border-left:1px solid #F70;
	padding-left:7px;
	padding-right:1em;
	padding-bottom:.3em;
	margin:0px;
	font-size:t;
	width:8em;
	text-align:left;
}


.nav a {
	display:block;
}
.nav li:hover ul,
ul.nav li.sfHover ul {
	left:-1px;
	top:1.5em;
	background-color:white;
	border-right:1px solid #F70;
	border-bottom:1px solid #F70;
}
.nav li:hover li ul,
.nav li.sfHover li ul {
	top:-999em;
}
.nav li li:hover ul,
ul.nav li li.sfHover ul {
	left:9.45em;
	top:-1px;
}
.superfish li:hover ul,
.superfish li li:hover ul {
	top: -999em;
	line-height:1.5em;
}

</style>

<script type="text/javascript">
		 $(document).ready(function(){
			 	$("ul.nav").superfish();
		 });
	/*
$(document).ready(function(){
	$(".nav")
	.superfish({
		animation : { opacity:"show", height:"show" }
	})
	.find(">li:has(ul)")
		.mouseover(function(){
			$("ul", this).bgIframe({opacity:false});
		})
		.find("a")
			.focus(function(){
				$("ul", $(".nav>li:has(ul)")).bgIframe({opacity:false});
			});
});
	 */
</script>
<div id="navbar">
<ul class="nav" style="width:100%;">
	<li <?if (@$t['selectedTab'] == 'mods') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'mods') echo 'class="current"'; ?>href="<?=cgn_adminurl('mods');?>">Modules</a>
	</li>
	<li <?if (@$t['selectedTab'] == 'cms') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'cms') echo 'class="current"'; ?> href="<?=cgn_adminurl('content');?>">Content</a>
		<ul>
		<li><a href="<?=cgn_adminurl('content','web')?>">Pages</a></li>
		<li><a href="<?=cgn_adminurl('content','articles')?>">Articles</a></li>
		<li><a href="<?=cgn_adminurl('content','image')?>">Images</a></li>
		<li><a href="<?=cgn_adminurl('content','assets')?>">Assets</a></li>
		<li><a href="<?=cgn_adminurl('content','main')?>">New Content</a></li>
		</ul>
	</li>

	<li <?if (@$t['selectedTab'] == 'site') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'site') echo 'class="current"'; ?> href="#">Site</a>
		<ul>
		<li><a href="<?=cgn_adminurl('menus')?>">Menus</a></li>
		<li><a href="<?=cgn_adminurl('site','area')?>">Site Areas</a></li>
		<li><a href="<?=cgn_adminurl('site','structure')?>">Site Structure</a></li>
		</ul>
	</li>

	<li <?if (@$t['selectedTab'] == 'blog') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'blog') echo 'class="current"'; ?> href="#">Blog</a>
		<ul>
		<li><a href="<?=cgn_adminurl('blog')?>">Manage Blogs</a></li>
		<li><a href="<?=cgn_adminurl('blog','post')?>">New Post</a></li>
		</ul>
	</li>


	<li <?if (@$t['selectedTab'] == 'system') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'system') echo 'class="current"'; ?> href="#">System</a>
		<ul>
		<li><a href="<?=cgn_adminurl('mxq')?>">MXQ</a></li>
		<li><a href="<?=cgn_adminurl('site','garbage')?>">Trash Can</a></li>
		</ul>
	</li>


	<li <?if (@$t['selectedTab'] == 'users') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'users') echo 'class="current"'; ?> href="<?=cgn_adminurl('users');?>">Users</a>
		<ul>
		<li><a href="<?=cgn_adminurl('users','groups');?>">Groups</a></li>
		<li><a href="<?=cgn_adminurl('users','main','add');?>">New User</a></li>
		</ul>
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
<div class="clearer"></div>

</div>

<br/>

<div id="tcontent">
	<div class="clearer"></div>

	<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr>
<!--
	<td width="120" valign="top">
		<div id="contentmenu">
<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_panel.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_menu.php');
?>

		</div>
		</td>
-->
		<td valign="top">
	<h3><?php echo Cgn_Service_Admin::getDisplayName(); ?></h3>
		<div id="contentcontent">
			<?php Cgn_Template::showSessionMessages(); ?>
			<?php Cgn_Template::parseTemplateSection('content.main'); ?>
		</div>
		</td></tr>
	</table>
</div>

	<div id="footer">
Release: 
<?php
	echo Cgn_SystemRunner::getReleaseNumber();
?>
	(build <?php echo  Cgn_SystemRunner::getBuildNumber(); ?>)
&nbsp;&nbsp;

	&copy; 2006 Mark Kimsal. Design inspired by <a href="http://threadbox.net/">Thread</a>.
	</div>

</body>
</html>
