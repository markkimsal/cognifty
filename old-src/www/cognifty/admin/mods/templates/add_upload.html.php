
<pre>
<? print_r($t['newModule']);?>
</pre>

<table border="1" width="400">
	<? while (list ($section,$args) = @each($t['iniFile']) ) { ?>
	<tr>
		<td colspan="2" align="center">
			<?= $section;?>
		</td>
	</tr>
	<? for ($x=0; $x< count($args); ++$x) {?>
	<tr>
		<td>
			<?= $args[$x]['NAME'];?>
		</td>
		<td>
			<?= $args[$x]['VALUE'];?>
		</td>
	</tr>
	<? } ?>
	<? } ?>
</table>
