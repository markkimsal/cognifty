<h3>Welcome to the Installer</h3>

You can jump right to the <?= cgn_applink('tutorial','tutorial');?>.
<br/>

<fieldset><legend>Checklist</legend>
<ol>
<li>Can write to core config file:
<?php
if ($t['core']) { echo "Yes";} else { echo "No";}
?></li>
<li>Can write to default config file:
<?php
if ($t['default']) { echo "Yes";} else { echo "No";}
?></li>
</ol>
</fieldset>
Please enter your database connection information below.
<form method="POST" action="<?= cgn_appurl('install','main','writeConf');?>">
Database login: <input type="text" name="db_user" value="root"/>
<br/>
Database password: <input type="text" name="db_pass"/>
<br/>
Database schema: <input type="text" name="db_schema" value="cognifty"/>
<br/>
Database Host: <input type="text" name="db_host" value="localhost"/>
<br/>
<input type="submit"/>
</form>
