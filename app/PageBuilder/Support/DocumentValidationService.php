<?php

namespace App\PageBuilder\Support;

final class DocumentValidationService
{
    public function __construct(
        private readonly DocumentValidator $validator,
        private readonly SectionZonePolicy $zones,
    ) {
    }

    /** @param array<string, mixed> $document */
    public function validate(array $document): array
    {
        $this->assertZoneSections($document['sections'] ?? [], 'main', 'sections');

        $layout = is_array($document['layout'] ?? null) ? $document['layout'] : [];
        foreach (['header', 'footer'] as $zone) {
            $zoneData = is_array($layout[$zone] ?? null) ? $layout[$zone] : [];
            $this->assertZoneSections($zoneData['sections'] ?? [], $zone, "layout.{$zone}");
        }

        return $this->validator->validate($document);
    }

    /** @param mixed $sections */
    private function assertZoneSections(mixed $sections, string $zone, string $path): void
    {
        if (! is_array($sections)) {
            return;
        }

        foreach ($sections as $id => $section) {
            if (! is_array($section) || ! is_string($section['type'] ?? null)) {
                continue;
            }

            $this->zones->assertAllowed($section['type'], $zone, "{$path}.{$id}.type");
        }
    }
}

