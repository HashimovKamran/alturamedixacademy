<?php

namespace App\Http\Controllers\Admin;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class PageBuilderApiController extends Controller
{
    public function __construct(private readonly AlturaPageBuilderService $builder) {}

    public function bootstrap(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->builder->bootstrap($this->language($request), $this->pageKey($request))]);
    }

    public function pages(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->builder->pageList($this->language($request))]);
    }

    public function save(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'page_key' => ['required', 'string', 'max:120'], 'document' => ['required', 'array'],
            'theme_settings' => ['nullable', 'array'], 'expected_editor_revision' => ['required', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'], 'meta.title' => ['nullable', 'string', 'max:255'],
            'meta.meta_title' => ['nullable', 'string', 'max:255'], 'meta.meta_description' => ['nullable', 'string', 'max:500'],
            'meta.meta_keywords' => ['nullable', 'string', 'max:255'], 'meta.template' => ['nullable', 'string', 'max:120'],
        ]);
        try {
            $data = $this->builder->saveDraft($this->language($request), $payload['page_key'], $payload, $request->session()->get('admin_user_id'));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
        return response()->json(['data' => $data]);
    }

    public function publish(Request $request): JsonResponse
    {
        $payload = $request->validate(['page_key' => ['required', 'string', 'max:120'], 'revision_id' => ['required', 'integer', 'min:1'], 'change_note' => ['nullable', 'string', 'max:255']]);
        return response()->json(['data' => $this->builder->publish($this->language($request), $payload['page_key'], (int) $payload['revision_id'], $request->session()->get('admin_user_id'), $payload['change_note'] ?? null)]);
    }

    public function rollback(Request $request): JsonResponse
    {
        $payload = $request->validate(['page_key' => ['required', 'string', 'max:120'], 'revision_id' => ['required', 'integer', 'min:1']]);
        return response()->json(['data' => $this->builder->rollback($this->language($request), $payload['page_key'], (int) $payload['revision_id'], $request->session()->get('admin_user_id'))]);
    }

    public function history(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->builder->history($this->language($request), $this->pageKey($request))]);
    }

    public function archive(Request $request): JsonResponse
    {
        $payload = $request->validate(['page_key' => ['required', 'string', 'max:120']]);
        $this->builder->archive($this->language($request), $payload['page_key'], $request->session()->get('admin_user_id'));
        return response()->json(status: 204);
    }

    public function restore(Request $request): JsonResponse
    {
        $payload = $request->validate(['page_key' => ['required', 'string', 'max:120']]);
        return response()->json(['data' => $this->builder->restorePage($this->language($request), $payload['page_key'], $request->session()->get('admin_user_id'))]);
    }

    public function remove(Request $request): JsonResponse
    {
        $payload = $request->validate(['page_key' => ['required', 'string', 'max:120']]);
        $this->builder->deletePage($this->language($request), $payload['page_key'], $request->session()->get('admin_user_id'));
        return response()->json(status: 204);
    }

    private function language(Request $request): string { return AdminLanguage::selected($request); }
    private function pageKey(Request $request): string { return (string) $request->query('page_key', $request->input('page_key', 'index')); }
}
