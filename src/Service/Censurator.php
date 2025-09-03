<?php

namespace App\Service;

class Censurator
{
    const BANNED_WORDS = ['pute', 'salope', 'connard', 'connasse', 'merde', 'putain', 'bordel', 'con', 'conne', 'bite', 'chatte', 'couille', 'foutre', 'baiser', 'niquer', 'enculé', 'enculée', 'gueule', 'crétin', 'imbécile', 'abruti', 'débile', 'taré', 'salaud', 'ordure', 'porc', 'chien', 'bâtard', 'salopard', 'fumier', 'racaille', 'cul', 'fesses', 'nichons', 'seins', 'zizi', 'pénis', 'vagin', 'sexe', 'cochon', 'dégueulasse', 'dégueu', 'caca', 'pipi', 'crotte', 'merdique'];
    public function purify(string $string): string
    {
        $words = explode(' ', $string);

        foreach ($words as &$word) {
            $cleanWord = strtolower(trim($word, '.,!?;:'));

            if (in_array($cleanWord, self::BANNED_WORDS)) {
                $word = str_repeat('*', strlen($cleanWord));
            }
        }

        return implode(' ', $words);
    }
}