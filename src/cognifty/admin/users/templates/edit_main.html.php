<h3>Users</h3>

<fieldset><legend>Edit a User</legend>
<br />
<h4> Edit the User's information below  OR  
&nbsp;<a href="<?=cgn_adminurl('users','main');?>" >Cancel This</a> : </h4>
<br />
<br />
<?php
	echo $t['form01']->toHtml();
?>
<br/>
<h4>  NOTE : *  Indicates a required field. </h4>
<h4>  NOTE : If you leave the passwords blank, they WILL NOT be overwritten.
</fieldset>
<br/>
