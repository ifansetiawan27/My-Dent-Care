<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Authentication\Enums;

use App\Domains\Authentication\Enums\DeviceType;
use App\Domains\Authentication\Enums\LoginStatus;
use PHPUnit\Framework\TestCase;

/**
 * Enum Tests for Authentication enums.
 * Verifies enum values match frozen DB CHECK constraints and labels.
 */
class AuthenticationEnumTest extends TestCase
{
    public function test_login_status_values_match_db_check_constraint(): void
    {
        $this->assertSame(['success', 'failed'], LoginStatus::values());
    }

    public function test_login_status_labels(): void
    {
        $this->assertSame('Success', LoginStatus::Success->label());
        $this->assertSame('Failed', LoginStatus::Failed->label());
    }

    public function test_device_type_values_match_db_check_constraint(): void
    {
        $this->assertSame(['web', 'mobile', 'tablet', 'api'], DeviceType::values());
    }

    public function test_device_type_labels(): void
    {
        $this->assertSame('Web', DeviceType::Web->label());
        $this->assertSame('Mobile', DeviceType::Mobile->label());
        $this->assertSame('Tablet', DeviceType::Tablet->label());
        $this->assertSame('API', DeviceType::Api->label());
    }

    public function test_enums_are_string_backed(): void
    {
        $this->assertIsString(LoginStatus::Success->value);
        $this->assertIsString(DeviceType::Web->value);
    }
}
