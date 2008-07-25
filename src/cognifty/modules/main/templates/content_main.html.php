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





	<h3 style="margin:0;"><?= $t['title'];?></h3>

	<?php if($t['caption'] != '') {  ?>
	<span style="padding-left:1em;font-size:90%;"><?= $t['caption'];?></span>
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



	<div name="upper" class="content_wrapper">
	<?php echo $t['content'];?>
	</div>   

	
<?php
if ($t['hasPages'] && $t['nextPageIdx'] != -1) { ?>
<p class="content_links">
Next Page: <a href="<?=cgn_appurl('main','content').$t['article']->link_text.'/p='.($t['nextPageIdx']+1);?>"><?= $t['nextPages'][($t['nextPageIdx']-1)];?></a>
</p>
<?php } ?>
