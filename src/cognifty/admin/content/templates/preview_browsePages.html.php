<form method="GET" action="<?=$t['urlBase'];?>">
Page <input style="padding:0em .4em;width:1em;" type="text" size="1" name="p" value="<?=$t['curPage'];?>"/> of <?= $t['maxPage']; ?> | 
<a href="<?=$t['urlPrev'];?>">Prev</a>
<a href="<?=$t['urlNext'];?>">Next</a>
</form>
<br/>

<?php
	foreach ($t['data'] as $datum) {
		echo $datum;
		echo "\n<br style=\"clear:both\"/><hr/>\n";
	}
?>

<br/>
<form method="GET" action="<?=$t['urlBase'];?>">
Page <input style="padding:0em .4em;width:1em;" type="text" size="1" name="p" value="<?=$t['curPage'];?>"/> of <?= $t['maxPage']; ?> | 
<a href="<?=$t['urlPrev'];?>">Prev</a>
<a href="<?=$t['urlNext'];?>">Next</a>
</form>
