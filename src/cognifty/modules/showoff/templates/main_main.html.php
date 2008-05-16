<h3>Sandbox Module</h3>
<p>
This module is intended as a way for developers to see how Cognifty works.
By tracing the code and viewing the output at the same time, a developer can get a 
better understanding of how Cognifty libraries are intended to be used.
This page will not make sense if you are simply viewing it on-line, without a running 
installation of Cognifty.
</p>

<h3>Other Pages</h3>
<ul>
<li><a href="<?=cgn_appurl('showoff','main','format');?>">Formatting</a>
</li>
<li><a href="<?=cgn_appurl('showoff','db');?>">Database</a>
</li>
<li><a href="<?=cgn_appurl('showoff','wiki');?>">Wiki</a>
</li>
<li><a href="<?=cgn_appurl('showoff','tree');?>">Tree</a>
</li>
</ul>


<br/>


<fieldset><legend>Menu Widget</legend>
<?php

echo $t['menuPanel']->toHtml();
?>
</fieldset>

<p>Here is the code to render this page.
<br/>
<?php echo $t['code'];?>
</p>
