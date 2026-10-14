<?php

namespace App\Enums;

enum MissionRpcoOffice: string
{
    case RPCO01 = 'rpco_01';
    case RPCO02 = 'rpco_02';
    case RPCO03 = 'rpco_03';
    case RPCO04A = 'rpco_04A';
    case RPCO05 = 'rpco_05';
    case RPCO06 = 'rpco_06';
    case RPCO07 = 'rpco_07';
    case RPCO08 = 'rpco_08';
    case RPCO09 = 'rpco_09';
    case RPCO10 = 'rpco_10';
    case RPCO11 = 'rpco_11';
    case RPCO12 = 'rpco_12';
    case CAR = 'car';
    case CARAGA = 'caraga';
    case BARMM = 'barmm';
    case MIMAROPA = 'mimaropa';

    public static function labels(): array
    {
        return [
            self::CAR->value => 'CAR',
            self::RPCO01->value => 'RPCO 01',
            self::RPCO02->value => 'RPCO 02',
            self::RPCO03->value => 'RPCO 03',
            self::RPCO04A->value => 'RPCO 04A',
            self::RPCO05->value => 'RPCO 05',
            self::RPCO06->value => 'RPCO 06',
            self::RPCO07->value => 'RPCO 07',
            self::RPCO08->value => 'RPCO 08',
            self::RPCO09->value => 'RPCO 09',
            self::RPCO10->value => 'RPCO 10',
            self::RPCO11->value => 'RPCO 11',
            self::RPCO12->value => 'RPCO 12',
            self::CARAGA->value => 'CARAGA',
            self::MIMAROPA->value => 'MIMAROPA',
            self::BARMM->value => 'BARMM',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
