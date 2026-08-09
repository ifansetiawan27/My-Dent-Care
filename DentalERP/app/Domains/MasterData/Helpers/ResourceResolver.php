<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Helpers;

use App\Domains\MasterData\Interfaces\MasterDataRepositoryInterface;
use App\Domains\MasterData\Interfaces\MasterDataServiceInterface;

final class ResourceResolver
{
    /** @var array<string, string> Resource name → Service class */
    private array $services = [
        'countries'           => \App\Domains\MasterData\Services\CountryService::class,
        'provinces'           => \App\Domains\MasterData\Services\ProvinceService::class,
        'cities'              => \App\Domains\MasterData\Services\CityService::class,
        'districts'           => \App\Domains\MasterData\Services\DistrictService::class,
        'villages'            => \App\Domains\MasterData\Services\VillageService::class,
        'currencies'          => \App\Domains\MasterData\Services\CurrencyService::class,
        'timezones'           => \App\Domains\MasterData\Services\TimezoneService::class,
        'languages'           => \App\Domains\MasterData\Services\LanguageService::class,
        'nationalities'       => \App\Domains\MasterData\Services\NationalityService::class,
        'genders'             => \App\Domains\MasterData\Services\GenderService::class,
        'religions'           => \App\Domains\MasterData\Services\ReligionService::class,
        'blood_types'         => \App\Domains\MasterData\Services\BloodTypeService::class,
        'marital_statuses'    => \App\Domains\MasterData\Services\MaritalStatusService::class,
        'patient_types'       => \App\Domains\MasterData\Services\PatientTypeService::class,
        'doctor_specialties'  => \App\Domains\MasterData\Services\DoctorSpecialtyService::class,
        'treatment_categories'=> \App\Domains\MasterData\Services\TreatmentCategoryService::class,
        'appointment_statuses'=> \App\Domains\MasterData\Services\AppointmentStatusService::class,
        'laboratory_categories'=> \App\Domains\MasterData\Services\LaboratoryCategoryService::class,
        'payment_methods'     => \App\Domains\MasterData\Services\PaymentMethodService::class,
        'insurance_companies' => \App\Domains\MasterData\Services\InsuranceCompanyService::class,
        'tax_rates'           => \App\Domains\MasterData\Services\TaxRateService::class,
        'asset_categories'    => \App\Domains\MasterData\Services\AssetCategoryService::class,
        'inventory_categories'=> \App\Domains\MasterData\Services\InventoryCategoryService::class,
    ];

    /** @var array<string, string> */
    private array $parentColumns = [
        'provinces' => 'country_id',
        'cities'    => 'province_id',
        'districts' => 'city_id',
        'villages'  => 'district_id',
    ];

    public function resolveService(string $resource): MasterDataServiceInterface
    {
        $class = $this->services[$resource] ?? throw new \InvalidArgumentException("Unknown resource: {$resource}");

        return app($class);
    }

    public function resolveRepository(string $resource): MasterDataRepositoryInterface
    {
        $class = \str_replace('Services', 'Repositories', $this->services[$resource])
            ?? throw new \InvalidArgumentException("Unknown resource: {$resource}");

        return app($class);
    }

    public function getParentColumn(string $resource): ?string
    {
        return $this->parentColumns[$resource] ?? null;
    }

    public function getResourceName(string $resource): string
    {
        return \Illuminate\Support\Str::singular($resource);
    }
}
