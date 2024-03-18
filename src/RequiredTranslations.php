<?php

namespace Druc\Langscanner;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

class RequiredTranslations
{
    private Filesystem $disk;
    private array $paths;
    private array $translationMethods;
    private array $excludedPaths;
    private array $translations;
    private string $langDirPath;

    public function __construct(array $options, Filesystem $disk = null)
    {
        Assert::keyExists($options, 'paths');
        Assert::keyExists($options, 'excluded_paths');
        Assert::keyExists($options, 'translation_methods');
        Assert::keyExists($options, 'lang_dir_path');

        $this->disk = $disk ?? resolve(Filesystem::class);
        $this->paths = $options['paths'];
        $this->excludedPaths = $options['excluded_paths'];
        $this->translationMethods = $options['translation_methods'];
        $this->langDirPath = $options['lang_dir_path'];
    }

    public function all(): array
    {
        if (isset($this->translations)) {
            return $this->translations;
        }

        $results = [];

        foreach ($this->files() as $file) {
            if (preg_match_all($this->pattern(), $file->getContents(), $matches)) {
                foreach ($matches[2] as $key) {
                    if (!empty($key)) {
                        $results[$key] = $file->getFilename();
                    }
                }
            }
        }

        // exclude php translations
        $results = array_diff_key($results, $this->existingPhpTranslations());

        return $this->translations = $results;
    }

    private function files(): array
    {
        $allFiles = $this->disk->allFiles($this->paths);

        return Collection::make($allFiles)
            ->reject(function ($file) {
                $filePath = $this->normalizePath($file->getPathName());

                foreach ($this->excludedPaths as $excludedPath) {
                    $normalizedExcludedPath = $this->normalizePath($excludedPath);
                    if (strpos($filePath, $normalizedExcludedPath) === 0) {
                        return true;
                    }
                }

                return false;
            })
            ->toArray();
    }

    /*
     *  Converts Directory Separators
     *  Different operating systems use different directory separators in file paths. Windows uses backslashes (\),
     *  while UNIX-like systems, including Linux and macOS, use forward slashes (/). The str_replace('\\', '/', $path)
     *  part of the function converts all backslashes to forward slashes. This ensures that paths are handled in a uniform way,
     *  regardless of the operating system on which your PHP code is running.
     *
     *  Removes Trailing Slashes
     *  Paths can sometimes end with a trailing slash (or backslash, depending on the system), especially when referring to directories.
     *  However, when comparing two paths, a trailing slash might lead to inconsistencies where two paths that essentially refer to the same directory are considered different.
     *  For example, some/path/ and some/path would be considered different strings even though they refer to the same directory.
     *  The rtrim($path, '/') part removes any trailing forward slashes from the path, ensuring that paths are compared without considering these potentially extraneous characters.
     */
    private function normalizePath($path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    private function startsWith($haystack, $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    private function existingPhpTranslations(): array
    {
        return Collection::make($this->disk->allFiles($this->langDirPath))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->reduce(function ($carry, $file) {
                $translations = $this->disk->getRequire($file->getRealPath());

                return $carry->merge(Arr::dot([
                    $file->getFilenameWithoutExtension() => $translations,
                ]));
            }, Collection::make([]))
            ->filter(fn ($item) => is_string($item))
            ->toArray();
    }

    private function pattern(): string
    {
        // See https://regex101.com/r/jS5fX0/5
        return
            "/" .
            "[^\w]" . // Must not start with any alphanum or _
            "(?<!->)" . // Must not start with ->
            '(' . implode('|', $this->translationMethods) . ')' .// Must start with one of the functions
            "\(" .// Match opening parentheses
            "[\r\n|\r|\n]*?" .// Ignore new lines
            "[\'\"]" .// Match " or '
            "(" .// Start a new group to match:
            ".*" .// Must start with group
            ")" .// Close group
            "[\'\"]" .// Closing quote
            "[\r\n|\r|\n]*?" .// Ignore new lines
            "[\),]" . // Close parentheses or new parameter
            "/siuU";
    }
}
