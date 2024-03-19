<?php

namespace Druc\Langscanner\Contracts;

interface FileTranslations
{
    public function update(array $translations, string $filename): void;

    public function all(): array;

    public function language(): string;

    public function contains(string $key): bool;
}
