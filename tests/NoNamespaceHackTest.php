<?hh // strict

namespace HackPack\Scanner\Tests;

class NoNamespaceHackTest extends AbstractHackTest
{
    public function getFilename(): string {
        return 'no_namespace_hack.php';
    }

    protected function getPrefix(): string {
        return '';
    }
}
