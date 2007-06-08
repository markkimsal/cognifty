<?php
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');

//need to test cookies
ob_start();

$suite = new TestSuite('All tests');

$suite->addTestFile('session_test.php');
$suite->run(new HtmlReporter());
?>
