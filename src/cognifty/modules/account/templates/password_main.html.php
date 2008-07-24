<h2>Change your password</h2>
<hr/>
<form method="POST" action="<?=cgn_appurl('account','password','change');?>">
<table cellpadding="0" cellspacing="0">
<tr><td style="text-align:right">
	<label for="oldpassword">Old Password:</label></td><td><input type="text" name="password" id="oldpassword"/>
</td></tr>
<tr><td style="text-align:right">
<label for="newpassword">New Password:</label></td><td><input type="text" name="newpassword" id="newpassword"/>
</td></tr>
<tr><td style="text-align:right">
<label for="newpassword2">Confirm Password:</label></td><td><input type="text" name="newpassword2" id="newpassword2"/>
</td></tr>
</table>
<input type="submit" id="sbmt_button" name="sbmt_button" value="Change Password"/>
</form>
