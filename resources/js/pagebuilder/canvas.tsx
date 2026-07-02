import React, { useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { mapFor, selectionKey, type Zone } from './document';
import type { Asset, BlockNode, Catalog, DocumentModel, SectionNode, SelectedNode } from './types';

const mount = document.querySelector<HTMLElement>('#page-builder-canvas');
if (!mount) throw new Error('Canvas root is missing.');
const apiBase = mount.dataset.apiBase || '/pagebuilder/api';

type CanvasState = {
  document: DocumentModel;
  theme: Record<string, unknown>;
  catalog: Catalog;
  selected: string | null;
};

function stripHtml(value: unknown): string {
  return String(value ?? '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
}

function hasValue(value: unknown): boolean {
  return value !== null && value !== undefined && value !== '';
}

function Canvas(): React.JSX.Element {
  const [state, setState] = useState<CanvasState | null>(null);
  const [assets, setAssets] = useState<Record<string, Asset>>({});
  const unavailableAssets = useRef(new Set<string>());

  useEffect(() => {
    const listener = (event: MessageEvent) => {
      if (event.origin !== window.location.origin || event.source !== window.parent) return;
      const data = event.data as { channel?: string; type?: string; payload?: CanvasState };
      if (data.channel === 'pagebuilder-canvas' && data.type === 'render' && data.payload) {
        setState(data.payload);
      }
    };

    window.addEventListener('message', listener);
    window.parent.postMessage({ channel: 'pagebuilder-canvas', type: 'ready' }, window.location.origin);
    return () => window.removeEventListener('message', listener);
  }, []);

  const wantedAssetIds = useMemo(() => state ? collectAssetIds(state.document) : [], [state?.document]);

  useEffect(() => {
    if (!wantedAssetIds.length) return;

    let cancelled = false;
    const missing = wantedAssetIds.filter((id) => !assets[id] && !unavailableAssets.current.has(id));
    if (!missing.length) return;

    void Promise.all(missing.map(async (id) => {
      try {
        const response = await fetch(`${apiBase}/assets/${encodeURIComponent(id)}`, {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });

        if (!response.ok) return { id, asset: null };
        const payload = await response.json() as { data: Asset };
        return { id, asset: payload.data };
      } catch {
        return { id, asset: null };
      }
    })).then((items) => {
      if (cancelled) return;

      const resolved = items.filter((item): item is { id: string; asset: Asset } => item.asset !== null);
      items.filter((item) => item.asset === null).forEach((item) => unavailableAssets.current.add(item.id));
      if (!resolved.length) return;

      setAssets((current) => {
        const next = { ...current };
        resolved.forEach(({ asset }) => { next[asset.id] = asset; });
        return next;
      });
    });

    return () => { cancelled = true; };
  }, [wantedAssetIds.join(','), assets]);

  if (!state) return <div className="pb-preview-root" />;

  return <div className="pb-preview-root" style={themeStyles(state.theme)}>
    <ZoneView zone="header" state={state} assets={assets} />
    <ZoneView zone="main" state={state} assets={assets} />
    <ZoneView zone="footer" state={state} assets={assets} />
  </div>;
}

function collectAssetIds(document: DocumentModel): string[] {
  const ids = new Set<string>();
  const visitBlocks = (blocks: Record<string, BlockNode>): void => {
    Object.values(blocks).forEach((block) => {
      for (const key of ['asset_id', 'image_id']) {
        const value = block.settings[key];
        if (typeof value === 'string' && value !== '') ids.add(value);
      }
      visitBlocks(block.blocks);
    });
  };

  for (const zone of ['header', 'main', 'footer'] as Zone[]) {
    const map = mapFor(document, zone);
    Object.values(map.sections).forEach((section) => {
      for (const key of ['asset_id', 'image_id']) {
        const value = section.settings[key];
        if (typeof value === 'string' && value !== '') ids.add(value);
      }
      visitBlocks(section.blocks);
    });
  }

  return [...ids];
}

function ZoneView({ zone, state, assets }: { zone: Zone; state: CanvasState; assets: Record<string, Asset> }): React.JSX.Element {
  const map = mapFor(state.document, zone);
  return <>{map.order.map((id) => <SectionView key={id} zone={zone} id={id} section={map.sections[id]} state={state} assets={assets} />)}</>;
}

function SectionView({ zone, id, section, state, assets }: { zone: Zone; id: string; section: SectionNode; state: CanvasState; assets: Record<string, Asset> }): React.JSX.Element | null {
  if (section.disabled) return null;

  const node: SelectedNode = { kind: 'section', zone, id };
  const selected = state.selected === selectionKey(node);
  const choose = () => postSelect(node);
  const settings = section.settings;
  const blocks = () => section.order.map((blockId) => <BlockView key={blockId} zone={zone} sectionId={id} path={[blockId]} block={section.blocks[blockId]} state={state} assets={assets} />);
  const wrapper = (children: React.ReactNode, className = '') => <section className={`pb-preview-node ${selected ? 'selected' : ''} ${className}`} onClick={choose}>{children}</section>;

  if (section.type === 'announcement') {
    return wrapper(<div className="pb-preview-section" style={{ padding: '10px 7vw', textAlign: 'center', background: String(settings.background || '#111827'), color: String(settings.color || '#fff') }}>
      {String(settings.text || '')}
      {hasValue(settings.link_url) && <a style={{ marginLeft: 12, fontWeight: 800 }} href="#" onClick={(event) => event.preventDefault()}>{String(settings.link_text || '')}</a>}
    </div>);
  }

  if (section.type === 'header') {
    return wrapper(<header style={{ padding: '14px 7vw', background: '#fff', borderBottom: '1px solid #e7ebf1', position: Boolean(settings.sticky) ? 'sticky' : 'relative', top: 0, zIndex: 5 }}>
      <div className="pb-preview-content" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 20 }}>
        <strong style={{ fontSize: 20 }}>{String(settings.logo_text || '')}</strong>
        <nav style={{ display: 'flex', alignItems: 'center', gap: 14 }}>{blocks()}</nav>
        {hasValue(settings.cta_label) && <a className="pb-preview-button" href="#" onClick={(event) => event.preventDefault()}>{String(settings.cta_label)}</a>}
      </div>
    </header>);
  }

  if (section.type === 'hero') {
    const asset = typeof settings.image_id === 'string' ? assets[settings.image_id] : null;
    return wrapper(<div className={`pb-preview-section ${String(settings.variant) === 'dark' ? 'dark' : ''}`}>
      <div className={`pb-preview-hero ${settings.alignment === 'center' ? 'center' : ''}`}>
        <div>
          <p className="pb-preview-eyebrow">{String(settings.eyebrow || '')}</p>
          <h1>{String(settings.title || '')}</h1>
          <p className="pb-preview-copy">{String(settings.description || '')}</p>
          <div className="pb-preview-actions">
            <a href="#" className="pb-preview-button" onClick={(event) => event.preventDefault()}>{String(settings.primary_text || '')}</a>
            {hasValue(settings.secondary_url) && <a href="#" className="pb-preview-button secondary" onClick={(event) => event.preventDefault()}>{String(settings.secondary_text || '')}</a>}
          </div>
        </div>
        {asset ? <img className="pb-preview-image" src={asset.url} alt={asset.alt_text ?? ''} /> : hasValue(settings.image_id) ? <div className="pb-preview-card" style={{ minHeight: 240, display: 'grid', placeItems: 'center', color: '#6a778c' }}>Image unavailable</div> : null}
      </div>
    </div>);
  }

  if (section.type === 'rich_text') return wrapper(<div className="pb-preview-section"><div className={`pb-preview-prose ${String(settings.container || 'default')}`}><h2>{String(settings.title || '')}</h2><p>{stripHtml(settings.content)}</p></div></div>);
  if (section.type === 'feature_grid') return wrapper(<div className="pb-preview-section"><div className="pb-preview-prose"><p className="pb-preview-eyebrow">{String(settings.eyebrow || '')}</p><h2>{String(settings.title || '')}</h2><p>{String(settings.description || '')}</p></div><div className={`pb-preview-grid c${String(settings.columns || '3')}`}>{blocks()}</div></div>);
  if (section.type === 'cta') return wrapper(<div className="pb-preview-section dark"><div className="pb-preview-prose"><h2>{String(settings.title || '')}</h2><p>{String(settings.description || '')}</p><a className="pb-preview-button" href="#" onClick={(event) => event.preventDefault()}>{String(settings.button_text || '')}</a></div></div>);
  if (section.type === 'footer') return wrapper(<footer className="pb-preview-section dark"><div className="pb-preview-content"><div className={`pb-preview-grid c${Math.min(4, Math.max(2, section.order.length || 2))}`}>{blocks()}</div><div style={{ marginTop: 30, paddingTop: 16, borderTop: '1px solid rgba(255,255,255,.2)', opacity: .8 }}>{String(settings.copyright_text || '')}</div></div></footer>);

  return wrapper(<div className="pb-preview-section" style={{ background: String(settings.background || '#fff'), padding: spacing(String(settings.padding || 'large')) }}><div className="pb-preview-content">{blocks()}</div></div>);
}

