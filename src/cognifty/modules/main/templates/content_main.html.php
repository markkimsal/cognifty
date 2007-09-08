
	<?php
		if ($t['hasPages']) {
			echo "<div style=\"float:right;border:1px solid grey;margin-top:1em;padding:2px;\">\n";
			echo "Page 1:&nbsp; ";
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
				echo "Page ".($idx+2).":&nbsp; ";
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

			echo "</div>\n";
		}
	?>
	<h3><?= $t['title'];?></h3>

	<div name="upper" filter="debug/debugHtml text/uc" class="content_wrapper">
	<?php if($t['caption'] != '') {  ?>
	<span style="padding-left:1em;font-size:90%;"><?= $t['caption'];?></span> 
	<?php }  ?>


	<?php echo $t['content'];?>
	</div>   

	<?php
		if ($t['hasPages']) {
			echo '<a href="';
			echo cgn_appurl(
				'main','
				content',
				''
			);
			echo $t['article']->link_text;
			echo '">Page&nbsp;1</a>&nbsp;|&nbsp;';

			$pagesArray = array();
			foreach ($t['nextPages'] as $idx=>$pageTitle) {
				$pageName =  "Page&nbsp;".($idx+2);
				$link = '<a href="';
				$link .= cgn_appurl(
					'main','
					content',
					''
				);
				$link .= $t['article']->link_text.'/p='.($idx+2);
				$link .= '">'.$pageName.'</a>';

				$pagesArray[] = $link;
			}
			echo implode('&nbsp;|&nbsp;', $pagesArray);
		}
	?>

