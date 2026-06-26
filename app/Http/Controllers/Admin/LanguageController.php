<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        AdminLanguage::set($request, (string) $request->input('lang_code', ''));

        return redirect()->to($this->previousWithoutLanguage($request));
    }

    private function previousWithoutLanguage(Request $request): string
    {
        $previous = url()->previous(route('admin.dashboard'));
        $parts = parse_url($previous);
        if (! is_array($parts) || empty($parts['path'])) {
            return route('admin.dashboard');
        }

        $query = [];
        if (! empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
            unset($query['lang_code']);
        }

        $url = ($parts['scheme'] ?? $request->getScheme()) . '://' . ($parts['host'] ?? $request->getHost());
        if (! empty($parts['port'])) {
            $url .= ':' . $parts['port'];
        }
        $url .= $parts['path'];

        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}
