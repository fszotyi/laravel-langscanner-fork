<?php

namespace Druc\Langscanner;

use Illuminate\Filesystem\Filesystem;
use Webmozart\Assert\Assert;

class FileTranslations implements Contracts\FileTranslations
{
    private string $language;
    private string $rootPath;
    private Filesystem $disk;

    public function __construct(array $opts)
    {
        Assert::keyExists($opts, 'language');

        $this->language = $opts['language'];
        $this->disk = $opts['disk'] ?? resolve(Filesystem::class);
        $this->rootPath = $opts['rootPath'] ?? config('langscanner.lang_dir_path') .DIRECTORY_SEPARATOR ."{$this->language}";
    }

    public function language(): string
    {
        return $this->language;
    }

    public function update(array $translations, $filename = 'text.json'): void
    {
        $translations = array_merge($this->all(), $translations);
        $translations = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->disk->put($this->path(), $translations);
    }

    public function all(): array
    {
        if (file_exists($this->path())) {
            $result = json_decode($this->disk->get($this->path()), true);

            if (!is_array($result)) {
                return [];
            }

            return $result;
        }

        return [];
    }

    public function contains(string $key): bool
    {
        return !empty($this->all()[$key]);
    }

    private function path(): string
    {
        return "{$this->rootPath}".DIRECTORY_SEPARATOR ."text.json";
    }
}
