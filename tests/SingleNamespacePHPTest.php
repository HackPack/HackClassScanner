<?hh // strict

namespace HackPack\Scanner\Tests;

class SingleNamespacePHPTest extends AbstractPHPTest
{
    public function getFilename(): string {
        return 'single_namespace_php.php';
    }

    protected function getPrefix(): string {
        return 'SingleNamespace\\';
    }
}
