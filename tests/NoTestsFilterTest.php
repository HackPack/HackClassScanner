<?hh // strict

use FredEmmott\DefinitionFinder\NoTestsFilter;
use FredEmmott\DefinitionFinder\TreeDefinitions;
use FredEmmott\DefinitionFinder\TreeWalker;

class NoTestsFilterTest
{
  //private static function GetUnfilteredDefinitions(): TreeDefinitions {
    //return new TreeWalker(realpath(__DIR__.'/../'));
  //}

  //private static function GetFilteredDefinitions(): TreeDefinitions {
    //return NoTestsFilter::Filter(self::GetUnfilteredDefinitions());
  //}

  //public function testStillContainsLibrary(): void {
    //$this->assertContains(
      //'FredEmmott\DefinitionFinder\FileParser',
      //self::GetFilteredDefinitions()->getClasses()->keys(),
    //);
  //}

  //public function testDoesNotContainTestClasses(): void {
    //$this->assertContains(
      //__CLASS__,
      //self::GetUnfilteredDefinitions()->getClasses()->keys(),
    //);
    //$this->assertNotContains(
      //__CLASS__,
      //self::GetFilteredDefinitions()->getClasses()->keys(),
    //);
  //}
}
