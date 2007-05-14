<a href="<?=cgn_appurl('showoff','main','format');?>">Formatting</a>
<br/>
<a href="<?=cgn_appurl('showoff','db');?>">Database</a>
<br/>
<a href="<?=cgn_appurl('showoff','wiki');?>">Wiki</a>


<fieldset><legend>Menu Widget</legend>
<?php

echo $t['menuPanel']->toHtml();
?>
</fieldset>

<p>Here is the code to render this page.
<br/>
<?php echo $t['code'];?>
</p>
