<?php
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');

//need to test cookies
ob_start();

$suite = new TestSuite('All tests');

$suite->addTestFile('session_test.php');
$suite->run(new HtmlReporter());

$h = fopen('results.html','w');
fputs($h, ob_get_contents());
ob_end_clean();
fclose($h);
echo "output written to results.html\n";
?>
