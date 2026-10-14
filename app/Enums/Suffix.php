<?php

namespace App\Enums;

enum Suffix: string
{
    case Jr = 'jr';
    case Sr = 'sr';
    case Ii = 'ii';
    case Iii = 'iii';
    case Iv = 'iv';

    public static function labels(): array
    {
        return [
            self::Jr->value => 'Jr.',
            self::Sr->value => 'Sr.',
            self::Ii->value => 'II',
            self::Iii->value => 'III',
            self::Iv->value => 'IV',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
