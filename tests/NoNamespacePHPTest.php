<?hh // strict

namespace HackPack\Scanner\Tests;

class NoNamespacePHPTest extends AbstractPHPTest
{
    public function getFilename(): string {
        return 'no_namespace_php.php';
    }

    protected function getPrefix(): string {
        return '';
    }
}
