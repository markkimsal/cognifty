<h4>Change your contact information</h4>
<hr/>

<form method="POST" action="<?=cgn_appurl('account','contact','change');?>">
<table cellpadding="0" cellspacing="0">
<tr><td style="text-align:right">
<label for="firstname">First Name:</label></td><td><input type="text" size="35" name="firstname" id="firstname" value="<?=htmlentities($t['contact']['firstname']);?>"/>
</td></tr>
<tr><td style="text-align:right">
<label for="lastname">Last Name:</label></td><td><input type="text" size="35" name="lastname" id="lastname" value="<?=htmlentities($t['contact']['firstname']);?>"/>
</td></tr>

<tr><td style="text-align:right" colspan="2">
You must supply your password to change your e-mail address.
</td></tr>
<tr><td style="text-align:right">
	<label for="email">E-Mail:</label></td><td><input type="text" size="35" name="email" id="email" value="<?=htmlentities($t['contact']['email']);?>"/>
</td></tr>
<tr><td style="text-align:right">
	<label for="password">Password:</label></td><td><input type="password" name="password" id="password"/>
</td></tr>
</table>
<input type="submit" id="sbmt_button" name="sbmt_button" value="Change Personal Info"/>
</form>
