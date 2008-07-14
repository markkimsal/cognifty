<?php echo $t['message'];?>
<br/>

<?php
foreach ($t['results'] as $res) {
	echo "<h2>";
	echo "<a href=\"".$res['url']."\">";
	echo $res['title'];
   	echo "</a></h2>";
	echo "<p style=\"margin-top:-5px;\">";
	echo "<a href=\"".$res['url']."\">";
	echo $res['url']."</a></p>";
}
?>


<?php
if (! count($t['results'])) {
?>
<h2>No results found.</h2>
<?php
}
?>
