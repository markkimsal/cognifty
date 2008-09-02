<h3>API agents</h3>
<p>
Your API agent allows you to interact with this Web site with external applications and scripts.
</p>

<p>
If you don't know what this feature is for, please disable it.  It can allow someone else to act 
on your behalf on this Web site.
</p>

<?php echo $t['form']->toHtml();?>


<br/>


<?php
if ($t['agentEnabled'] != 0) {
	?>

	Your Agent Key is : <?= $t['agentKey']; ?>

<br/>


To reset your key submit the following form.
<form method="post" action="<? cgn_appurl('account', 'agent', 'change'); ?>">

Force Agent Key regeneration? <input type="checkbox" name="force" value="on">
<br/>
<input type="submit" value="Change key"/>
</form>

<?php
}
?>

