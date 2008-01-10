<?php
require_once('simpletest/unit_tester.php');
//require_once('simpletest/web_tester.php');
require_once('simpletest/reporter.php');
require_once('fancyreporter.php');
require_once('simpletest/mock_objects.php');

define('BASE_DIR',dirname(__FILE__).'/../src/www/');
chdir(BASE_DIR);
require_once('../boot/bootstrap.php');

$dsnPool =& Cgn_ObjectStore::getObject('object://defaultDatabaseLayer');

//need to test cookies
ob_start();

$suite = new TestSuite('All tests');

//$suite->addTestFile('../../tests/webtest.php');
$suite->addTestFile('../../tests/session_test.php');
$suite->addTestFile('../../tests/login_test.php');
$suite->addTestFile('../../tests/error_test.php');
$suite->addTestFile('../../tests/content_test.php');

//$suite->run(new FancyHtmlReporter());
$suite->run(new TextReporter());

/*
$h = fopen('../../tests/results.html','w');
if (!$h) { echo ob_get_contents(); exit(); }
fputs($h, ob_get_contents());
ob_end_clean();
fclose($h);
echo "output written to results.html\n";
// */
ob_end_flush();
?>
