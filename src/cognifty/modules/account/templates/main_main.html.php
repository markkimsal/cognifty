
<h2>Profile Information</h2>

<div style="border:1px solid black;padding:7px;">
<div class="acct-sect-hdr" style="font-size:140%;">Contact Information</div>
<span style="float:left;width:8.5em;">Display Name:</span> <span style="font-weight:bold;"><?=$u->getDisplayName();?></span>
<br style="clear:both;"/>
<span style="float:left;width:8.5em;clear:left;">E-mail:</span>      <span style="font-weight:bold;"><?=$u->email;?></span>
<br style="clear:both;"/>
<a href="<?=cgn_appurl('account','contact');?>">Change your contact information</a>
</div>

<br style="clear:both;"/>

<div style="height:6em;border:1px solid black;padding:7px;overflow:hidden;">
<img style="float:right;vertical-align:middle;margin-right:60%;" src="<?=cgn_appurl('account', 'img', '', array('r'=>rand()));?>"/>
<span class="acct-sect-hdr" style="font-size:140%;">Profile Picture</span>
<br/>
<a href="<?=cgn_appurl('account', 'img', 'edit');?>">Change your picture</a>
</div>

<br style="clear:left;"/>

<h2>Account Settings</h2>
<div style="border:1px solid black;padding:7px;">
<div class="acct-sect-hdr" style="font-size:140%;">Password</div>
<a href="<?=cgn_appurl('account','password');?>">Change your password</a>

<br/>
<br/>
<div class="acct-sect-hdr" style="font-size:140%;">Advanced Settings</div>
<a href="<?=cgn_appurl('account','agent');?>">Setup your API agent</a>
</div>
