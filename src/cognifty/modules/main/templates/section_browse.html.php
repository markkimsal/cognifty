<h2>All Articles</h2>
<hr/>

<?php
foreach ($t['sections'] as $_sec): ?>

<h3 style="margin-top:1em;"><a href="<?=cgn_appurl('main','section'). $_sec->link_text;?>"><?= htmlentities($_sec->title); ?></a></h3>
	<?php
	foreach ($t['articles'][$_sec->cgn_article_section_id] as $_art): ?>
		<ul>
		<li><a href="<?=cgn_appurl('main','content'). $_art->link_text;?>"><?= htmlentities($_art->title); ?></a></li>
		</ul>
	<?php endforeach; ?>

<span style="font-size:small;">Browse all in &quot;<a href="<?=cgn_appurl('main','section'). $_sec->link_text;?>"><?= htmlentities($_sec->title); ?></a>&quot;</span>

<?php endforeach; ?>
