
<!--
<fieldset><legend>Add Text Content</legend>
<?php
	echo '<ul><li><a href="'.cgn_adminurl('content','edit','',array('m'=>'html')).'">Add HTML content</a></li>';
	echo '<li><a href="'.cgn_adminurl('content','edit','',array('m'=>'wiki')).'">Add Wiki content</a></li></ul>';
?>
</fieldset>
<fieldset><legend>Upload Files, Documents and Images</legend>
<?php
	echo '<ul><li><a href="'.cgn_adminurl('content','upload').'">Upload a file (pdf, doc, xls, odt, etc...)</a></li>';
	echo '<li><a href="'.cgn_adminurl('content','upload').'">Upload an image (jpg, gif, png, bmp, etc...)</a></li></ul>';
?>
</fieldset>
-->

<br/>
<h3>List of Pending Content</h3>
<?php

	echo $t['form']->toHtml();
?>


