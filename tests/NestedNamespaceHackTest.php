<?hh // strict

namespace HackPack\Scanner\Tests;

<<TestSuite>>
class NestedNamespaceHackTest extends AbstractHackTest
{
    public function getFilename(): string {
        return 'nested_namespace_hack.php';
    }

    protected function getPrefix(): string {
        return 'Namespaces\\AreNestedNow\\';
    }
}
