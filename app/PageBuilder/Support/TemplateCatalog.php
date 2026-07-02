<?php

namespace App\PageBuilder\Support;

final class TemplateCatalog
{
    /** @return array<string, array{label: string, view: string}> */
    public function all(): array
    {
        return [
            'default' => [
                'label' => 'Default page',
                'view' => 'generic-pagebuilder.templates.default',
            ],
            'landing' => [
                'label' => 'Landing page',
                'view' => 'generic-pagebuilder.templates.landing',
            ],
        ];
    }

    public function view(?string $template): string
    {
        return $this->all()[$template ?: 'default']['view'] ?? $this->all()['default']['view'];
    }
}

