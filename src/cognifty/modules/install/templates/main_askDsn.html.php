<h2>Notice</h2>
<p>
You should ensure that you have created the necessary database schemas before proceeding.
</p>

<form method="POST" action="<?= cgn_appurl('install','main','writeConf');?>">
<fieldset><legend>Database</legend>
Please enter your database connection information for the <b>default</b> database connection.
<table border="0" cellspacing="3">
<tr><td>
	Connection name: 
	</td><td>
	default.uri
</td></tr>
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
<input type="submit" value="Only use the default database"/>

<fieldset><legend>Extra Connections</legend>
If you wish to configure extra database connections at this time you can do so below.
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
<input type="submit" value="Add both of these database connections"/>
</form>
