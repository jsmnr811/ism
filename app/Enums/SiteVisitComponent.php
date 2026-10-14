<?php

namespace App\Enums;

enum SiteVisitComponent: string
{
    case IBUILD = 'i_build';
    case IREAP = 'i_reap';

    public static function labels(): array
    {
        return [
            self::IBUILD->value => 'I-BUILD',
            self::IREAP->value => 'I-REAP',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
