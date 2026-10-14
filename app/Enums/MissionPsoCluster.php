<?php

namespace App\Enums;

enum MissionPsoCluster: string
{
    case NorthLuzon = 'north_luzon';
    case SouthLuzon = 'south_luzon';
    case Visayas = 'visayas';
    case Mindanao = 'mindanao';

    public static function labels(): array
    {
        return [
            self::NorthLuzon->value => 'North Luzon',
            self::SouthLuzon->value => 'South Luzon',
            self::Visayas->value => 'Visayas',
            self::Mindanao->value => 'Mindanao',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
