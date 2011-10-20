
<?php if (!$t['otherUser']): ?>
<h2>Account Settings</h2>
<?php else: ?>
<?php endif; ?>

<div style="border:1px solid black;padding:7px;">
	<div class="acct-sect-hdr" style="font-size:140%;">Public Profile</div>
	<span style="float:left;width:8.5em;">Display Name:</span>
	<span style="font-weight:bold;"><?php echo htmlspecialchars($t['displayName']);?></span>
	<br style="clear:both;"/>


	<span style="float:left;width:8.5em;">Web Site:</span>
	<span style="font-weight:bold;"><a href="<?php echo htmlspecialchars($t['profile']['ws']);?>"><?php echo htmlspecialchars($t['profile']['ws']);?></a></span>
	<br style="clear:both;"/>

	<span style="float:left;width:8.5em;">Twitter:</span>
	<?php if (strlen($t['profile']['tw'])): ?>
	<span style="font-weight:bold;"><a href="http://twitter.com/<?php echo htmlspecialchars($t['profile']['tw']);?>">@<?php echo htmlspecialchars($t['profile']['tw']);?></a></span>
	<?php endif; ?>
	<br style="clear:both;"/>

	<span style="float:left;width:8.5em;">Facebook:</span>
	<span style="font-weight:bold;"><a href="<?php echo htmlspecialchars($t['profile']['fb']);?>"><?php echo htmlspecialchars($t['profile']['fb']);?></a></span>
	<br style="clear:both;"/>

	<?php if (!$t['otherUser']): ?>
	<span style="float:left;width:8.5em;">About you:</span>
	<?php else: ?>
	<span style="float:left;width:8.5em;">About this user:</span>
	<?php endif; ?>
	<span style=""><?php echo htmlspecialchars($t['profile']['bio']);?></span>
	<br style="clear:both;"/>



	<?php if (!$t['otherUser']): ?>
	<a href="<?=cgn_appurl('account', 'profile', 'edit');?>">Change your profile</a>
	<br style="clear:both;"/>
	<?php endif; ?>

	<br style="clear:both;"/>


	<?php if (!$t['otherUser']): ?>
	<img style="float:right;vertical-align:middle;margin-right:60%;" src="<?php echo cgn_sappurl('account', 'img', '', array('r'=>rand()));?>"/>
	<?php else: ?>
	<img style="float:right;vertical-align:middle;margin-right:60%;" src="<?php echo cgn_sappurl('account', 'img', '').$t['profile']['cgn_user_id'];?>"/>
	<?php endif; ?>
	<span class="acct-sect-hdr" style="font-size:140%;">Profile Picture</span>
	<br style="clear:both;"/>

	<?php if (!$t['otherUser']): ?>
	<a href="<?=cgn_appurl('account', 'img', 'edit');?>">Change your picture</a>
	<?php endif; ?>

</div>

<?php if (!$t['otherUser']): ?>
<br style="clear:both;"/>

<h2>E-mail and Other Settings</h2>
<div style="border:1px solid black;padding:7px;">
	<div class="acct-sect-hdr" style="font-size:140%;">Account Information</div>

	<span style="float:left;width:8.5em;clear:left;">E-mail:</span>
	<span style="font-weight:bold;"><?=$u->email;?></span>
	<br style="clear:both;"/>

	<span style="float:left;width:8.5em;clear:left;">Phone:</span>
	<span style="font-weight:bold;"><?php echo $t['addrObj']->get('telephone');?></span>
	<br style="clear:both;"/>


	<a href="<?=cgn_appurl('account','contact');?>">Change your account information</a>

	<br/>
	<br style="clear:left;"/>

	<div class="acct-sect-hdr" style="font-size:140%;">Password</div>
	<a href="<?=cgn_appurl('account','password');?>">Change your password</a>

	<br/>
	<br/>
	<div class="acct-sect-hdr" style="font-size:140%;">Advanced Settings</div>
	<a href="<?=cgn_appurl('account','agent');?>">Setup your API agent</a>
</div>
<?php endif; ?>
