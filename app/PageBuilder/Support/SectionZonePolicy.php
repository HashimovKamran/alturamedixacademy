<?php

namespace App\PageBuilder\Support;

use Illuminate\Validation\ValidationException;

final class SectionZonePolicy
{
    /** @return array<int, string> */
    public function allowedZones(string $sectionType): array
    {
        return match ($sectionType) {
            'header' => ['header'],
            'footer' => ['footer'],
            default => ['main', 'header', 'footer'],
        };
    }

    public function assertAllowed(string $sectionType, string $zone, string $path): void
    {
        if (! in_array($zone, $this->allowedZones($sectionType), true)) {
            throw ValidationException::withMessages([
                $path => [sprintf('The %s section is not allowed in the %s zone.', $sectionType, $zone)],
            ]);
        }
    }
}

