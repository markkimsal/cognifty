<h3>Password Recovery</h3>

Enter the e-mail address with which you registered.

<form method="POST" action="<?=cgn_appurl('login','recover','send');?>">
<input type="text" size="30" name="email" value=""/>
<br/>
<input type="submit" size="30" name="smb_button"/>
</form>
