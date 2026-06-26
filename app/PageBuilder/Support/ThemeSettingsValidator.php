<?php

namespace App\PageBuilder\Support;

use Illuminate\Validation\ValidationException;

final class ThemeSettingsValidator
{
    public function __construct(private readonly ComponentCatalog $catalog)
    {
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): array
    {
        $schema = [];
        foreach ($this->catalog->themeSettings() as $group) {
            foreach ($group['settings'] as $setting) {
                $schema[$setting['key']] = $setting;
            }
        }

        $unknown = array_diff(array_keys($values), array_keys($schema));
        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'theme_settings' => ['Unknown setting: '.reset($unknown)],
            ]);
        }

        $normalized = [];
        foreach ($schema as $key => $setting) {
            $value = $values[$key] ?? $setting['default'];
            $normalized[$key] = $this->value($key, $setting['type'], $value);
        }

        return $normalized;
    }

    private function value(string $key, string $type, mixed $value): string
    {
        $text = trim((string) $value);

        if ($type === 'color') {
            if (! preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([^\r\n;{}]+\)|hsla?\([^\r\n;{}]+\))$/', $text)) {
                throw ValidationException::withMessages([$key => ['Invalid color value.']]);
            }
            return $text;
        }

        if ($text === '' || mb_strlen($text) > 240 || str_contains($text, ';') || str_contains($text, '{') || str_contains($text, '}')) {
            throw ValidationException::withMessages([$key => ['Invalid theme value.']]);
        }

        return $text;
    }
}

