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

<p>
<h3>Content which links here...</h3>
<?php
echo $t['dataList']->toHtml();
?>
This content may need to be updated if you are changing the "link text" field of this content item.
</p>


