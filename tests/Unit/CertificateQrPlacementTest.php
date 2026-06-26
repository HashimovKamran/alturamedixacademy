<?php

namespace Tests\Unit;

use App\Models\Certificate;
use App\Services\Admin\CertificateQrService;
use ReflectionMethod;
use Tests\TestCase;

class CertificateQrPlacementTest extends TestCase
{
    public function test_qr_placement_preserves_decimal_percentages(): void
    {
        $certificate = new Certificate([
            'qr_x' => 50.125,
            'qr_y' => 85.25,
            'qr_size' => 8.375,
        ]);

        [$x, $y, $size] = $this->placement($certificate, 1920, 1865);

        $this->assertEqualsWithDelta(962.4, $x, 0.001);
        $this->assertEqualsWithDelta(1589.9125, $y, 0.001);
        $this->assertEqualsWithDelta(160.8, $size, 0.001);
    }

    public function test_zero_coordinates_are_not_replaced_with_defaults(): void
    {
        $certificate = new Certificate([
            'qr_x' => 0,
            'qr_y' => 0,
            'qr_size' => 10,
        ]);

        [$x, $y] = $this->placement($certificate, 1200, 850);

        $this->assertSame(0.0, $x);
        $this->assertSame(0.0, $y);
    }

    private function placement(Certificate $certificate, float $width, float $height): array
    {
        $method = new ReflectionMethod(CertificateQrService::class, 'qrPlacement');

        return $method->invoke(new CertificateQrService(), $certificate, $width, $height);
    }
}
