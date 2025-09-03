<?php

namespace App\Service;

class Censurator
{
    private array $bannedWords = [];

    public function __construct(private string $bannedWordsFile)
    {
        $this->loadBannedWords();
    }
    private function loadBannedWords(): void
    {
        if (!file_exists($this->bannedWordsFile)) {
            throw new \RuntimeException("Le fichier des mots bannis n'existe pas : {$this->bannedWordsFile}");
        }

        $content = file($this->bannedWordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($content === false) {
            throw new \RuntimeException("Impossible de lire le fichier des mots bannis : {$this->bannedWordsFile}");
        }

        $this->bannedWords = $content;
    }

    public function purify(string $string): string
    {
        $words = explode(' ', $string);

        foreach ($words as &$word) {
            $cleanWord = strtolower(trim($word, '.,!?;:'));

            if (in_array($cleanWord, $this->bannedWords)) {
                $word = str_repeat('*', strlen($cleanWord));
            }
        }

        return implode(' ', $words);
    }
}