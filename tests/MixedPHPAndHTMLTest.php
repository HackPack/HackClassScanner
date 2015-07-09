<?hh // strict

namespace HackPack\Scanner\Tests;

<<TestSuite>>
class MixedPHPAndHTMLTest extends AbstractPHPTest {
  public function getFilename(): string {
    return 'mixed_php_html.php';
  }

  protected function getPrefix(): string {
    return '';
  }
}
