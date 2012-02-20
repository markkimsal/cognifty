
<form method="POST" action="<?= cgn_appurl('install','main', 'checkDb');?>">
<fieldset><legend>Database</legend>

<span class="label warning">Notice:</span>
<span>
This script will attempt to create the necessary databases or schemas.  If this procedure fails you should ensure that you have created the necessary database schemas before trying this step a second time.
</span>

<table border="0" cellspacing="3">
<tr><td>
	Connection name: 
	</td><td>
	 <span class="uneditable-input">default.uri</span>
	</td>
</tr>
<tr><td>
	Connection driver: 
	</td><td>
	<select name="db_driver">
		<option value="mysql">MySQL</option>
		<option value="pdolite">PDO Sqlite</option>
	</select>
</td></tr>
<tr><td>
	Database login: 
	</td><td>
	<input type="text" name="db_user" value="root"/>
</td></tr>
<tr><td>
	Database password: 
	</td><td>
	<input type="text" name="db_pass"/>

</td></tr>
<tr><td>
	Database schema: 
	</td><td>
	<input type="text" name="db_schema" value="cognifty"/>
</td></tr>
<tr><td>
	Database Host: 
	</td><td>
	<input type="text" name="db_host" value="localhost"/>

</td></tr>
</table>
</fieldset>
<input type="submit" class="btn primary" value="Only use the default database"/>

<div class="clear">&nbsp;</div>
<fieldset><legend>Extra Connections</legend>
<span>
If you wish to have access to extra database connections you can configure them below.
<table border="0" cellspacing="3">
<tr><td>
	Connection name: 
	</td><td>
	<input type="text" name="db2_uri" value="extra.uri"/>
</td></tr>
<tr><td>
	Connection driver: 
	</td><td>
	<select name="db2_driver">
		<option value="mysql">MySQL</option>
		<option value="pdolite">PDO Sqlite</option>
	</select>
</td></tr>
<tr><td>
	Database login: 
	</td><td>
	<input type="text" name="db2_user" value=""/>
</td></tr>
<tr><td>
	Database password: 
	</td><td>
	<input type="text" name="db2_pass"/>
</td></tr>
<tr><td>

	Database schema: 
	</td><td>
	<input type="text" name="db2_schema" value="other"/>

</td></tr>
<tr><td>
	Database Host: 
	</td><td>
	<input type="text" name="db2_host" value=""/>

</td></tr>
</table>
</fieldset>
<input type="submit" class="btn primary" value="Create both database connections"/>
</form>
