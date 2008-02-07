	<?php
//table of contents
		$toc = '';
		if ($t['hasPages']) {
//			$toc .= "<div style=\"float:left;border:1px solid grey;margin-top:1em;padding:2px;\">\n";
//			$toc .= "<div style=\"float:left;\">\n";
			$toc .= "Page : 1 - ";
				$toc .= '<a href="';
				$toc .= cgn_appurl(
					'main','
					content',
					''
				);
				$toc .= $t['article']->link_text;
				$toc .= '" ';
				$toc .= 'style=" line-height: 2em;"';
				$toc .= '>';
				$toc .= $t['article']->title;
				$toc .= '</a>';
				$toc .= "<br/> ";

			foreach ($t['nextPages'] as $idx=>$pageTitle) {
				$toc .= "Page : ".($idx+2)." - ";
				$toc .= '<a href="';
				$toc .= cgn_appurl(
					'main','
					content',
					''
				);
				$toc .= $t['article']->link_text.'/p='.($idx+2);
				$toc .= '" ';
				$toc .= 'style=" line-height: 2em;"';
				$toc .= '>';
				$toc .= $pageTitle;
				$toc .= '</a>';
				$toc .= "<br/> ";
			}
//			$toc .= "</div>\n";
		}
	?>





	<h3><?= $t['title'];?></h3>



	<?php if($t['caption'] != '') {  ?>
	<span style="font-weight:bold;"><?= $t['caption'];?></span> 
	<?php }  ?>


<?php
		if ($t['hasPages']) {
?>
            <div class="box" style="margin-left:2em;float:right;width:18em;">
                <h4>Table of Contents</h4>
                <div class="contentarea">
<?php
echo $toc;
?>
                </div>
            </div>
<?php
		}
?>



	<div name="upper" filter="debug/debugHtml text/uc" class="content_wrapper">
	<?php echo $t['content'];?>
	</div>   

	
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

