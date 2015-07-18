<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\Scanner\DefinitionFinder;
use HackPack\Scanner\NameType;

class MockDefinitionFinder implements DefinitionFinder
{
    private Vector<string> $vect = Vector{};

    public function get(NameType $type) : \ConstVector<string>
    {
        return $this->vect;
    }
}
