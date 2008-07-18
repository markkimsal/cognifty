<?php
echo "trying to gather sql files to create PHP schema files...\n";

if (!isset($argv[1]) 
	|| ! file_exists($argv[1])) {
		//no parameter passed, show usage
		echo "You must pass a target directory as the first parameter.\n";
		echo "Example: php ./compactsql.php db_install\n";
		die();
	}
$sourceDir = $argv[1];

$schemas = array();
$cleanSchemas = array();

$data = array();
$cleanData = array();

//$filesToProcess = array();

$d = dir($sourceDir);
while ($entry = $d->read()){ 
	if (substr($entry,-4) == '.sql') {
		$files[] = $entry;
		echo $entry."\n";
	}
}
$d->close();

foreach($files as $f) {
	$setupFile = trim(file_get_contents($sourceDir.'/'.$f));
	$setupFile = str_replace("; \n", ";\n", $setupFile);
	$schemas[$f] = explode(";\n",$setupFile);
}

foreach ($schemas as $filename => $manyDefs) {
	foreach ($manyDefs as $fullDef) {
		$lines = explode("\n",$fullDef);
		$cleaner = '';
		foreach ($lines as $line) {

			if (trim($line) == '') {continue;}
			if (trim($line) == '--') {continue;}
			if (trim($line) == '#') {continue;}
			if (trim($line) == '# ') {continue;}
			if (preg_match("/^#/",trim($line))) {continue;}
			if (preg_match("/^--/",trim($line))) {continue;}

			$cleaner .= $line."\n";
		}
		$cleanSchemas[$filename][] = trim($cleaner)."\n";
	}
}

foreach ($data as $filename => $manyDefs) {
	foreach ($manyDefs as $fullDef) {
		$lines = explode("\n",$fullDef);
		$cleaner = '';
		foreach ($lines as $line) {

			if (trim($line) == '') {continue;}
			if (trim($line) == '--') {continue;}
			if (trim($line) == '#') {continue;}
			if (trim($line) == '# ') {continue;}
			if (preg_match("/^#/",trim($line))) {continue;}
			if (preg_match("/^--/",trim($line))) {continue;}

			$cleaner .= $line."\n";
		}
		$cleanData[$filename][] = trim($cleaner)."\n";
	}
}

$c = 0;
foreach ($cleanSchemas as $secionName => $section) {
	$fileContents = '';
	foreach ($section as $def) {
		if (trim ($def) == '') { continue; }
		$fileContents .= "\$table = <<<sqldelimeter\n";
		$fileContents .= $def;
		$fileContents .= "sqldelimeter;\n";
		$fileContents .= "\$installTableSchemas[] = \$table;\n";
		if (strlen($fileContents) > 40000) {
			writeOutSchemas($fileContents,++$c);
			$fileContents = '';
		}
	}
	writeOutSchemas($fileContents,++$c);
}

$c = 0;
foreach ($cleanData as $secionName => $section) {
	$fileContents = '';
	foreach ($section as $def) {
		if (trim ($def) == '') { continue; }
		$fileContents .= "\$table = <<<sqldelimeter\n";
		$fileContents .= $def;
		$fileContents .= "sqldelimeter;\n";
		$fileContents .= "\$installTableSchemas[] = \$table;\n";
		if (strlen($fileContents) > 40000) {
			writeOutSchemas($fileContents,++$c,'data_');
			$fileContents = '';
		}
	}
	writeOutSchemas($fileContents,++$c,'data_');
}




function writeOutSchemas($contents, $c, $prefix='schema_') {
	$fileContents = '';
	$fileContents = "<?\n";
	$fileContents .= "\$installTableSchemas = array();\n";
	$fileContents .= $contents;

	$fileContents .= "\n?>";
	$f = fopen('./compact/'.$prefix.sprintf('%02d',$c).'.php','w');
	fputs($f,$fileContents);
	fclose($f);
}

?>
