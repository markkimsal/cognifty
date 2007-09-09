
	<h3><?= $t['title'];?></h3>
	<?php if($t['caption'] != '') {  ?>
	<span style="font-weight:bold;"><?= $t['caption'];?></span> 
	<?php }  ?>

	<div name="upper" filter="debug/debugHtml text/uc" class="content_wrapper">
	<?php echo $t['content'];?>
	</div>   

	<?php
		if ($t['hasPages']) {
			// echo "<div style=\"float:left;border:1px solid grey;margin-top:1em;padding:2px;\">\n";
			echo "<hr>";
			echo "<div style=\"float:left;\">\n";
			echo "Page : 1 - ";
				echo '<a href="';
				echo cgn_appurl(
					'main','
					content',
					''
				);
				echo $t['article']->link_text;
				echo '" ';
				echo 'style=" line-height: 2em;"';
				echo '>';
				echo $t['article']->title;
				echo '</a>';
				echo "<br/> ";

			foreach ($t['nextPages'] as $idx=>$pageTitle) {
				echo "Page : ".($idx+2)." - ";
				echo '<a href="';
				echo cgn_appurl(
					'main','
					content',
					''
				);
				echo $t['article']->link_text.'/p='.($idx+2);
				echo '" ';
				echo 'style=" line-height: 2em;"';
				echo '>';
				echo $pageTitle;
				echo '</a>';
				echo "<br/> ";
			}
			echo "</div>\n";
		}
	?>
	
	<?php
	// COMMENTED THIS OUT IN ORDER TO USE THE PAGE STYLE CODE ABOVE - SCOTT
	/* 	if ($t['hasPages']) {
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
	*/
	// THIS CODE WAS COMMENTED OUT TO ALLOW TO USE THE TITLE VERSION CODE
	// WANTED TO HAVE THE TITLES AT THE BOTTOM OF THE PAGE FOR EACH PAGE BREAK
	// ALLOWS ME TO CREATE NEW LABELS FOR SECTIONS OF A LONG ARTICLE.....SCOTT
	?>

