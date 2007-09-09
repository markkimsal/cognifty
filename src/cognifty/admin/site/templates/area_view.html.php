<h3>Site Area:&nbsp; <?php
echo $t['area']->title;
?></h3>
<ul>
<li>Site Template:&nbsp; <?php echo htmlentities($t['area']->site_template);?></li>
<li>Template Style:&nbsp; <?php echo htmlentities($t['area']->template_style);?></li>
<li>Description:&nbsp; <?php echo htmlentities($t['area']->description);?></li>
</ul>

<?php
echo $t['form']->toHtml();
?>

<br/>
<iframe id="prevframe" name="prevframe" height="600" width="700" style="display:block;" src="<?= cgn_appurl('main');?>"></iframe>
