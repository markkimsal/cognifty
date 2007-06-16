<?php
echo $t['form']->toHtml();
?>

Version: <?= $t['version'];?>

<br/>
<fieldset>
<legend>Insert Other Content</legend>
<a onclick="insertTags('{{img:','}}','Image Title');return false" href="#">Web image</a>
<br/>
<a onclick="insertTags('[[',']]','Article Title');return false" href="#">link to article</a>
</fieldset>
