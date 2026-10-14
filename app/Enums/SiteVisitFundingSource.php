<?php

namespace App\Enums;

enum SiteVisitFundingSource: string
{
    case OriginalFunding = 'original_funding';
    case AdditionalFunding = 'additional_funding';
    case AdditionalFunding2 = 'additional_funding_2';
    case ScaleUP = 'scale_up';

    public static function labels(): array
    {
        return [
            self::OriginalFunding->value => 'Original Funding',
            self::AdditionalFunding->value => 'Additional Funding',
            self::AdditionalFunding2->value => 'Additional Funding 2',
            self::ScaleUP->value => 'Scale-UP',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
