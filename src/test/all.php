<?php

define('BASE_DIR',dirname(__FILE__).'/');
chdir(BASE_DIR);
require(BASE_DIR."../boot/bootstrap.php");

if (! defined('SIMPLE_TEST')) {
	define('SIMPLE_TEST', 'simpletest/');
}

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');

$test = &new GroupTest('All tests');

$f = glob("test_*.php");
foreach($f as $name) { 
	preg_match("/test_(.*).php/",$name,$match);
	$className = "Test".$match[1];
	require_once($name);
	$test->addTestCase(new $className());
}

if (php_sapi_name()=='cli') { 
	$test->run(new TextReporter());
} else {
	$test->run(new HtmlReporter());

}

#print_r(get_included_files());
?>
