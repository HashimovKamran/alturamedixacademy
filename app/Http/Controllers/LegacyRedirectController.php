<?php

namespace App\Http\Controllers;

use App\Support\CleanUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegacyRedirectController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $target = CleanUrl::redirectPath($request);

        if ($target === null) {
            throw new NotFoundHttpException();
        }

        $status = $request->isMethod('GET') || $request->isMethod('HEAD') ? 301 : 307;

        return redirect()->to($target, $status);
    }
}
