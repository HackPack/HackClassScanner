<?hh

namespace kilahm\Scanner;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;

final class ClassScanner
{
    protected static array<string> $longOpts = [
        'exclude:',
    ];

    public static function fromCli(array<string> $argv): this
    {
        // The first value of argv is the script name
        $paths = Set::fromItems($argv);

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

    public function mapFileToClass() : Map<string,string>
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

    private function parseFile(\SplFileObject $file) : string
    {
        // incrementally break contents into tokens
        $namespace = $class = $buffer = '';
        $i = 0;
        while (!$class) {
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
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === \T_STRING) {
                            $namespace .= '\\' . (string) $tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                //search for the class name
                if ($tokens[$i][0] === \T_CLASS) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i + 2][1];
                            break;
                        }
                    }
                }

                if($class) {
                    // Stop looking for the class name after it is found
                    break;
                }
            }
        }

        return $namespace . '\\' . $class;
    }
}
