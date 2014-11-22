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

    protected static array<string> $longOpts = [
        'exclude:',
    ];

    public static function fromCli(Vector<string> $argv): this
    {
        // The first value of argv is the script name
        $argv->removeKey(0);
        $paths = $argv->toSet();

        $options = getopt('', static::$longOpts);
        if (array_key_exists('exclude', $options)) {
            $excludes = Set::fromItems(preg_split('/\s+/', $options['exclude']));
        } else {
            $excludes = Set{};
        }

        return new static($paths, $excludes);
    }

    public function __construct(public Set<string> $paths, public Set<string> $excludes = Set{})
    {
        $this->paths = $paths
            ->filter($p ==> $p !== '' && is_dir($p))
            ->map($p ==> realpath($p));
        $this->excludes = $excludes
            ->filter($p ==> $p !== '' && is_dir($p))
            ->map($p ==> realpath($p));
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

    private function findFilesRecursive(string $path, Map<string,string> $map) : Map<string,string>
    {
        foreach(new FileSystemIterator($path) as $finfo) {
            if($finfo->isDir() && ! $this->excludes->contains($finfo->getRealPath())) {
                $this->findFilesRecursive($finfo->getRealPath(), $map);
            } elseif($finfo->isFile()) {
                $className = $this->parseFile($finfo->openFile());
                if($className !== '') {
                    $map[$this->parseFile($finfo->openFile())] = $finfo->getRealPath();
                }
            }
        }
        return $map;
    }

    private function parseFile(SplFileObject $file) : string
    {
        // incrementally break contents into tokens
        $namespace = $class = $buffer = '';
        $i = 0;
        while ($class === '') {
            if ($file->eof()) {
                return '';
            }
            // Load 10 lines at a time
            for($newline = 0; $newline < 10; $newline++) {
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
                    $namespace = '';
                    $stop = false;
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === \T_STRING) {
                            $namespace .= '\\' . (string) $tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                //search for the class name
                if (
                    ($this->findClasses && $tokens[$i][0] === \T_CLASS) ||
                    ($this->findInterfaces && $tokens[$i][0] === \T_INTERFACE)
                ) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        switch($tokens[$j]) {
                        case '{':
                            // We found one!
                            $class = $tokens[$i + 2][1];
                            break;
                        case '}':
                        case ';':
                            // Looks like ::class inside of a context
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

        return $namespace . '\\' . $class;
    }
}
