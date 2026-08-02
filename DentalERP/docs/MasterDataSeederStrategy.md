# Master Data Seeder Strategy

## Overview

| Item         | Detail                                        |
|--------------|-----------------------------------------------|
| Domain       | `app/Domains/MasterData`                      |
| Seeder Path  | `app/Domains/MasterData/Seeders/`             |
| Run Command  | `php artisan db:seed --class=MasterDataSeeder`|
| Environment  | Run on fresh install and staging setup        |
| Idempotent   | Yes — all seeders use `firstOrCreate()`       |

> Seeders MUST be idempotent. Running them multiple times must not create duplicates.
> Geographic seeders (Country → Village) MUST run in strict order due to FK dependencies.
> All other seeders can run in any order.

---

## Seeder Order

Run in this exact sequence:

### Group A — Geographic (Strict Order — FK Dependency)

```
1.  CountrySeeder         →  countries table
2.  ProvinceSeeder        →  provinces table     (requires countries)
3.  CitySeeder            →  cities table        (requires provinces)
4.  DistrictSeeder        →  districts table     (requires cities)
5.  VillageSeeder         →  villages table      (requires districts)
```

### Group B — Locale (Any Order)

```
6.  CurrencySeeder        →  currencies table
7.  TimezoneSeeder        →  timezones table
8.  LanguageSeeder        →  languages table
```

### Group C — Demographic (Any Order)

```
9.  ReligionSeeder        →  religions table
10. BloodTypeSeeder       →  blood_types table
11. GenderSeeder          →  genders table
12. MaritalStatusSeeder   →  marital_statuses table
```

### Group D — Clinical (Any Order)

```
13. PatientTypeSeeder     →  patient_types table
14. DoctorSpecialtySeeder →  doctor_specialties table
```

### Group E — Financial (Any Order)

```
15. PaymentMethodSeeder     →  payment_methods table
16. InsuranceCompanySeeder  →  insurance_companies table
```

---

## Dependency Graph

```
CountrySeeder
    └── ProvinceSeeder
            └── CitySeeder
                    └── DistrictSeeder
                            └── VillageSeeder

CurrencySeeder        ─── (independent)
TimezoneSeeder        ─── (independent)
LanguageSeeder        ─── (independent)
ReligionSeeder        ─── (independent)
BloodTypeSeeder       ─── (independent)
GenderSeeder          ─── (independent)
MaritalStatusSeeder   ─── (independent)
PatientTypeSeeder     ─── (independent)
DoctorSpecialtySeeder ─── (independent)
PaymentMethodSeeder   ─── (independent)
InsuranceCompanySeeder─── (independent)
```

---

## Data Sources

| Seeder               | Data Source                                 |
|----------------------|---------------------------------------------|
| CountrySeeder        | ISO 3166-1 — prioritize Indonesia first     |
| ProvinceSeeder       | BPS (Badan Pusat Statistik) — 38 provinces  |
| CitySeeder           | BPS — 514 cities & regencies                |
| DistrictSeeder       | BPS — Kecamatan data                        |
| VillageSeeder        | BPS — Kelurahan/Desa + postal codes         |
| CurrencySeeder       | ISO 4217 — IDR default                      |
| TimezoneSeeder       | IANA — WIB, WITA, WIT                       |
| LanguageSeeder       | ISO 639-1 — Bahasa Indonesia default        |
| ReligionSeeder       | Indonesian MoHA — 6 official religions      |
| BloodTypeSeeder      | ABO + Rh system — 8 records                 |
| GenderSeeder         | Male, Female — 2 records                    |
| MaritalStatusSeeder  | Single, Married, Divorced, Widowed          |
| PatientTypeSeeder    | General, BPJS, Insurance, VIP, Employee     |
| DoctorSpecialtySeeder| General Dentist, Orthodontist, Periodontist, etc. |
| PaymentMethodSeeder  | Cash, Transfer, Card, GoPay, OVO, BPJS      |
| InsuranceCompanySeeder | BPJS Kesehatan, Prudential, AXA, etc.     |

---

## Business Rules

1. All seeders use `firstOrCreate()` — safe to run multiple times.
2. Geographic seeders MUST run in order (Country → Province → City → District → Village).
3. All other groups may run in parallel or any order.
4. Default active status: `is_active = true` for all seeded records.
5. Seeded records should NEVER be hard-deleted — use `is_active = false` to disable.
6. Village seeder may be split into batches due to large dataset size (~83,000 records).
7. Seeder data must reflect the latest BPS territorial data.

---

## MasterDataSeeder (Orchestrator)

The root seeder calls all group seeders in the correct order:

```php
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Group A — Geographic (strict order)
        $this->call([
            CountrySeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            DistrictSeeder::class,
            VillageSeeder::class,
        ]);

        // Groups B, C, D, E — independent
        $this->call([
            CurrencySeeder::class,
            TimezoneSeeder::class,
            LanguageSeeder::class,
            ReligionSeeder::class,
            BloodTypeSeeder::class,
            GenderSeeder::class,
            MaritalStatusSeeder::class,
            PatientTypeSeeder::class,
            DoctorSpecialtySeeder::class,
            PaymentMethodSeeder::class,
            InsuranceCompanySeeder::class,
        ]);
    }
}
```

---

## Notes

- `VillageSeeder` handles the largest dataset (~83,000 records) — use chunked inserts.
- Run `php artisan permission:cache-reset` after seeding if roles/permissions are affected.
- Geographic data is sourced from BPS and should be updated periodically.
- All Master Data is global (not org/branch-scoped) — seed once per installation.
