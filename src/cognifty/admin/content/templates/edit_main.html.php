<?php
echo $t['form']->toHtml();
?>

Version: <?= $t['version'];?>

<!--
<br/>
<fieldset>
<legend>Insert Other Content</legend>
<a onclick="insertTags('{{img:','}}','Image Title');return false" href="#">Web image</a>
<br/>
<a onclick="insertTags('[[',']]','Article Title');return false" href="#">link to article</a>
<br/>
<a onclick="insertTags('<p style=&quot;page-break-before: always&quot;></p>','','');return false" href="#">Page break</a>
<br/>
<a onclick="insertTags('{{pagebreak:','}}','Title of new page');return false" href="#">Page break</a>
</fieldset>
-->


<fieldset>
<legend>Web Images</legend>
<iframe height="100" width="600" src="<?=cgn_adminurl('content','preview','images');?>"></iframe>
</fieldset>

<fieldset>
<legend>Other Articles</legend>
<iframe height="100" width="600" src="<?=cgn_adminurl('content','preview','articles');?>"></iframe>
</fieldset>


<fieldset>
<legend>Downloadable Files</legend>
<iframe height="100" width="600" src="<?=cgn_adminurl('content','preview','files');?>"></iframe>
</fieldset>
