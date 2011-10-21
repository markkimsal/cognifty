<h2>Dashboard</h2>
<br/>


<div style="width:47%;text-align:left;float:left;">
<fieldset class="colorset1">
<legend>Recent Activity</legend>
<?php
if (isset($t['lastActivityWarn'])) {
	echo $t['lastActivityWarn'];
}?>
<ol>
<?php
foreach ($t['lastActivity'] as $act) {
	$recordedOn = time()-$act['recorded_on'];
	$units = 'sec';
	if ($recordedOn > 60 ) {
		$recordedOn = $recordedOn / 60;
		$units = 'min';
	}
	echo "<li>(".$act['ip_addr'].") ". sprintf('%d', $recordedOn)." ".$units.". ago <br/>".$act['url']."</li>";
}
?>
</ol>
</fieldset>


</div>



<?php foreach ($t['modDashboardList'] as $_modIdx => $_mod): ?>

<?php $align =  ($_modIdx % 2) ? 'left':'right'; ?>


	<div style="text-align:<?php echo $align;?>;float:<?php echo $align;?>;width:47%;">
	<fieldset class="colorset1">
	<legend><?php echo ucfirst($_mod->codeName);?></legend>

	<br/>
	<?php
	$mse = explode('.', $_mod->config['dashboard_portal_mse']);
		if (is_object($t[ 'dashboard_'.$mse[0] ])) {
			echo $t[ 'dashboard_'.$mse[0] ]->toHtml();
		} else {
			echo $t[ 'dashboard_'.$mse[0] ];
		}
	?>
	</fieldset>
</div>
<?php endforeach;?>

