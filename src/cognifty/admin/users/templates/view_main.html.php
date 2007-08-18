<h2>View Users</h2>
<br />

<table  width="auto" cellpadding="3" cellspacing="3" border="2" bgcolor="#EEEEEE">
<tr><td>

	<table align="left" cellpadding="0" cellspacing="0" style="border: 1px solid #C0C0C0; background-color: #EEEEEE ;" >
	<tr><td nowrap>

	<?php
		echo '<table  width="600px" border="0px" cellpadding="3" cellspacing="3">'."\n";
		echo '<tr>'."\n";
		echo '<td colspan="2">&nbsp;</td>'."\n";
		echo '</tr><tr>'."\n";
		echo '<td align="right">Record :</td><td align="left">'.$t['users']->cgn_user_id.'</td>'."\n";
		echo '</tr><tr>'."\n";
		echo '<td width="200px" align="right" nowrap>Username :</td><td align=\"left\"><h3>'.$t['users']->username.'</h3></td>'."\n";
		echo '</tr><tr>'."\n";
		echo '<td align="right">Email :</td><td align="left"><h3>'.$t['users']->email.'</h3></td>'."\n";
		echo '</tr>'."\n";
		if (is_array($t['groups'])) {
			echo '<tr><td align="right">Belongs to group(s):</td><td align="left" style="font-weight:bold;">'."\n";
			foreach ($t['groups'] as $grp) {
				echo $grp['display_name']."</td>\n";
				echo '</tr><tr><td align="right">&nbsp;</td><td align="left" style="font-weight:bold;">'."\n";
			}
		} else {
			echo '<tr><td align="right">Belongs to group(s):</td>'."\n";
			echo '<td align="left" style="font-weight:bold;">NONE</td>'."\n";
			echo '</tr>'."\n";
		}
		echo '<tr>'."\n";
		echo '<td align="right" colspan="2">';
		echo '<form >'."\n";
		echo '<input style="width:7em;" class="formbutton" type="button" name="viewuser01_cancel" onclick="javascript:history.go(-1);" value="Return"/>';
		echo "\n";
		echo '</form >'."\n";
		echo '</td>'."\n";
		echo '</tr>'."\n";
		echo '</table>'."\n";
	?>

	</td></tr>
	</table>

</td></tr>
</table>