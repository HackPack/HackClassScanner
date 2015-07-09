<?hh // strict

namespace HackPack\Scanner;

final class ClassScanner
{
    private bool $findClasses = false;
    private bool $findInterfaces = false;
    private Vector<(function (string) : bool)> $fileFilters = Vector{};
    private Map<DefinitionType, Vector<(function (string) : bool)>> $definitionFilters;
    private (function(string):DefinitionFinder) $definitionFinderFactory;
    private Map<DefinitionType, Map<string, string>> $definitions;

    public function __construct(
        private \ConstSet<string> $includes,
        \ConstSet<string> $excludes = Set{},
        ?(function(string):DefinitionFinder) $definitionFinderFactory = null,
    )
    {
        // Initialize the filter container with empty lists
        $this->definitionFilters = Map{};
        $this->definitions = Map{};
        foreach(DefinitionType::getValues() as $type) {
             $this->definitionFilters->set($type, Vector{});
             $this->definitions->set($type, Map{});
        }

        // Default to the file parser if no factory given
        $this->definitionFinderFactory = $definitionFinderFactory === null ?
            (string $data) ==> new FileParser($data) :
            $definitionFinderFactory;

        // Ensure paths given exist and canonicalize them
        $this->includes = $includes
            ->filter($p ==> $p !== '' && (is_dir($p) || is_file($p)))
            ->map($p ==> realpath($p));

        // Set up a filter using excludes as base paths
        if( ! $excludes->isEmpty() ) {
            $excludes = $excludes
                ->filter($p ==> $p !== '' && (is_dir($p) || is_file($p)))
                ->map($p ==> realpath($p));
            $this->addFileNameFilter($fname ==> {
                foreach($excludes as $path) {
                    if(strpos($fname, $path) !== false) {
                         return false;
                    }
                }
                return true;
            });
        }

        // Kick process the input
        $this->process();
    }

    public function addDefinitionNameFilter(
        DefinitionType $type,
        (function (string) : bool) $filter,
    ) : this
    {
        $this->definitionFilters->at($type)->add($filter);
        return $this;
    }

    public function addDefinitionNameFilters(
        DefinitionType $type,
        Traversable<(function (string) : bool)> $filters,
    ) : this
    {
        $this->definitionFilters->at($type)->addAll($filters);
        return $this;
    }

    public function addFileNameFilter((function (string) : bool) $filter) : this
    {
        $this->fileFilters->add($filter);
        return $this;
    }

    public function addFileNameFilters(Traversable<(function (string) : bool)> $filters) : this
    {
        $this->fileFilters->addAll($filters);
        return $this;
    }

    public function mapClassToFile() : Map<string,string>
    {
        return $this->mapDefinitionsToFile(Vector{DefinitionType::CLASS_DEF});
    }

    public function mapClassOrInterfaceToFile() : Map<string,string>
    {
        return $this->mapDefinitionsToFile(Vector{
            DefinitionType::CLASS_DEF,
            DefinitionType::INTERFACE_DEF,
        });
    }

    public function mapDefinitionToFile(DefinitionType $type) : Map<string,string>
    {
        return $this->mapDefinitionsToFile(Vector{$type});
    }

    public function mapDefinitionsToFile(Traversable<DefinitionType> $types) : Map<string,string>
    {
         $out = Map{};
         foreach($types as $type) {
             $out->setAll(
                 $this->definitions->at($type)
                 ->filter($token ==> {
                     foreach($this->definitionFilters->at($type) as $f) {
                         if( ! $f($token) ) {
                             return false;
                         }
                     }
                     return true;
                 })
             );
         }
         return $out;
    }

    private function filterFile(string $fileName) : bool
    {
        foreach($this->fileFilters as $filter) {
            if( ! $filter($fileName)){
                return false;
            }
        }
        return true;
    }

    private function process() : void
    {
        $files = Vector{};
        /* HH_FIXME[2049] no HHI */
        foreach($this->includes as $root) {
            /* HH_FIXME[2049] no HHI */
            $dit = new \RecursiveDirectoryIterator($root);
            /* HH_FIXME[2049] no HHI */
            $rit = new \RecursiveIteratorIterator($dit);
            foreach ($rit as $path => $info) {
                if ($info->isDir() || $info->isLink() || !$info->isReadable()) {
                    continue;
                }

                if($this->filterFile($path))
                    $files->add($path);
            }
        }

        $factory = $this->definitionFinderFactory;
        foreach($files as $path) {
             $this->addDefinitions($path, $factory(file_get_contents($path)));
        }
    }

    private function addDefinitions(string $path, DefinitionFinder $finder) : void
    {
        foreach($this->definitions as $type => $list) {
            $list->addAll(
                $finder->get($type)
                ->map($def ==> Pair{$def, $path})
            );
        }
    }
}
