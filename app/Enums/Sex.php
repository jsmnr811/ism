<?php

namespace App\Enums;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public static function labels(): array
    {
        return [
            self::Male->value => 'Male',
            self::Female->value => 'Female',
            self::Other->value => 'Other',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
