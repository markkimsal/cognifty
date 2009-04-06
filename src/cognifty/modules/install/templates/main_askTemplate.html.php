<h2>Customize your site's template</h2>

<form method="POST" action="<?= cgn_appurl('install','main','writeTemplate');?>">
<fieldset><legend>Template</legend>
Enter information to customize your site's template behavior
<table border="0" cellspacing="3">
<tr><td>
	Site Name:
	</td><td>
	<input type="text" name="site_name" value="<?= htmlspecialchars($t['site_name']);?>"/>
</td></tr>
<tr><td>
	Site Tag-line:
	</td><td>
	<input type="text" name="site_tag" value="<?= htmlspecialchars($t['site_tag']);?>"/>
</td></tr>
<tr><td>
	Port for SSL/HTTPS ?
	<br/> (set to nothing for systems w/o SSL)
	</td><td>
	<input type="text" name="ssl_port" value="443"/>
</td></tr>
</table>
</fieldset>
<input type="submit" value="Save these values"/>


