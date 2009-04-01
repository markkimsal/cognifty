
<h2>Account Settings</h2>
<hr/>

<div class="acct-sect-hdr">Basic Information </div>
Display Name: <?=$u->getDisplayName();?>
<br/>
E-mail:       <?=$u->email;?>
<br/><a href="<?=cgn_appurl('account','contact');?>">Change your contact information</a>

<br/>
<br/>
<div class="acct-sect-hdr">Picture</div>
<img src="<?=cgn_appurl('account','img');?>"/>
<br/>
<a href="<?=cgn_appurl('account','img', 'edit');?>">Change your picture</a>

<br/>
<br/>
<div class="acct-sect-hdr">Password</div>
<a href="<?=cgn_appurl('account','password');?>">Change your password</a>

<br/>
<br/>
<div class="acct-sect-hdr">Advanced Settings</div>
<a href="<?=cgn_appurl('account','agent');?>">Setup your API agent</a>
