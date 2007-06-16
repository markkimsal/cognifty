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
<br/>
<a onclick="insertTags('<p style=&quot;page-break-before: always&quot;></p>','','');return false" href="#">Page break</a>
</fieldset>
