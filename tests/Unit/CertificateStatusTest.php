<?php

namespace Tests\Unit;

use App\Models\Certificate;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CertificateStatusTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_effective_status_uses_expiry_date_and_supports_lifetime_documents(): void
    {
        Carbon::setTestNow('2026-06-17 12:00:00');

        $expired = new Certificate([
            'status' => 'valid',
            'expire_date' => '2026-06-16',
        ]);
        $validThroughToday = new Certificate([
            'status' => 'valid',
            'expire_date' => '2026-06-17',
        ]);
        $lifetime = new Certificate([
            'status' => 'valid',
            'expire_date' => null,
        ]);

        $this->assertSame('expired', $expired->effectiveStatus());
        $this->assertSame('valid', $validThroughToday->effectiveStatus());
        $this->assertSame('valid', $lifetime->effectiveStatus());
    }

    public function test_revoked_status_has_priority_over_expiry_date(): void
    {
        Carbon::setTestNow('2026-06-17 12:00:00');

        $certificate = new Certificate([
            'status' => 'revoked',
            'expire_date' => '2026-06-16',
        ]);

        $this->assertSame('revoked', $certificate->effectiveStatus());
    }
}
