<?php

namespace App\PageBuilder\Registry;

class SchemaExtractor
{
    public function extract(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        return $this->parse($content);
    }

    private function parse(string $content): ?array
    {
        $marker = '@pbSchema(';
        $start = strpos($content, $marker);
        if ($start === false) {
            return null;
        }

        $expression = $this->balancedArray($content, $start + strlen($marker));
        if ($expression === null) {
            return null;
        }

        try {
            $schema = eval('return '.$expression.';');
        } catch (\Throwable) {
            return null;
        }

        return is_array($schema) ? $schema : null;
    }

    private function balancedArray(string $content, int $start): ?string
    {
        $length = strlen($content);
        while ($start < $length && ctype_space($content[$start])) {
            $start++;
        }

        if ($start >= $length || $content[$start] !== '[') {
            return null;
        }

        $depth = 0;
        $inString = false;
        $quote = '';
        $escaped = false;

        for ($i = $start; $i < $length; $i++) {
            $char = $content[$i];
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $escaped = true;
                continue;
            }
            if ($inString) {
                if ($char === $quote) {
                    $inString = false;
                }
                continue;
            }
            if ($char === '\'' || $char === '"') {
                $inString = true;
                $quote = $char;
                continue;
            }
            if ($char === '[') {
                $depth++;
            } elseif ($char === ']') {
                $depth--;
                if ($depth === 0) {
                    return substr($content, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }
}
