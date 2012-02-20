<!--
<h2>Customize your site's template</h2>
-->

<?php echo $t['form']->toHtml(); ?>
<!--

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


<h2>Set default e-mail addresses</h2>

<form method="POST" action="<?= cgn_appurl('install','main','writeTemplate');?>">
<fieldset><legend>E-mail</legend>
Certain parts of the system behave better with deafult e-mail addresses.
<table border="0" cellspacing="3">
<tr><td>
	Contact-Us:
	</td><td>
	<input type="text" name="email_contact_us" value="<?= htmlspecialchars($t['email_contact_us']);?>"/>
</td></tr>
<tr><td>
	Default From:
<br/> (ex: noreply@example.com)
	</td><td>
	<input type="text" name="email_default_from" value="<?= htmlspecialchars($t['email_default_from']);?>"/>
</td></tr>
<tr><td>
	Error Notify:
<br/> (developer or e-mail list)
	</td><td>
	<input type="text" name="email_error_notify"  value="<?= htmlspecialchars($t['email_error_notify']);?>"/>
</td></tr>
</table>
</fieldset>

<input type="submit" class="btn primary" value="Save these values"/>


-->
