<h2>Installation/Upgrade of module <?= ucfirst($t['mid']);?></h2>
<h4>From: <?= $t['oldversion'];?></h4>
<h4>To: <?= $t['newversion'];?></h4>

	<a href="<?=cgn_adminurl('mods', 'install', 'step', array($t['midamid']=>$t['mid'], 'step'=> $t['step']+1));?>">Proceed with installation.</a>
<?php //show failure option to skip step
if (isset($t['failure'])) {
?>
	<br style="clear:both;"/>
	<span style="font-size:120%"><a href="<?=cgn_adminurl('mods', 'install', 'step', array($t['midamid']=>$t['mid'], 'step'=> $t['step']+2));?>">Skip this step!</a></span>
<?php
}
?>
	<br style="clear:both;"/>
	<br style="clear:both;"/>

	<div style="text-align:center;">
	Installation requires the following tasks:
	</div>
<?php
$stepCount = 0;
foreach ($t['tasks'] as $task) {
?>
<div class="info-block" >
<?php
	$iconUrl = ($task['status'] == 'notdone')? 'caution_24.png': 'bool_yes_24.png';
?>
	<img src="<?=cgn_url()?>media/icons/default/<?=$iconUrl;?>">

<?php echo '<span style="mod-task-name">'.$task['name']. ':</span><br/> <span style="mod-install-desc">' .$task['description'].'</span>'; ?>

<? //print logged output
	if ($stepCount == (int)$t['step']) {
		echo '<pre style="padding:3px;margin:2em;white-space:pre;border:1px solid black;">'.$t['logOut'].'</pre>';
	}
?>
</div>

<?php
	$stepCount++;
}
?>

<br style="clear:both;"/>
	<a href="<?=cgn_adminurl('mods', 'install', 'step', array($t['midamid']=>$t['mid'], 'step'=> $t['step']+1));?>">Proceed with installation.</a>

