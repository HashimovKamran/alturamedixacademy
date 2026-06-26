<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\Admin\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactMessage::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($q): void {
                $builder->where('full_name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%')
                    ->orWhere('subject', 'like', '%' . $q . '%')
                    ->orWhere('message', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%');
            });
        }

        if ($request->query('read') !== null && $request->query('read') !== '') {
            $query->where('is_read', $request->query('read') === '1');
        }

        return view('admin.contact_messages', [
            'messages' => $query->latest()->paginate(40)->withQueryString(),
            'stats' => [
                'total' => ContactMessage::query()->count(),
                'unread' => ContactMessage::query()->where('is_read', false)->count(),
                'read' => ContactMessage::query()->where('is_read', true)->count(),
                'today' => ContactMessage::query()->whereDate('created_at', now()->toDateString())->count(),
            ],
            'filters' => $request->only(['q', 'read']),
        ]);
    }

    public function read(Request $request, ContactMessage $message, AdminLogService $logs): RedirectResponse
    {
        $message->forceFill(['is_read' => true])->save();
        $logs->write($request, 'contact_messages', 'read', 'Mesaj oxundu edildi: ' . $message->full_name, 'ContactMessage', (int) $message->id);

        return back()->with('status', 'Mesaj oxundu edildi.');
    }

    public function destroy(Request $request, ContactMessage $message, AdminLogService $logs): RedirectResponse
    {
        $label = $message->full_name . ' / ' . $message->email;
        $id = (int) $message->id;
        $message->delete();

        $logs->write($request, 'contact_messages', 'delete', 'Mesaj silindi: ' . $label, 'ContactMessage', $id);

        return back()->with('status', 'Mesaj silindi.');
    }
}
