import type { Asset, BootstrapPayload, PageMeta, Revision } from './types';

const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

async function request<T>(url: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(url, {
    ...init,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrf,
      ...(init.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
      ...(init.headers ?? {}),
    },
  });

  const payload = await response.json().catch(() => ({}));
  if (!response.ok) {
    const error = new Error(payload.message ?? firstError(payload.errors) ?? 'Request failed.');
    (error as Error & { status?: number }).status = response.status;
    throw error;
  }

  return payload.data as T;
}

function firstError(errors: unknown): string | null {
  if (!errors || typeof errors !== 'object') return null;
  const value = Object.values(errors as Record<string, unknown>)[0];
  return Array.isArray(value) && typeof value[0] === 'string' ? value[0] : null;
}

export function editorApi(base: string) {
  return {
    listPages: () => request<PageMeta[]>(`${base}/pages`),
    bootstrap: (slug: string) => request<BootstrapPayload>(`${base}/pages/${encodeURIComponent(slug)}`),
    save: (slug: string, body: Record<string, unknown>) =>
      request<{ page: PageMeta; draft: Revision }>(`${base}/pages/${encodeURIComponent(slug)}`, {
        method: 'PUT',
        body: JSON.stringify(body),
      }),
    publish: (slug: string, revisionId: string) =>
      request<Revision>(`${base}/pages/${encodeURIComponent(slug)}/publish`, {
        method: 'POST',
        body: JSON.stringify({ revision_id: revisionId }),
      }),
    rollback: (slug: string, revisionId: string) =>
      request<Revision>(`${base}/pages/${encodeURIComponent(slug)}/rollback`, {
        method: 'POST',
        body: JSON.stringify({ revision_id: revisionId }),
      }),
    archive: (slug: string) => request<void>(`${base}/pages/${encodeURIComponent(slug)}/archive`, { method: 'PATCH' }),
    restore: (slug: string) => request<PageMeta>(`${base}/pages/${encodeURIComponent(slug)}/restore`, { method: 'POST' }),
    destroy: (slug: string) => request<void>(`${base}/pages/${encodeURIComponent(slug)}`, { method: 'DELETE' }),
    history: (slug: string) => request<{ revisions: Revision[]; activities: Array<{ id: string; action: string; created_at: string }> }>(`${base}/pages/${encodeURIComponent(slug)}/history`),
    listAssets: (search = '') => request<{ data: Asset[] }>(`${base}/assets${search ? `?search=${encodeURIComponent(search)}` : ''}`),
    uploadAsset: async (file: File, altText = '') => {
      const body = new FormData();
      body.append('file', file);
      body.append('alt_text', altText);
      return request<Asset>(`${base}/assets`, { method: 'POST', body });
    },
    updateAsset: (id: string, altText: string) => request<Asset>(`${base}/assets/${encodeURIComponent(id)}`, { method: 'PATCH', body: JSON.stringify({ alt_text: altText }) }),
    deleteAsset: (id: string) => request<void>(`${base}/assets/${encodeURIComponent(id)}`, { method: 'DELETE' }),
  };
}
