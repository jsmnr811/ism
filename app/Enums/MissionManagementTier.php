<?php

namespace App\Enums;

enum MissionManagementTier: string
{
    case NPCO = 'npco';
    case PSO = 'pso';
    case RPCO = 'rpco';

    public static function labels(): array
    {
        return [
            self::NPCO->value => 'NPCO',
            self::PSO->value => 'PSO',
            self::RPCO->value => 'RPCO',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
