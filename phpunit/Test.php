<?php
//require_once 'MyTest.php';
 
class MySuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        return new MySuite('MyTest');
    }
 
    protected function setUp()
    {
        print "\nMySuite::setUp()";
    }
 
    protected function tearDown()
    {
        print "\nMySuite::tearDown()";
    }
}
?>
