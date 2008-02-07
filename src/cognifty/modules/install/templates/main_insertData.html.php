<h3>Almost Done</h3>
Now, setup your admin user
<br/>

<form method="POST" action="<?= cgn_appurl('install','main','setupAdmin');?>">
Admin login: <input type="text" name="adm_user" value="admin"/>
<br/>
Admin password: <input type="text" name="adm_pass" value="<?=$t['pass'];?>" />
<br/>
<input type="submit"/>
</form>
