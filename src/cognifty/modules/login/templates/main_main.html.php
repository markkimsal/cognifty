
<h2>Sign-in</h2>
<br/>


<form method="post" action="<?= cgn_appurl('login','main','login', array('loginredir'=> $t['redir']));?>">
	<table cellspacing="3" cellpadding="2" border="0">
		<tr>
			<td><h4>What is your e-mail address?</h4></td>
			<td>
				<? if ($t['username'] ) { ?>
				<b><?= $t['username']?></b>
				<input type="hidden" name="email" value="<?=$t['username']?>"/>
				<? } else { ?>
				<input type="text" name="email" size="20" maxlength="35"/>
				<? } ?>
			</td>
		</tr>
		<tr>
			<td><h4>Do you have a password?</h4></td>
			<td colspan="1">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="1"><input type="radio" id="hp_no" name="hp" checked="checked" value="no"/><label for="">No, I am a new user.</label></td>
			<td>
<input type="radio" id="hp_yes" name="hp" value="yes"/><label for="">Yes, my password is</label> <input type="password" name="password" size="20" maxlength="35" onfocus="document.getElementById('hp_yes').checked = true;"/>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="submit" value="Sign-in"/>
			<p>
				<span style="font-size:smaller"><a href="<?=cgn_appurl('login','recover','',array('redir'=>$t['redir']));?>">Password Help</a></span>
			</p>
			</td>
		</tr>
<? if ($t['username'] ) { ?>
<tr><td colspan="2">	
				Sign-in as a <a href="<?=cgn_appurl('login','main','',array('clear'=>'y'));?>">different user</a>
				</td></tr>
<? } ?>
	</table>
</form>


<p>&nbsp;</p>



