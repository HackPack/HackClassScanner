<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\Scanner\DefinitionFinder;
use HackPack\Scanner\DefinitionType;

class MockDefinitionFinder implements DefinitionFinder
{
    private Vector<string> $vect = Vector{};

    public function get(DefinitionType $type) : \ConstVector<string>
    {
        return $this->vect;
    }
}
