<?php

namespace Druc\Langscanner\Commands;

use Druc\Langscanner\CachedFileTranslations;
use Druc\Langscanner\FileTranslations;
use Druc\Langscanner\Languages;
use Druc\Langscanner\MissingTranslations;
use Druc\Langscanner\RequiredTranslations;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LangscannerCommand extends Command
{
    protected $signature = 'langscanner {language?} {--path= : The path to scan for translation keys}';
    protected $description = "Updates translation files with missing translation keys.";

    public function handle(Filesystem $filesystem): void
    {
        $language = $this->argument('language');
        $modulePath = $this->option('path');

        if ($modulePath) {
            $outputPath = $modulePath . '/resources/lang/';
        } else {
            $outputPath = config('langscanner.lang_dir_path') . '/';
        }

        $languages = $this->getLanguages($language, $outputPath, $filesystem);

        foreach ($languages as $language) {
            $this->processLanguage($language, $outputPath, $filesystem);
        }
    }

    protected function getLanguages($language, $outputPath, $filesystem): array
    {
        if ($language) {
            return [$language];
        } else {
            // If no specific language is provided, determine available languages from the outputPath
            return Languages::fromPath($outputPath, $filesystem)->all();
        }
    }

    protected function processLanguage($language, $outputPath, $filesystem): void
    {
        $fileTranslations = new CachedFileTranslations(
            new FileTranslations([
                'language' => $language,
                'disk' => $filesystem,
                'rootPath' => $outputPath,
            ])
        );

        $missingTranslations = new MissingTranslations(
            new RequiredTranslations(config('langscanner')),
            $fileTranslations
        );

        $fileTranslations->update(
            // Sets translation values to empty string
            array_fill_keys(
                array_keys($missingTranslations->all()),
                ''
            )
        );

        // Render table with missing translations
        $this->renderMissingTranslationsTable($language, $missingTranslations->all());
    }

    protected function renderMissingTranslationsTable($language, array $missingTranslations): void
    {
        $this->comment(PHP_EOL);
        $this->comment(strtoupper($language) . " missing translations:");

        $rows = [];

        foreach ($missingTranslations as $key => $path) {
            $rows[] = [$key, $path];
        }

        $this->table(["Key", "Path"], $rows);
    }
}
