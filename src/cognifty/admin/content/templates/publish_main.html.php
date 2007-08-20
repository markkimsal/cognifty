<h3><?=$t['data']['title'];?></h3>
<span style="font-size:75%;"><?=$t['data']['caption'];?></span>

<br />
<?php

	if ($t['data']['sub_type'] == '') {
		 echo $t['publishForm']->toHtml(); 
	 } else {
		 echo $t['republishForm']->toHtml();
	 }
?>
