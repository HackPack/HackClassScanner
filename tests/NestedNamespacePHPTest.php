<?hh // strict

namespace HackPack\Scanner\Tests;

class NestedNamespacePHPTest extends AbstractPHPTest {

  public function getFilename(): string {
    return 'nested_namespace_php.php';
  }

  protected function getPrefix(): string {
    return 'Namespaces\\AreNestedNow\\';
  }
}
