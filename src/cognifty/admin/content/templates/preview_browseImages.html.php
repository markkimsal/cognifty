<form method="GET" action="<?=$t['urlBase'];?>">
Page <input style="padding:0em .2em;width:1.4em;" type="text" size="1" name="p" value="<?=$t['curPage'];?>"/> of <?= $t['maxPage']; ?> | 
<a href="<?=$t['urlPrev'];?>">Prev</a>
<a href="<?=$t['urlNext'];?>">Next</a>
</form>
<br/>
<?php
	foreach ($t['data'] as $datum) {
?>
		<div style="float:left;text-align:left;color:#333;width:160px;">
		<img height="60" src="<?=cgn_adminurl('content','preview','showImage',array('id'=>$datum['cgn_image_publish_id']));?>" style="float:left;text-align:center;margin-right:13px;"/>

		</div>
		<div style="text-align:left;margin-left:63px;color:#333;">
		<span style="font-size:150%">
		<?=$datum['title'];?>
		</span>
<?php
		if (isset($datum['caption']) && !empty($datum['caption'])) {
?>
		<br/>
		<?=$datum['caption'];?>
<?php
		}
?>
		<br/>

		<a onclick="parent.closeEmbedPanel();window.setTimeout('parent.insertImage(\'<?=$datum['link_text'];?>\',\'<?=$datum['cgn_content_id'];?>\');',300);"  href="#">Web Size Image</a>

		<br/>
		<a onclick="parent.closeEmbedPanel();window.setTimeout('parent.insertImageThm(\'<?=$datum['link_text'];?>\',\'<?=$datum['cgn_content_id'];?>\');',300);"  href="#">Thumbnail Size Image</a>

		</div>

		<br style="clear:both"/>
		<hr/>
<?
	}
?>
<br/>
<form method="GET" action="<?=$t['urlBase'];?>">
Page <input style="padding:0em .2em;width:1.4em;" type="text" size="1" name="p" value="<?=$t['curPage'];?>"/> of <?= $t['maxPage']; ?> | 
<a href="<?=$t['urlPrev'];?>">Prev</a>
<a href="<?=$t['urlNext'];?>">Next</a>
</form>
