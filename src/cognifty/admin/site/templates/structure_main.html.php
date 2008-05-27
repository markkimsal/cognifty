<h2>Site Structure</h2>
<h4>(bread crumbs)</h4>

<link type="text/css" rel="stylesheet" href="<?=cgn_url();?>media/js/yui/build/treeview/assets/skins/sam/treeview.css"> 

<style type="text/css">
.icon-ppt { padding-left: 20px; background: transparent url(<?=cgn_templateurl();?>images/treesprite.png) 0 0px no-repeat; }
.icon-dmg { padding-left: 20px; background: transparent url(<?=cgn_templateurl();?>images/treesprite.png) 0 -36px no-repeat; }
.icon-prv { padding-left: 20px; background: transparent url(<?=cgn_templateurl();?>images/treesprite.png) 0 -72px no-repeat; }
.icon-gen { padding-left: 20px; background: transparent url(<?=cgn_templateurl();?>images/treesprite.png) 0 -108px no-repeat; }
.icon-doc { padding-left: 20px; background: transparent url(<?=cgn_templateurl();?>images/treesprite.png) 0 -144px no-repeat; }
.icon-jar { padding-left: 20px; background: transparent url(<?=cgn_templateurl();?>images/treesprite.png) 0 -180px no-repeat; }
.icon-zip { padding-left: 20px; background: transparent url(<?=cgn_templateurl();?>images/treesprite.png) 0 -216px no-repeat; }
.selectedTreeRow:link{ background-color:#EEEEFF;padding:1px;border :1px dashed navy;font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;-moz-user-select: none;  }
</style>

<div id="spacer01" style="width:40%;margin-right:2em;float:right;">
	<table summary="attributes for content node">
		<tr><td>Title: </td><td><?=$t['contentTitle'];?></td></tr>
		<tr><td colspan="2">
			<a href="<?=cgn_adminurl('site', 'structure', 'del');?>">Remove <?=$t['contentTitle'];?> structure</a></td></tr>
<!--
		<tr><td>Site Area: </td><td>Default</td></tr>
		<tr><td>Number of Children: </td><td>0</td></tr>
-->
	</table>
	<hr/>
<!--
	<form method="POST" action="<?=cgn_adminurl('site','structure','add');?>">
	Add content under '<?=$t['contentTitle'];?>':
	<br/>
	<select name="struct_type">
		<option value="page">Web Page</option>
		<option value="portal">Portal Page</option>
		<option>Article</option>
		<option>Article Section</option>
		<option>File Download</option>
		<option>Web Image</option>
		<option>Web Module</option>
	</select>
	&nbsp;<input type="submit" name="sbmt-button" value="Add New"/>
	</form>
	<br/>
-->
	<form method="POST" action="<?=cgn_adminurl('site','structure','browse');?>">
	<input type="submit" name="sbmt-button" value="Add pages under selected node..."/>
	</form>
</div>


<?= $t['treeView']->toHtml(); ?>
