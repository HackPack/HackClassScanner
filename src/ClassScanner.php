<?hh

namespace kilahm\Scanner;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use SplFileObject;

final class ClassScanner
{
    private bool $findClasses = false;
    private bool $findInterfaces = false;
    private Vector<(function (string) : bool)> $fileFilters = Vector{};
    private Vector<(function (string) : bool)> $classFilters = Vector{};

    public function __construct(public Set<string> $paths, public Set<string> $excludes = Set{})
    {
        $this->paths = $paths
            ->filter($p ==> $p !== '' && is_dir($p))
            ->map($p ==> realpath($p));
        $this->excludes = $excludes
            ->filter($p ==> $p !== '' && is_dir($p))
            ->map($p ==> realpath($p));
    }

    public function addClassNameFilter((function (string) : bool) $filter) : this
    {
        $this->classFilters->add($filter);
        return $this;
    }

    public function addClassNameFilters(Traversable<(function (string) : bool)> $filters) : this
    {
        $this->classFilters->addAll($filters);
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
        $this->findClasses = true;
        $this->findInterfaces = false;
        return $this->map();
    }

    public function mapClassOrInterfaceToFile() : Map<string,string>
    {
        $this->findClasses = true;
        $this->findInterfaces = true;
        return $this->map();
    }

    private function map() : Map<string,string>
    {
        $classMap = Map{};
        foreach($this->paths as $basePath) {
            $this->findFilesRecursive($basePath, $classMap);
        }
        return $classMap;
    }

    private function filterClass(string $className) : bool
    {
        foreach($this->classFilters as $filter) {
            if( ! $filter($className)){
                return false;
            }
        }
        return true;
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

    private function findFilesRecursive(string $path, Map<string,string> $map) : void
    {
        foreach(new FileSystemIterator($path) as $finfo) {
            if($finfo->isDir() && ! $this->excludes->contains($finfo->getRealPath())) {
                $this->findFilesRecursive($finfo->getRealPath(), $map);
            } elseif($finfo->isFile() && $this->filterFile($finfo->getRealPath())) {
                $className = $this->parseFile($finfo->openFile());
                if($className !== '') {
                    $map[$this->parseFile($finfo->openFile())] = $finfo->getRealPath();
                }
            }
        }
    }

    private function parseFile(SplFileObject $file) : string
    {
        // incrementally break contents into tokens
        $namespace = $class = $buffer = '';
        $i = 0;
        while ($class === '') {
            if ($file->eof()) {
                // No class to find
                return '';
            }
            // Load 30 lines at a time
            for($newline = 0; $newline < 30; $newline++) {
                $buffer .= $file->fgets();
            }

            if (strpos($buffer, '{') === false) {
                // Class definition requires braces
                continue;
            }

            $tokens = token_get_all($buffer);

            for (; $i < count($tokens); $i++) {

                //search for a namespace
                if ($tokens[$i][0] === \T_NAMESPACE) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        // Namespace ends on { or ;
                        if (is_array($tokens[$j])) {
                            $namespace .= trim((string) $tokens[$j][1]);
                        } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        } else {
                            $namespace .= (string) $tokens[$j];
                        }
                    }
                }

                //search for the class name
                if (
                    ($this->findClasses && $tokens[$i][0] === \T_CLASS) ||
                    ($this->findInterfaces && $tokens[$i][0] === \T_INTERFACE)
                ) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if($tokens[$j] === '{') {
                            // We found one!
                            $class = $tokens[$i + 2][1];
                            break;
                        } elseif($tokens[$j] === '}' || $tokens[$j] === ';') {
                            // We found ::class inside of a context
                            $class = '';
                            break;
                        }
                    }
                }

                if($class !== '') {
                    // Stop looking for the class name after it is found
                    break;
                }
            }
        }

        if($namespace !== '' && substr($namespace, 0, 1) !== '\\') {
            $namespace = '\\' . $namespace;
        }

        $fullname = implode('\\', [$namespace, $class]);
        if($this->filterClass($fullname)){
            return $fullname;
        }
        return '';
    }
}
