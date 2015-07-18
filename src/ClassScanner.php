<?hh // strict

namespace HackPack\Scanner;

final class ClassScanner
{
    private bool $findClasses = false;
    private bool $findInterfaces = false;
    private Vector<(function (string) : bool)> $fileFilters = Vector{};
    private Map<NameType, Vector<(function (string) : bool)>> $definitionFilters;
    private (function(string):DefinitionFinder) $definitionFinderFactory;
    private Map<NameType, Map<string, string>> $definitions;

    public function __construct(
        private \ConstSet<string> $includes,
        \ConstSet<string> $excludes = Set{},
        ?(function(string):DefinitionFinder) $definitionFinderFactory = null,
    )
    {
        // Initialize the filter container with empty lists
        $this->definitionFilters = Map{};
        $this->definitions = Map{};
        foreach(NameType::getValues() as $type) {
             $this->definitionFilters->set($type, Vector{});
             $this->definitions->set($type, Map{});
        }

        // Default to the file parser if no factory given
        $this->definitionFinderFactory = $definitionFinderFactory === null ?
            (string $data) ==> new FileParser(file_get_contents($data)) :
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
    }

    /**
     * Register a name filter callback
     *
     * The callback will be given the name of the class, function, etc including the full namespace.
     * If any filter returns false, the name will not appear in the list of the provided name type.
     */
    public function addNameFilter(
        NameType $type,
        (function (string) : bool) $filter,
    ) : this
    {
        $this->definitionFilters->at($type)->add($filter);
        return $this;
    }

    public function addNameFilters(
        NameType $type,
        Traversable<(function (string) : bool)> $filters,
    ) : this
    {
        $this->definitionFilters->at($type)->addAll($filters);
        return $this;
    }

    /**
     * Register a file name filter callback
     *
     * The callback will be given the name of the file, including the full path.
     * If any filter returns false, the file will not be loaded/scanned
     */
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

    /**
     * Get a mapping of definition name to file where the name is defined.
     */
    public function getNameToFileMap(NameType $type) : Map<string, string>
    {
        return $this->getAllNameToFileMaps()->at($type);
    }

    /**
     * Get all mappings of definition names to files where the names are defined.
     */
    public function getAllNameToFileMaps() : Map<NameType, Map<string, string>>
    {
        // process is memoized so files will only be scanned once
        $this->process();
        return $this->definitions->mapWithKey(
            // Loop through all names
            ($type, $list) ==> $list->filter($token ==> {
                // Apply all filters for each type
                foreach($this->definitionFilters->at($type) as $f) {
                    if( ! $f($token) ) {
                        return false;
                    }
                }
                return true;
            })
        );
    }

    ///// Implementation /////

    <<__Memoize>>
    private function process() : void
    {
        $files = Vector{};
        foreach($this->includes as $root) {

            // If user referenced a file, just add it to the list to be scanned
            if(is_file($root) && is_readable($root)) {
                $files->add($root);
                continue;
            }

            // If user referenced a non file and non directory, just skip it
            if(!is_dir($root) || ! is_readable($root)) {
                continue;
            }

            /* HH_FIXME[2049] no HHI */
            $dit = new \RecursiveDirectoryIterator($root);
            /* HH_FIXME[2049] no HHI */
            $rit = new \RecursiveIteratorIterator($dit);
            foreach ($rit as $path => $info) {
                // Only add actual files
                if ($info->isDir() || $info->isLink() || !$info->isReadable()) {
                    continue;
                }

                if($this->filterFile($path)) {
                    $files->add($path);
                }
            }
        }

        $factory = $this->definitionFinderFactory;
        foreach($files as $path) {
             $this->addDefinitions($path, $factory($path));
        }
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
