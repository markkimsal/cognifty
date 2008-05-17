<?php
	foreach ($t['data'] as $datum) {
?>
		<div style="float:left;text-align:left;margin-right:13px;color:#333;width:100%;">
		<img height="60" src="<?=cgn_adminurl('content','preview','showImage',array('id'=>$datum['cgn_image_publish_id']));?>" style="float:left;text-align:center;margin-right:13px;">

		<a onclick="parent.$('#container-1 ol').tabsClick(1);parent.$('#content').focus();window.setTimeout('parent.insertImage(\'<?=$datum['link_text'];?>\',\'<?=$datum['cgn_content_id'];?>\');',300);"  href="#">Web Size Image</a>

		<br/>
		<a onclick="parent.$('#container-1 ol').tabsClick(1);parent.$('#content').focus();window.setTimeout('parent.insertImageThm(\'<?=$datum['link_text'];?>\',\'<?=$datum['cgn_content_id'];?>\');',300);"  href="#">Thumbnail Size Image</a>

		<br/>
		<?=$datum['title'];?>
		</div>
		<br style=\"clear:both\"/>
		<hr/>
<?
	}
?>



