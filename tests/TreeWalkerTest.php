<?hh // strict

use \FredEmmott\DefinitionFinder\TreeWalker;

final class TreeWalkerTest
{
  //private function createWalker(
    //?(function(string):bool) $filter
  //): TreeWalker {
    //return new TreeWalker(realpath(__DIR__.'/../'), $filter);
  //}

  //public function testHasThisLibraryDefinitions(): void {
    //$w = $this->createWalker(null);
    //$this->assertContains(
      //'FredEmmott\DefinitionFinder\TreeWalker',
      //$w->getClasses()->keys(),
    //);
    //$this->assertContains(
      //'FredEmmott\DefinitionFinder\DefinitionType',
      //$w->getEnums()->keys(),
    //);
    //$this->assertContains(
      //'FredEmmott\DefinitionFinder\TreeDefinitions',
      //$w->getInterfaces()->keys(),
    //);

    //$this->assertContains(
      //realpath(__DIR__.'/../src/TreeWalker.php'),
      //$w->getClasses()['FredEmmott\DefinitionFinder\TreeWalker'],
    //);
  //}

  //public function testEachDefinitionKind(): void {
    //$w = $this->createWalker(null);
    //$this->assertContains('SimpleClass', $w->getClasses()->keys());
    //$this->assertContains('SimpleInterface', $w->getInterfaces()->keys());
    //$this->assertContains('SimpleTrait', $w->getTraits()->keys());
    //$this->assertContains('MyEnum', $w->getEnums()->keys());
    //$this->assertContains('MyType', $w->getTypes()->keys());
    //$this->assertContains('MyNewtype', $w->getNewtypes()->keys());
    //$this->assertContains('generic_function', $w->getFunctions()->keys());
    //$this->assertContains('MY_CONST', $w->getConstants()->keys());
  //}

  //public function testPathFilters(): void {
    //$w = $this->createWalker(null);
    //$this->assertContains('SimpleClass', $w->getClasses()->keys());
    //$this->assertContains('SingleNamespace\SimpleClass', $w->getClasses()->keys());

    //$w = $this->createWalker($path ==> strpos($path, 'single_namespace') === false);
    //$this->assertContains('SimpleClass', $w->getClasses()->keys());
    //$this->assertNotContains('SingleNamespace\SimpleClass', $w->getClasses()->keys());
  //}

  //public function testContainsDuplicates(): void {
    //$w = $this->createWalker(null);
    //$this->assertContains('SimpleClass', $w->getClasses()->keys());

    //$files = $w->getClasses()['SimpleClass'];
    //$data = realpath(__DIR__.'/data');
    //$this->assertContains($data.'/no_namespace_php.php', $files);
    //$this->assertContains($data.'/mixed_php_html.php', $files);
  //}
}
