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
    <script language="JavaScript" src="<?=cgn_url();?>media/js/ui.tabs.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_templateurl();?>jquery.hoverIntent.minified.js" type="text/javascript"></script>
    <script language="JavaScript" src="<?=cgn_templateurl();?>jquery.megaMenu.js" type="text/javascript"></script>

</head>

<body>
<div id="outterwrapper">
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

<a href="<?=cgn_url();?>" target="_blank">View Site</a>
|
<a href="<?=cgn_adminurl('login','main','logout');?>">Sign-out</a>
	</div>
	<div class="toptitle">Cognifty Control Center</div>
	<div class="topsitename"><?php cgn_sitename();?> &mdash; <?= cgn_sitetagline();?></div>
</div>
<style type="text/css">
.nav, .nav * {
	margin:0;
	padding:0;
	list-style:none;
}
.nav {
	line-height:1.5em;
}

ul.nav li {
display:inline;
position:relative;
}
ul.nav div {
display:none;
}
ul.nav ul {
display:none;
}

ul.nav li div {
width:20em;
}

ul.nav li.hovering div {
display:block;
position:absolute;
top:1.9em;
left:-1px;
background-color:white;
text-align:left;
padding-left:1em;
padding-bottom:.5em;
	border-left:1px solid #F70;
	border-right:1px solid #F70;
	border-bottom:1px solid #F70;
	border-top:1px solid #CCC;
line-height:1.2em;
}

ul.nav li.hovering div h3 {
margin-top:.5em;
padding:0;
}
ul.nav li.hovering div a {
font-weight:normal
}
ul.nav li.hovering ul li a {
font-weight:normal
}


ul.nav li.hovering ul {
display:block;
position:absolute;
top:1.9em;
left:-1px;
background-color:white;
text-align:left;
padding-left:1em;
	border-left:1px solid #F70;
	border-right:1px solid #F70;
	border-bottom:1px solid #F70;
	border-top:1px solid #CCC;
}
</style>

</style>


<script type="text/javascript">
		 $(document).ready(function(){
//			 	$("ul.nav").superfish();

$("ul.nav li").hoverIntent(megaConfig);
		 });
function addMega(){
  $(this).addClass("hovering");
  }

function removeMega(){
  $(this).removeClass("hovering");
  }
var megaConfig = {     
    interval: 500, 
    sensitivity: 4, 
    over: addMega,
    timeout: 500,
    out: removeMega
};
</script>
<div id="navbar">
<ul class="nav mega" style="width:100%;" class="mega">
	<li <?if (@$t['selectedTab'] == 'mods') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'mods') echo 'class="current"'; ?>href="<?=cgn_adminurl();?>">Dashboard</a>
	</li>
	<li <?if (@$t['selectedTab'] == 'cms') echo 'class="current"'; ?>>
<h2>
		<a <?if (@$t['selectedTab'] == 'cms') echo 'class="current"'; ?> href="<?=cgn_adminurl('content');?>">Content</a>
</h2>
<div>
<h3>Pages</h3>
		<p><a href="<?=cgn_adminurl('content','web')?>">List all Pages</a></p>
<h3>Articles</h3>
<p><a href="<?=cgn_adminurl('content','articles')?>">List all Articles</a></p>
<h3>Images</h3>
		<p><a href="<?=cgn_adminurl('content','image')?>">List all Images</a></p>
<h3>Assets</h3>
		<p><a href="<?=cgn_adminurl('content','assets')?>">List all Assets</a></p>
			<h3>New Content</h3>
		<a href="<?=cgn_adminurl('content','update')?>">Upload a file</a>, 
		<a href="<?=cgn_adminurl('content','edit', '', array('m'=>'wiki'))?>">Write Wiki</a>,
		<a href="<?=cgn_adminurl('content','main')?>">More...</a>
		<p>
		</p>
</div>
	</li>
	<li <?if (@$t['selectedTab'] == 'site') echo 'class="current"'; ?>>
<h2>
		<a <?if (@$t['selectedTab'] == 'site') echo 'class="current"'; ?> href="#">Site</a>
</h2>
		<div style="width:12em;">
		<h3>Menus</h3>
		<p><a href="<?=cgn_adminurl('menus')?>">List all Menus</a></p>
		<h3>Design</h3>
		<p><a href="<?=cgn_adminurl('site','area')?>">Show site areas</a></p>
		<h3>Structure / Lists</h3>
		<p><a href="<?=cgn_adminurl('site','structure')?>">Show site structure</a></p>
		<h3>Search</h3>
		<p><a href="<?=cgn_adminurl('site','search')?>">Show Search Indexes</a></p>
		</div>
	</li>

	<li <?if (@$t['selectedTab'] == 'blog') echo 'class="current"'; ?>>
<h2>
		<a <?if (@$t['selectedTab'] == 'blog') echo 'class="current"'; ?> href="#">Blog</a>
</h2>
		<div>
<h3>Blogs</h3>
		<p><a href="<?=cgn_adminurl('blog')?>">List all blogs</a></p>
<h3>Default Blogs</h3>
		<p><a href="<?=cgn_adminurl('blog','post')?>">Create new post</a></p>
		</div>
	</li>


	<li <?if (@$t['selectedTab'] == 'system') echo 'class="current"'; ?>>
		<a <?if (@$t['selectedTab'] == 'system') echo 'class="current"'; ?> href="#">System</a>
		<div style="width:14em;">
		<h3>Message Queue</h3>
		<p><a href="<?=cgn_adminurl('mxq')?>">Show Channels</a></p>
		<h3>Undo Deletes</h3>
		<p><a href="<?=cgn_adminurl('site','garbage')?>">Trash Can</a></p>
		<h3>Module Manager</h3>
		<p><a href="<?=cgn_adminurl('mods')?>">Show all modules</a></p>
		<h3>Backup Manager</h3>
		<p><a href="<?=cgn_adminurl('backup')?>">Show back-ups</a></p>
		<h3>Database Manager</h3>
		<p><a href="<?=cgn_adminurl('adbm')?>">Run database scripts</a></p>
		</div>
	</li>


	<li <?if (@$t['selectedTab'] == 'users') echo 'class="menu_last current"'; else echo 'class="menu_last"';?>>
<h2>
		<a <?if (@$t['selectedTab'] == 'users') echo 'class="menu_last current"'; ?> href="#">Users</a>
</h2>
		<div>
<h3>Users</h3>
		<p><a href="<?=cgn_adminurl('users');?>">List users</a>, 
		<a href="<?=cgn_adminurl('users','groups');?>">Groups</a>,
		<a href="<?=cgn_adminurl('users','main','add');?>">Add a user</a></p>
<h3>Organizations</h3>
		<p><a href="<?=cgn_adminurl('users', 'org');?>">List organizations</a>,
		<a href="<?=cgn_adminurl('users','org','create');?>">Add an organization</a></p>
		</div>
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
</ul>
<div class="clearer"></div>

</div>

<br/>

<div id="tcontent">
	<div class="clearer"></div>

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
		<div id="contentcontent">
<?php
//page name
if ( $pageHeader =  Cgn_Service_Admin::getDisplayName()) { ?>
		<h2 class="header_page"><?php echo $pageHeader; ?></h2>
<?php
}
?>

			<?php Cgn_Template::showSessionMessages(); ?>
<?php
//toolbar
if (isset($t['toolbar'])) {
	echo $t['toolbar']->toHtml();
	unset($t['toolbar']);
}
?>
			<?php Cgn_Template::parseTemplateSection('content.main'); ?>
<div style="height:4em;clear:both;"></div>
		</div>
</div>

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
