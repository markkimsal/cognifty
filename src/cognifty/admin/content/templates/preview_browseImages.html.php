<div style="color:#333;">
<form method="GET" action="<?=$t['urlBase'];?>">
Page <input style="padding:0em .2em;width:1.4em;" type="text" size="1" name="p" value="<?=$t['curPage'];?>"/> of <?= $t['maxPage']; ?> | 
<a href="<?=$t['urlPrev'];?>">Prev</a>
<a href="<?=$t['urlNext'];?>">Next</a>
</form>
<br/>
<?php
	foreach ($t['data'] as $datum) {
?>
		<div style="float:left;text-align:left;margin-right:2em;z-index:2">
		<img height="80" src="<?=cgn_adminurl('content','preview','showImage',array('id'=>$datum['cgn_image_publish_id']));?>" style="text-align:center;"/>

		</div>
		<div style="float:left;text-align:left;padding-left:1em;z-index:1;overflow:hidden;">
		<span style="font-size:120%">
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

		<ul style="line-height:1.8em;">
			<li>
		<a onclick="parent.closeEmbedPanel();window.setTimeout('parent.insertImage(\'<?=$datum['link_text'];?>\',\'<?=$datum['cgn_content_id'];?>\', \'<?php echo htmlspecialchars($datum['caption'], ENT_QUOTES);?>\');', 300);"  href="#">Web Size Image</a>
			</li>

			<li>
			<a onclick="parent.closeEmbedPanel();window.setTimeout('parent.insertImageThm(\'<?=$datum['link_text'];?>\',\'<?=$datum['cgn_content_id'];?>\', \'<?php echo htmlspecialchars($datum['caption'], ENT_QUOTES);?>\');', 300);"  href="#">Thumbnail Size Image</a>
			</li>
		</ul>

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
</div>
