<?php

namespace App\Enums;

enum MissionComponentUnit: string
{
    case OfficeOfNationalProjectDirector = 'office_of_national_project_director';
    case OfficeOfNationalDeputyProjectDirector = 'office_of_national_deputy_project_director';
    case IPLAN = 'i_plan';
    case IBUILD = 'i_build';
    case IREAP = 'i_reap';
    case ISUPPORT = 'i_support';
    case AdminUnit = 'admin_unit';
    case ProcurementUnit = 'procurement_unit';
    case SESUnit = 'ses_unit';
    case FinanceUnit = 'finance_unit';
    case MEUnit = 'm_unit';
    case EconomicsUnit = 'economics_unit';
    case InfoAceUnit = 'infoace_unit';
    case GeomappingAndGovernanceUnit = 'geomapping_and_governance_unit';
    case GEFNRMUnit = 'gef_nrm_unit';

    public static function labels(): array
    {
        return [
            self::OfficeOfNationalProjectDirector->value => 'Office of the National Project Director',
            self::OfficeOfNationalDeputyProjectDirector->value => 'Office of the National Deputy Project Director',
            self::IPLAN->value => 'I-PLAN',
            self::IBUILD->value => 'I-BUILD',
            self::IREAP->value => 'I-REAP',
            self::ISUPPORT->value => 'I-SUPPORT',
            self::AdminUnit->value => 'Admin Unit',
            self::ProcurementUnit->value => 'Procurement Unit',
            self::SESUnit->value => 'SES Unit',
            self::FinanceUnit->value => 'Finance Unit',
            self::MEUnit->value => 'M&E Unit',
            self::EconomicsUnit->value => 'Economics Unit',
            self::InfoAceUnit->value => 'InfoAce Unit',
            self::GeomappingAndGovernanceUnit->value => 'Geomapping and Governance Unit',
            self::GEFNRMUnit->value => 'GEF-NRM Unit',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
