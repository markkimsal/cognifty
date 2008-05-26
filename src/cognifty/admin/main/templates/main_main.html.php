<h2>Dashboard</h2>




<div style="float:right;width:40%;">
<div style="width:100%;text-align:right;float:right;">
<fieldset>
<legend>Sessions</legend>
Last 5 min:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['recentSessions'])); ?>
<br/>
Today:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['todaySessions'])); ?>
</fieldset>
</div>

<br style="clear:right;"/>

<div style="width:100%;text-align:right;float:right;">
<fieldset>
<legend>Content</legend>
Text Content Items:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['textContent'])); ?>
<br/>
Binary Content Items:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['fileContent'])); ?>
<hr/>
All Content Items:  <?= str_replace(' ', '&nbsp;',sprintf('%\' 3d', $t['allContent'])); ?>
</fieldset>
</div>
</div>









<div style="width:45%;text-align:left;float:left;">
<fieldset>
<legend>Recent Activity</legend>
<ol>
<?php
foreach ($t['lastActivity'] as $act) {
	echo "<li>(".$act['ip_addr'].") ".$act['url']."</li> ";
}
?>
</ol>
</fieldset>
</div>



<div style="width:45%;text-align:left;float:left;clear:left;">
<fieldset>
<legend>Recent Content</legend>
<ol>
<?php
foreach ($t['lastContent'] as $act) {
	echo "<li>(".$act['sub_type'].") ".$act['title']."</li> ";
}
?>
</ol>
</fieldset>
</div>



<br style="clear:both;"/>

