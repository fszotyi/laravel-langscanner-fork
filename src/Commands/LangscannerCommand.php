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
    protected $signature = 'langscanner
                        {language?}
                        {--path= : The path to scan for translation keys (ex: --path=app/Modules/Module1 )}
                        {--exclude-path=* : Directories to exclude from the scan (ex: --exclude-path=app/Modules/)}';
    protected $description = "Updates translation files with missing translation keys.";

    public function handle(Filesystem $filesystem): void
    {
        $language = $this->argument('language');
        $modulePath = $this->option('path');
        $exclusions = $this->option('exclude-path');

        $config = config('langscanner');

        if ($modulePath) {
            $config['paths'] = [$modulePath];
            $outputPath = $modulePath . '/resources/lang/';
        } else {
            $outputPath = config('langscanner.lang_dir_path') . '/';
        }

        // Apply exclusions if any are provided
        if (!empty($exclusions)) {
            $config['excluded_paths'] = array_merge($config['excluded_paths'], $exclusions);
        }

        $languages = $this->getLanguages($language, $outputPath, $filesystem);

        foreach ($languages as $language) {
            $this->processLanguage($language, $outputPath, $filesystem, $config);
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

    protected function processLanguage($language, $outputPath, Filesystem $filesystem, $config): void
    {
        if (!$filesystem->exists($outputPath)) {
            // Create the directory if it doesn't exist
            $filesystem->makeDirectory($outputPath, 0755, true);
            $this->info("Created directory: {$outputPath}");
        }

        $fileTranslations = new CachedFileTranslations(
            new FileTranslations([
                'language' => $language,
                'disk' => $filesystem,
                'rootPath' => $outputPath,
            ])
        );

        $missingTranslations = new MissingTranslations(
            new RequiredTranslations($config),
            $fileTranslations
        );

        $fileTranslations->update(
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
