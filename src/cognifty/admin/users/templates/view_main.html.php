<h3>Username: <?= $t['users']->username;?></h3>
Email:  <?= $t['users']->email;?>
<br/>
User ID:  <?= $t['users']->cgn_user_id;?>

<br/>
<br/>
Belongs to group(s):
<?php
if ( is_array($t['groups']) ) {
?>
<ul>
	<?php
	foreach ($t['groups'] as $grp) {
		echo "<li>".$grp['display_name']."</li>\n";
	}
?>
</ul>
<?php
}
?>
