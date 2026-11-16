<?php

namespace App\Enums;

enum RegistrationSource: string
{
    case Landing = 'landing';
    case Manual = 'manual';

    public static function labels(): array
    {
        return [
            self::Landing->value => 'Landing Page',
            self::Manual->value => 'Manually Registered',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
