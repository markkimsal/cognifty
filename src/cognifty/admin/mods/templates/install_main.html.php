<h2>Installation/Upgrade of module <?= ucfirst($t['mid']);?></h2>
<h4>From: <?= $t['oldversion'];?></h4>
<h4>To: <?= $t['newversion'];?></h4>
Installation requires the following tasks:
<?php
foreach ($t['tasks'] as $task) {
?>
<div style="border:1px solid black;">
<?php
	$iconUrl = ($task['status'] == 'notdone')? 'caution_24.png': 'bool_yes_24.png';
?>
	<img src="<?=cgn_url()?>media/icons/default/<?=$iconUrl;?>">

<?php echo '<span style="mod-task-name">'.$task['name']. ':</span><br/> <span style="mod-install-desc">' .$task['description'].'</span>'; ?>
</div>

<?php
}
?>

	<a href="<?=cgn_adminurl('mods', 'install', 'step', array($t['midamid']=>$t['mid']));?>">Proceed with installation.</a>
