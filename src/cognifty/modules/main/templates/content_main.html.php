	<div name="upper" filter="debug/debugHtml text/uc" class="content_wrapper">
	<h2><?= $t['title'];?></h2>
	<?php if($t['caption'] != '') {  ?>
	<span style="font-size:90%;"><?= $t['caption'];?></span> 
	<?php }  ?>
	<?= $t['content'];?>
	<?php
		if ($t['hasPages']) {
			echo "<p></p>\n";
			echo "Page 1 ";
				echo '<a href="';
				echo cgn_appurl(
					'main','
					content',
					''
				);
				echo $t['article']->link_text;
				echo '">'.$t['article']->title.'</a>';
				echo "<br/> ";

			foreach ($t['nextPages'] as $idx=>$pageTitle) {
				echo "Page ".($idx+2)." ";
				echo '<a href="';
				echo cgn_appurl(
					'main','
					content',
					''
				);
				echo $t['article']->link_text.'/p='.($idx+2);
				echo '">'.$pageTitle.'</a>';
				echo "<br/> ";
			}
		}
	?>

<!--	<div class="links">
		submitted by <a href="#">Author</a> in <a href="#">Section1</a>
	</div>
 -->
	</div>   


