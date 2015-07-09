<?hh // strict

namespace HackPack\Scanner\Tests;

class SingleNamespaceHackTest extends AbstractHackTest
{
    public function getFilename(): string {
        return 'single_namespace_hack.php';
    }

    protected function getPrefix(): string {
        return 'SingleNamespace\\';
    }
}