function BlockView({ zone, sectionId, path, block, state, assets }: { zone: Zone; sectionId: string; path: string[]; block: BlockNode; state: CanvasState; assets: Record<string, Asset> }): React.JSX.Element | null {
  if (block.disabled) return null;

  const node: SelectedNode = { kind: 'block', zone, sectionId, blockPath: path };
  const selected = state.selected === selectionKey(node);
  const choose = (event: React.MouseEvent) => { event.stopPropagation(); postSelect(node); };
  const settings = block.settings;
  const children = () => block.order.map((id) => <BlockView key={id} zone={zone} sectionId={sectionId} path={[...path, id]} block={block.blocks[id]} state={state} assets={assets} />);
  const wrapper = (childrenValue: React.ReactNode, className = '') => <div className={`pb-preview-node ${selected ? 'selected' : ''} ${className}`} onClick={choose}>{childrenValue}</div>;

  if (block.type === 'nav_link') return wrapper(<a href="#" onClick={(event) => event.preventDefault()} style={{ fontSize: 14, fontWeight: 650, opacity: .75 }}>{String(settings.label || '')}</a>);
  if (block.type === 'row') return wrapper(<div className={`pb-preview-row columns-${String(settings.columns || '2')}`}>{children()}</div>);
  if (block.type === 'column') return wrapper(<div className="pb-preview-column" style={{ background: String(settings.background || 'transparent') }}>{children()}</div>);
  if (block.type === 'text') return wrapper(<div className={`pb-preview-text ${String(settings.alignment || 'left')}`}><p>{stripHtml(settings.content)}</p></div>);
  if (block.type === 'button') return wrapper(<div className={`pb-preview-text ${String(settings.alignment || 'left')}`}><a className={`pb-preview-button ${settings.style === 'secondary' ? 'secondary' : ''}`} href="#" onClick={(event) => event.preventDefault()}>{String(settings.text || '')}</a></div>);
  if (block.type === 'image') {
    const asset = typeof settings.asset_id === 'string' ? assets[settings.asset_id] : null;
    return wrapper(asset ? <img className={`pb-preview-img radius-${String(settings.radius || 'medium')}`} src={asset.url} alt={String(settings.alt || asset.alt_text || '')} /> : <div className="pb-preview-card" style={{ minHeight: 110, display: 'grid', placeItems: 'center', color: '#9aa7b8' }}>{hasValue(settings.asset_id) ? 'Image unavailable' : 'Choose an image'}</div>);
  }
  if (block.type === 'footer_column') return wrapper(<div><h3 style={{ margin: '0 0 12px' }}>{String(settings.title || '')}</h3><div style={{ display: 'grid', gap: 8 }}>{children()}</div></div>);
  if (block.type === 'footer_link') return wrapper(<a href="#" onClick={(event) => event.preventDefault()} style={{ fontSize: 13, opacity: .75 }}>{String(settings.label || '')}</a>);

  return wrapper(<article className="pb-preview-card"><p className="pb-preview-eyebrow">{String(settings.icon || '')}</p><h3>{String(settings.title || '')}</h3><p>{String(settings.description || '')}</p></article>);
}

function postSelect(selected: SelectedNode): void {
  window.parent.postMessage({ channel: 'pagebuilder-canvas', type: 'select', payload: { selection: selectionKey(selected) } }, window.location.origin);
}

function spacing(value: string): string {
  return value === 'small' ? '28px 7vw' : value === 'medium' ? '44px 7vw' : '64px 7vw';
}

function themeStyles(theme: Record<string, unknown>): React.CSSProperties {
  const style: Record<string, string> = {};
  if (theme['colors.primary']) style['--pb-primary'] = String(theme['colors.primary']);
  if (theme['colors.background']) style['--pb-background'] = String(theme['colors.background']);
  if (theme['colors.text']) style['--pb-text'] = String(theme['colors.text']);
  if (theme['fonts.display']) style['--pb-font-display'] = String(theme['fonts.display']);
  if (theme['fonts.body']) style['--pb-font-body'] = String(theme['fonts.body']);
  return style as React.CSSProperties;
}

createRoot(mount).render(<React.StrictMode><Canvas /></React.StrictMode>);
