
<h2>Sign-in</h2>
<br/>


<form method="POST" action="<?= cgn_appurl('login','main','login', array('loginredir'=> $t['redir']), 'https');?>">
	<table cellspacing="3" cellpadding="2" border="0">
		<tr>
			<td><h4 style="margin-top: 2px;">What is your e-mail address?</h4></td>
			<td valign="top">&nbsp;&nbsp;
				<? if ($t['username'] ) { ?>
				<b><?= $t['username']?></b>
				<input type="hidden" name="email" size="41" maxlength="95" value="<?=$t['username']?>"/>
				<? } else { ?>
				<input type="text" name="email" size="41" maxlength="95"/>
				<? } ?>
			</td>
		</tr>
	<?php if($t['canregister']==true){ ?>
		<tr>
			<td><h4 style="margin-top: 2px; text-align: right;">Do you have a password?</h4></td>
			<td valign="top">
				<input type="radio" id="hp_yes" name="hp" value="yes"/>
				<label for="">Yes, My password is: &nbsp;</label> 
				<input type="password" name="password" size="22" maxlength="35" onfocus="document.getElementById('hp_yes').checked = true;"/>

			</td>
		</tr>
		<?php } else { ?>
		<tr>
			<td><h4 style="margin-top: 2px; text-align: right;">Enter your password</h4></td>
			<td valign="top">&nbsp;&nbsp;
				<input type="password" name="password" size="41" maxlength="35" />
			</td>
		</tr>
		<?php } ?>
		
		<?php if($t['canregister']==true){ ?>
		<tr>
			<td>
				&nbsp;
			</td>
			<td>
				<input type="radio" id="hp_no" name="hp" checked="checked" value="no"/><label for="">No, I am a new user.</label>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2" align="right">
			<input type="submit" value="Sign-in"/><br /><br />
			<p style="float: right;">
				<span style="font-size:smaller;">
				<a href="<?=cgn_appurl('login','recover','',array('redir'=>$t['redir']));?>">Password Help</a>
				</span>
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



