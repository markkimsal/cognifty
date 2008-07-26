<h2>Dashboard</h2>
<br/>




<div style="float:right;width:40%;">


<div style="width:100%;text-align:right;float:right;">
<fieldset class="colorset1">
<legend>Content</legend>
Text Content Items:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['textContent'])); ?>
<br/>
Binary Content Items:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['fileContent'])); ?>
<hr/>
All Content Items:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['allContent'])); ?>
</fieldset>
</div>


<br style="clear:right;"/>


<div style="width:100%;text-align:left;float:left;clear:left;">
<fieldset class="colorset1">
<legend>Recent Content</legend>
<ol>
<?php
foreach ($t['lastContent'] as $act) {
        echo "<li><a href=\"".cgn_adminurl('content','view','',array('id'=>$act['cgn_content_id']))."\">".$act['title']."</a> (".$act['sub_type'].")</li> ";
}
?>
</ol>
</fieldset>
</div>


</div>







<div style="width:45%;text-align:left;float:left;">

<div style="width:100%;text-align:right;float:right;">
<fieldset class="colorset1">
<legend>Sessions</legend>
Last 5 min:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['recentSessions'])); ?>
<br/>
Today:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['todaySessions'])); ?>
</fieldset>
</div>


<br style="clear:both;"/>


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




