
<h2>Sign-in</h2>
<br/>


<form method="post" action="<?= cgn_adminurl('login','main','fakelogin', array('loginredir'=> $t['redir']));?>">
	<table cellspacing="3" cellpadding="2" border="0">
		<tr>
			<td><h4>What is your e-mail address?</td>
			<td>
				<? if ($t['username'] ) { ?>
				<b><?= $t['username']?></b>
				<input type="hidden" name="email" value="<?=$t['username']?>">
				<? } else { ?>
				<input type="text" name="email" size="20" maxlength="35">
				<? } ?>
			</td>
		</tr>
		<tr>
			<td><h4>What is your password?</h4></td>
			<td>
				<input type="password" name="password" size="20" maxlength="35" onfocus="document.getElementById('hp_yes').checked = true;">
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="submit" value="Sign-in">
			</td>
		</tr>
<? if ($t['username'] ) { ?>
<tr><td colspan="2">	
				Sign-in as a <a href="<?=appurl("login/main/clear=y");?>">different user</a>
				</td></tr>
<? } ?>
	</table>
</form>


<p>&nbsp;</p>


<p>
<!--
<form method="post" action="<?=cgn_appurl("login");?>">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="reglogin"><h3>Lost your password?</h3>
If you've lost your password, please enter your email address below and we'll email your password to your email address.
<BR>
<table class="bodycopy">
<tr>
<td>Email</td>
<td><input type="text" name="email" size="25" maxlength="50"></td>
</tr>
</table>
<input type="submit" value="Send me my password">
<input type="hidden" name="event" value="lost">

</td>
</tr>
</table>
</form>
-->


