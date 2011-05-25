<?php
foreach ($t['articles'] as $idx => $articleObj) {
	if (!isset($t['readMoreList'][$idx])) {
		$readMoreLink = cgn_appurl('main','content','').$articleObj->link_text;
	} else {
		$readMoreLink = $t['readMoreList'][$idx];
	}
?>
	<div name="upper" filter="debug/debugHtml text/uc" class="content_wrapper">
	<h2 style="margin:0;"><a href="<?php echo $readMoreLink;?>"><?= $articleObj->title;?></a></h2>
	<span style="padding-left:1em;font-size:90%;"><?= $articleObj->caption;?></span>
	<br/>
	<p class="preview-paragraph">
	<?= $t['content'][$idx];?>
	</p>
	<div class="links"><a href="<?php echo $readMoreLink;?>">Read More...</a> 
	<?php
	
	//print sections
	if (@is_array($t['sectionList'][$articleObj->cgn_article_publish_id])) {

		echo ' &nbsp;|&nbsp; Browse ';
		$sections = $t['sectionList'][$articleObj->cgn_article_publish_id];
		foreach ($sections as $slink => $sname) {
			echo '<a href="'.cgn_appurl('main','section','').$slink.'">'.$sname.'</a> ';
		}
	}
?>
	</div>
<!--		 &nbsp;|&nbsp;
		submitted by <a href="#">Author</a> in <a href="#">Section1</a>
		</div>
-->
	</div>        
<?php
}
?>

<!--
	<div name="upper" filter="debug/debugHtml text/uc" class="content_wrapper">
		<h2>Lorem Ipsum &copy;</h2>
		<p>"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, 
		totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. 
		Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, 
		sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. 
		Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, 
		sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. 
		Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, 
		nisi ut aliquid ex ea commodi consequatur? 
		Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, 
		vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?" </p>
		<div class="links">submitted by <a href="#">Drugo</a> in <a href="#">Section1</a></div>
	</div>
	<div filter="text/hexColor/ff0">
		<div name="lower" filter="text/lc">Hola > lower</div>
	</div>
	<div id='date' plugin="date/show" format="m/d/Y h:i:s A">Sample date</div>

-->	
