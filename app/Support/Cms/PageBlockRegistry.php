<?php

namespace App\Support\Cms;

class PageBlockRegistry
{
    public function types(): array
    {
        return [
            'hero' => 'Hero / böyük giriş',
            'text' => 'Mətn',
            'image_text' => 'Şəkil + mətn',
            'cards' => 'Kartlar',
            'cta' => 'Çağırış bloku',
            'faq' => 'Sual-cavab',
        ];
    }

    public function defaults(): array
    {
        return [
            'theme' => 'surface',
            'layout' => 'card',
            'align' => 'left',
            'radius' => '24',
            'shadow' => 'soft',
            'animation' => 'fade-up',
            'image_position' => 'right',
            'max_width' => 'full',
            'spacing' => 'large',
        ];
    }

    public function settings(array $input): array
    {
        return [
            'theme' => $this->option($input['theme'] ?? null, ['surface', 'muted', 'brand', 'dark'], 'surface'),
            'layout' => $this->option($input['layout'] ?? null, ['card', 'wide', 'centered'], 'card'),
            'align' => $this->option($input['align'] ?? null, ['left', 'center'], 'left'),
            'radius' => (string) $this->number($input['radius'] ?? 24, [0, 12, 18, 24, 32], 24),
            'shadow' => $this->option($input['shadow'] ?? null, ['none', 'soft', 'strong'], 'soft'),
            'animation' => $this->option($input['animation'] ?? null, ['none', 'fade-up', 'zoom'], 'fade-up'),
            'image_position' => $this->option($input['image_position'] ?? null, ['left', 'right'], 'right'),
            'max_width' => $this->option($input['max_width'] ?? null, ['full', 'boxed', 'narrow'], 'full'),
            'spacing' => $this->option($input['spacing'] ?? null, ['small', 'medium', 'large'], 'large'),
        ];
    }

    public function content(string $type, ?string $body, mixed $json, SafeHtml $html): array
    {
        if (! in_array($type, ['cards', 'faq'], true)) {
            return ['html' => $html->clean($body)];
        }

        $items = is_string($json) ? json_decode($json, true) : $json;
        if (! is_array($items) || $items === []) {
            $items = collect(preg_split('/\r\n|\r|\n/', (string) $body))
                ->filter(fn ($line) => trim((string) $line) !== '')
                ->map(function ($line): array {
                    $parts = explode('|', (string) $line, 2);

                    return ['title' => trim($parts[0] ?? ''), 'text' => trim($parts[1] ?? '')];
                })->values()->all();
        }

        return ['items' => collect($items)->take(30)->map(static fn ($item): array => [
            'title' => mb_substr(trim((string) ($item['title'] ?? '')), 0, 255),
            'text' => mb_substr(trim(strip_tags((string) ($item['text'] ?? ''))), 0, 2000),
        ])->filter(fn ($item) => $item['title'] !== '')->values()->all()];
    }

    public function theme(string $name): array
    {
        return match ($name) {
            'muted' => ['#f2f7f8', '#071728', '#df7412'],
            'brand' => ['#fff3e5', '#071728', '#d85f00'],
            'dark' => ['#071728', '#ffffff', '#ff9b3d'],
            default => ['#ffffff', '#071728', '#df7412'],
        };
    }

    private function option(mixed $value, array $allowed, string $default): string
    {
        $value = (string) $value;

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function number(mixed $value, array $allowed, int $default): int
    {
        $value = (int) $value;

        return in_array($value, $allowed, true) ? $value : $default;
    }
}
