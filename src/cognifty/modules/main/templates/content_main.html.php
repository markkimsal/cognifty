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


	<div class="links"><?php
	
	//print sections
	if (@is_array($t['sectionList'])) {

		echo ' &nbsp; Browse more articles [ ';
		$sections = $t['sectionList'];
		foreach ($sections as $slink => $sname) {
			echo '<a href="'.cgn_appurl('main','section','').$sname.'">'.$sname.'</a> ';
		}
		echo ' ]';
	}
?>

	</div>   
	</div>   


