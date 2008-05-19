<h3>Welcome to the Installer</h3>

You can jump right to the <?= cgn_applink('tutorial','tutorial');?>.
<br/>

<fieldset><legend>Checklist</legend>
<ol>
<li>Can write to local config directory (boot/local/)?:
<br/>
<?php
if ($t['core']) { echo "Yes";} else { echo "No";}
?></li>
<li>Can write to &quot;var&quot; directory (var/)?:
<br/>
<?php
if ($t['var']) { echo "Yes";} else { echo "No";}
?></li>

</ol>
</fieldset>

<?php
if ($t['complete']) {
?>
<p>
<span style="color:red">Warning</span>
<br/>
It appears that an installation has already been completed.  If you wish to reset the installation
remove the file "boot/local/core.ini".
</p>
<?php
}
?>

<p>
If any of the above values are "No", you cannot proceed with the installation.
Please make all the necessary changes before continuing with the installation.
</p>

<fieldset><legend>Database</legend>
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
<input type="submit" <? if (!$t['core'] || !$t['var'] || $t['complete']) { echo "DISABLED=\"DISABLED\"";}?> value="Next"/>
</form>
</fieldset>
