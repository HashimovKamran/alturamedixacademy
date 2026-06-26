<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\Site\SiteDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request, SiteDataService $site): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        ContactMessage::query()->create($data + ['ip_address' => $request->ip()]);

        return redirect()
            ->to(route('pages.contact', ['lang' => $site->language($request)]))
            ->with('contact_success', 'Mesajınız qəbul edildi. Tezliklə sizinlə əlaqə saxlanılacaq.');
    }
}
