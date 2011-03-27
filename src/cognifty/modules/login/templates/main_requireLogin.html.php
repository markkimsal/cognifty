
<div id="login_header">
<h2><?php echo Cgn_Template::siteName();?> Sign-in</h2>

<?php if($t['canregister']==true){ ?>
<p>Sign-in using an existing account or select "I am a new user." to create a new account.</p>
<?php } else { ?>
<p>Sign-in using an existing account.</p>
<?php } ?>
</div>

<form method="POST" id="login_form" action="<?= cgn_appurl('login','main','login', array('loginredir'=> $t['redir']), 'https');?>">
	<table id="login_table" cellspacing="3" cellpadding="2" border="0">
		<tr>
			<td><h4 style="margin-top: 2px;">My e-mail address is:</h4></td>
			<td valign="top">
				<? if ($t['username'] ) { ?>
				<b><?= $t['username']?></b>
				<input type="hidden" name="email" size="41" maxlength="95" value="<?=$t['username']?>"/>
				<? } else { ?>
				<input tabindex="1" type="text" name="email" size="41" maxlength="95"/>
				<? } ?>
			</td>
		</tr>
	<?php if($t['canregister']==true){ ?>
		<?php } else { ?>
		<tr>
			<td><h4 style="margin-top: 2px; text-align: right;">My password is:</h4></td>
			<td valign="top">
				<input tabindex="2" type="password" name="password" size="41" maxlength="35" />
			</td>
		</tr>
		<?php } ?>
		
		<?php if($t['canregister']==true){ ?>
		<tr>
			<td colspan="1" align="right"></td>
			<td colspan="1" align="left">
				<ul style="margin-left:-4em;list-style:none;margin-bottom:0.5em;">
				<li style="padding-bottom:0.4em;">
				<input type="radio" id="hp_no" name="hp" checked="checked" value="no" onclick="document.getElementById('password').value = '';"/>&nbsp;<label for="hp_no">I am a new user.</label>
				</li>
				<li style="padding-bottom:0.4em;">
				<input type="radio" id="hp_yes" name="hp" value="yes"/>&nbsp;<label for="hp_yes">My password is:</label> 
				</li>
				</ul>
				<input tabindex="2" type="password" id="password" name="password" size="22" maxlength="35" onfocus="document.getElementById('hp_yes').checked = true;"/>

			</td>
		</tr>

		<?php } ?>
		<tr>
			<td colspan="1" align="right"></td>
			<td colspan="1" align="left">
			<input tabindex="3" type="submit" value="Sign-in"/><br /><br />
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



