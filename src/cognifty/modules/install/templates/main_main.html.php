<h2>Install Cognifty</h2>

<fieldset><legend>Directory Permissions Checklist</legend>
<ol>
<li>Can write to local config directory (boot/local/)?:
<br/>
<?php
if ($t['core']) { echo "<span class=\"label success\">Yes</span>";} else { echo "<span class=\"label warning\">No</span>";}
?></li>
<li>Can write to &quot;var&quot; directory (var/)?:
<br/>
<?php
if ($t['var']) { echo "<span class=\"label success\">Yes</span>";} else { echo "<span class=\"label warning\">No</span>";}
?></li>
<li>Can write to &quot;var/search_cache&quot; directory (var/)?:
<br/>
<?php
if ($t['search']) { echo "<span class=\"label success\">Yes</span>";} else { echo "<span class=\"label warning\">No</span>";}
?></li>

</ol>
</fieldset>

<?php
if ($t['complete']) {
?>
<p>
<span class="label important" >Warning</span>
It appears that an installation has already been completed.  If you wish to reset the installation
remove the file "boot/local/core.ini".
</p>
<?php
}
?>

<p>
If any of the above values are "No", you cannot proceed with the installation.
Please make the necessary permission changes and reload this page.
</p>

<?php if (!$t['core'] || !$t['search'] || !$t['var'] || $t['complete']): ?>
<form method="GET" action="<?= cgn_appurl('install', 'main');?>">
	<input type="submit"  value="Reload this page" class="btn primary"/>
</form>
<?php else: ?>
<form method="GET" action="<?= cgn_appurl('install', 'main', 'askDsn');?>">
	<input type="submit" value="Next" class="btn primary"/>
</form>
<?php endif; ?>
<!--
<fieldset><legend>Database</legend>
Please enter your database connection information below.
<form method="POST" action="<?= cgn_appurl('install','main','askDsn');?>">
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
-->

You can skip the installation and jump right to the <?= cgn_applink('tutorial','tutorial');?>.
