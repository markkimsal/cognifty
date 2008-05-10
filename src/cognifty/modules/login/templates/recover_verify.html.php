
<p>
Your username is : <em><?= $t['username']; ?></em>
</p>

<p>
Enter your new password:
<form method="POST" action="<?=cgn_appurl('login','recover','reset','','https');?>">
<table border="0" cellspacing="0">
<tr><td>
Password:</td><td> <input type="password" size="30" name="password" value=""/>
</td></tr>
<tr><td>
Veryify:</td><td> <input type="password" size="30" name="password2" value=""/>
</td></tr>
</table>
<input type="submit" size="30" name="smb_button"/>
<input type="hidden" size="30" name="tk" value="<?=$t['tk'];?>"/>
</form>
</p>
