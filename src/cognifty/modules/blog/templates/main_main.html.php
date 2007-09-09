<?

foreach ($t['entries'] as $key=>$entry) {
	echo '<h3>'.$entry->title.'</h3>';
	echo "<hr/>\n";
	echo $entry->content;
}
?>
