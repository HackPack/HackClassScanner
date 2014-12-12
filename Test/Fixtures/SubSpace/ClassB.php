<?hh // strict

namespace \kilahm\Random\NS;

enum WithClass : string
{
    messItUp = ClassB::class;
}

class ClassB
{
    private function a() : void
    {
        ClassB::class;
    }
}
