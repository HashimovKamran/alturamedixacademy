import React, { useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
  Archive, CirclePlus, Copy, Eye, FolderTree, History, ImagePlus, LayoutPanelLeft,
  Monitor, Redo2, RotateCcw, Save, Smartphone, Tablet, Trash2, Undo2, Wand2, X,
} from 'lucide-react';
import { editorApi } from './api';
import {
  blockAt, clone, componentDefaults, createId, fromSelectionKey, mapFor, nodeAt,
  parentCollection, parentOrder, reorder, sameParent, schemaAt, sectionAt, selectionKey, setMap, type Zone,
} from './document';
import type {
  Asset, BlockNode, BootstrapPayload, Catalog, ComponentSchema, DocumentModel, FieldSchema,
  PageMeta, Revision, SectionNode, SelectedNode, Snapshot,
} from './types';

const mount = document.querySelector<HTMLElement>('#page-builder-root');
if (!mount) throw new Error('Page Builder root is missing.');

const apiBase = mount.dataset.apiBase || '/pagebuilder/api';
const editorBase = mount.dataset.editorBase || '/pagebuilder/editor';
const canvasBase = mount.dataset.canvasBase || '/pagebuilder/canvas';
const canvasMode = mount.dataset.canvasMode || 'react';
const previewBase = mount.dataset.previewBase || canvasBase;
const publicOrigin = mount.dataset.publicOrigin || window.location.origin;
const initialSlug = mount.dataset.slug || 'home';
const api = editorApi(apiBase);

type Device = 'desktop' | 'tablet' | 'mobile';
type ViewTab = 'structure' | 'page' | 'theme';
type Dialog = null | 'sections' | 'blocks' | 'media' | 'history' | 'pages';
type DragItem =
  | { kind: 'section'; zone: Zone; id: string }
  | { kind: 'block'; zone: Zone; sectionId: string; path: string[] };

function text(value: unknown): string {
  return value === null || value === undefined ? '' : String(value);
}

function titleFor(slug: string): string {
  return slug === 'home' ? 'Home' : slug.split('/').at(-1)!.replaceAll('-', ' ').replace(/\b\w/g, (value) => value.toUpperCase());
}

function defaultMeta(slug: string): PageMeta {
  return { title: titleFor(slug), meta_title: null, meta_description: null, meta_keywords: null, template: null };
}

function previewUrlFor(slug: string): string {
  return canvasMode === 'public' ? `${previewBase}/${slug}` : `${canvasBase}/${slug}`;
}

function liveUrlFor(slug: string): string {
  return `${publicOrigin}${slug === 'home' ? '/' : `/${slug}`}`;
}

function readDrag(event: React.DragEvent): DragItem | null {
  try {
    return JSON.parse(event.dataTransfer.getData('application/pagebuilder')) as DragItem;
  } catch {
    return null;
  }
}

function App(): React.JSX.Element {
  const [slug, setSlug] = useState(initialSlug);
  const [catalog, setCatalog] = useState<Catalog | null>(null);
  const [documentModel, setDocumentModel] = useState<DocumentModel | null>(null);
  const [theme, setTheme] = useState<Record<string, unknown>>({});
  const [meta, setMeta] = useState<PageMeta>(defaultMeta(initialSlug));
  const [draft, setDraft] = useState<Revision | null>(null);
  const [revisions, setRevisions] = useState<Revision[]>([]);
  const [pages, setPages] = useState<PageMeta[]>([]);
  const [selected, setSelected] = useState<SelectedNode | null>(null);
  const [tab, setTab] = useState<ViewTab>('structure');
  const [structureZone, setStructureZone] = useState<Zone>('main');
  const [device, setDevice] = useState<Device>('desktop');
  const [past, setPast] = useState<Snapshot[]>([]);
  const [future, setFuture] = useState<Snapshot[]>([]);
  const [dialog, setDialog] = useState<Dialog>(null);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState('');
  const [message, setMessage] = useState<{ text: string; error: boolean } | null>(null);
  const [assetSetter, setAssetSetter] = useState<(asset: Asset | null) => void>(() => () => undefined);
  const iframe = useRef<HTMLIFrameElement>(null);

  const snapshot = (): Snapshot | null => documentModel
    ? { document: clone(documentModel), theme: clone(theme), meta: clone(meta) }
    : null;
  const dirty = useMemo(
    () => documentModel ? JSON.stringify({ document: documentModel, theme, meta }) !== saved : false,
    [documentModel, theme, meta, saved],
  );

  const notify = (value: string, error = false) => {
    setMessage({ text: value, error });
    window.setTimeout(() => setMessage(null), 3500);
  };

  const hydrate = (payload: BootstrapPayload, availablePages: PageMeta[], nextSlug: string) => {
    const initialMeta = payload.page ?? defaultMeta(nextSlug);
    setSlug(nextSlug);
    setCatalog(payload.catalog);
    setDocumentModel(payload.document);
    setTheme(payload.theme_settings ?? {});
    setMeta(initialMeta);
    setDraft(payload.draft);
    setRevisions(payload.revisions);
    setPages(availablePages);
    setPast([]);
    setFuture([]);
    setSelected(null);
    setSaved(JSON.stringify({ document: payload.document, theme: payload.theme_settings ?? {}, meta: initialMeta }));
  };

  const load = async (nextSlug: string) => {
    try {
      const [payload, availablePages] = await Promise.all([api.bootstrap(nextSlug), api.listPages()]);
      hydrate(payload, availablePages, nextSlug);
    } catch (error) {
      notify(error instanceof Error ? error.message : 'Unable to load this page.', true);
    }
  };

  const refreshPages = async () => {
    try {
      setPages(await api.listPages());
    } catch (error) {
      notify(error instanceof Error ? error.message : 'Unable to refresh the page list.', true);
    }
  };

  useEffect(() => { void load(initialSlug); }, []);

  const pushCanvas = () => {
    if (canvasMode === 'public' || !iframe.current?.contentWindow || !documentModel || !catalog) return;
    iframe.current.contentWindow.postMessage({
      channel: 'pagebuilder-canvas',
      type: 'render',
      payload: { document: documentModel, theme, catalog, selected: selectionKey(selected) },
    }, window.location.origin);
  };

  useEffect(() => { pushCanvas(); }, [documentModel, theme, selected, catalog]);

  useEffect(() => {
    if (canvasMode === 'public') return;

    const listener = (event: MessageEvent) => {
      if (event.origin !== window.location.origin || event.source !== iframe.current?.contentWindow) return;
      const data = event.data as { channel?: string; type?: string; payload?: { selection?: string } };
      if (data.channel !== 'pagebuilder-canvas') return;
      if (data.type === 'ready') pushCanvas();
      if (data.type === 'select' && data.payload?.selection) setSelected(fromSelectionKey(data.payload.selection));
    };

    window.addEventListener('message', listener);
    return () => window.removeEventListener('message', listener);
  });

  function mutate(fn: (nextDocument: DocumentModel, nextTheme: Record<string, unknown>, nextMeta: PageMeta) => void, record = true): void {
    if (!documentModel) return;

    const before = snapshot();
    const nextDocument = clone(documentModel);
    const nextTheme = clone(theme);
    const nextMeta = clone(meta);
    fn(nextDocument, nextTheme, nextMeta);

    if (record && before) {
      setPast((items) => [...items.slice(-79), before]);
      setFuture([]);
    }

    setDocumentModel(nextDocument);
    setTheme(nextTheme);
    setMeta(nextMeta);
  }

  function undo(): void {
    const previous = past.at(-1);
    const current = snapshot();
    if (!previous || !current) return;

    setPast((items) => items.slice(0, -1));
    setFuture((items) => [...items, current]);
    setDocumentModel(clone(previous.document));
    setTheme(clone(previous.theme));
    setMeta(clone(previous.meta));
  }

  function redo(): void {
    const next = future.at(-1);
    const current = snapshot();
    if (!next || !current) return;

    setFuture((items) => items.slice(0, -1));
    setPast((items) => [...items, current]);
    setDocumentModel(clone(next.document));
    setTheme(clone(next.theme));
    setMeta(clone(next.meta));
  }

  function addSection(type: string): void {
    if (!documentModel || !catalog) return;

    const component = catalog.sections[type];
    if (!component || (component.zones && !component.zones.includes(structureZone))) return;

    const id = createId(type);
    mutate((next) => {
      const map = mapFor(next, structureZone);
      map.sections[id] = { type, _name: null, disabled: false, slot_key: 'default', settings: componentDefaults(component), blocks: {}, order: [] };
      map.order.push(id);
      setMap(next, structureZone, map);
    });

    setSelected({ kind: 'section', zone: structureZone, id });
    setDialog(null);
  }

  function addBlock(type: string): void {
    if (!documentModel || !catalog || !selected) return;

    const parentSchema = schemaAt(documentModel, catalog, selected);
    const component = catalog.blocks[type] ?? catalog.sections[type];
    if (!parentSchema || !component || !parentSchema.blocks.includes(type)) return;

    const id = createId(type);
    mutate((next) => {
      const parent = nodeAt(next, selected);
      if (!parent) return;
      parent.blocks[id] = { type, _name: null, disabled: false, slot_key: parentSchema.slots?.[0] ?? 'default', settings: componentDefaults(component), blocks: {}, order: [] };
      parent.order.push(id);
    });

    if (selected.kind === 'section') {
      setSelected({ kind: 'block', zone: selected.zone, sectionId: selected.id, blockPath: [id] });
    } else {
      setSelected({ kind: 'block', zone: selected.zone, sectionId: selected.sectionId, blockPath: [...selected.blockPath, id] });
    }

    setDialog(null);
  }

  function removeSelected(): void {
    if (!documentModel || !selected || !window.confirm(`Delete this ${selected.kind}?`)) return;

    mutate((next) => {
      if (selected.kind === 'section') {
        const map = mapFor(next, selected.zone);
        delete map.sections[selected.id];
        map.order = map.order.filter((item) => item !== selected.id);
        setMap(next, selected.zone, map);
        return;
      }

      const section = sectionAt(next, selected.zone, selected.sectionId);
      if (!section) return;
      const collection = parentCollection(section, selected.blockPath);
      const order = parentOrder(section, selected.blockPath);
      const id = selected.blockPath.at(-1)!;
      if (collection) delete collection[id];
      if (order) order.splice(order.indexOf(id), 1);
    });

    setSelected(null);
  }

  function duplicateSelected(): void {
    if (!documentModel || !selected) return;

    let nextSelection: SelectedNode | null = null;
    mutate((next) => {
      if (selected.kind === 'section') {
        const map = mapFor(next, selected.zone);
        const source = map.sections[selected.id];
        if (!source) return;

        const id = createId(source.type);
        map.sections[id] = clone(source);
        map.order.splice(map.order.indexOf(selected.id) + 1, 0, id);
        setMap(next, selected.zone, map);
        nextSelection = { kind: 'section', zone: selected.zone, id };
        return;
      }

      const section = sectionAt(next, selected.zone, selected.sectionId);
      if (!section) return;
      const source = blockAt(section, selected.blockPath);
      const collection = parentCollection(section, selected.blockPath);
      const order = parentOrder(section, selected.blockPath);
      if (!source || !collection || !order) return;

      const id = createId(source.type);
      collection[id] = clone(source);
      order.splice(order.indexOf(selected.blockPath.at(-1)!) + 1, 0, id);
      nextSelection = { kind: 'block', zone: selected.zone, sectionId: selected.sectionId, blockPath: [...selected.blockPath.slice(0, -1), id] };
    });

    if (nextSelection) setSelected(nextSelection);
  }

  function move(source: DragItem, target: DragItem): void {
    if (!documentModel || source.zone !== target.zone || source.kind !== target.kind) return;

    if (source.kind === 'section' && target.kind === 'section') {
      mutate((next) => {
        const map = mapFor(next, source.zone);
        reorder(map.order, source.id, target.id);
        setMap(next, source.zone, map);
      });
      return;
    }

    if (source.kind !== 'block' || target.kind !== 'block') return;
    if (source.sectionId !== target.sectionId || !sameParent(source.path, target.path)) return;

    mutate((next) => {
      const section = sectionAt(next, source.zone, source.sectionId);
      const order = section ? parentOrder(section, source.path) : null;
      if (order) reorder(order, source.path.at(-1)!, target.path.at(-1)!);
    });
  }

  async function save(): Promise<Revision | null> {
    if (!documentModel || saving) return null;

    setSaving(true);
    try {
      const result = await api.save(slug, {
        document: documentModel,
        theme_settings: theme,
        expected_editor_revision: draft?.editor_revision ?? 0,
        meta,
      });

      setDraft(result.draft);
      setMeta(result.page);
      setPages((items) => [result.page, ...items.filter((item) => item.slug !== result.page.slug)]);
      setRevisions((items) => [result.draft, ...items.filter((item) => item.id !== result.draft.id)]);
      setSaved(JSON.stringify({ document: documentModel, theme, meta: result.page }));
      if (canvasMode === 'public') iframe.current?.contentWindow?.location.reload();
      notify('Draft saved.');
      return result.draft;
    } catch (error) {
      notify(error instanceof Error ? error.message : 'The draft could not be saved.', true);
      return null;
    } finally {
      setSaving(false);
    }
  }

  async function publish(): Promise<void> {
    const currentDraft = await save();
    if (!currentDraft) return;

    try {
      const published = await api.publish(slug, currentDraft.id);
      setDraft(null);
      setRevisions((items) => [published, ...items.filter((item) => item.id !== published.id)]);
      setSaved(JSON.stringify({ document: documentModel, theme, meta }));
      await refreshPages();
      notify(`Revision ${published.revision_number} is live.`);
    } catch (error) {
      notify(error instanceof Error ? error.message : 'The page could not be published.', true);
    }
  }

  async function rollback(revision: Revision): Promise<void> {
    if (revision.status === 'draft') return;
    if (!window.confirm(`Restore revision ${revision.revision_number}? This creates a new published revision.`)) return;

    try {
      const restored = await api.rollback(slug, revision.id);
      setDraft(null);
      setDocumentModel(clone(restored.document));
      setTheme(clone(restored.theme_settings ?? {}));
      setRevisions((items) => [restored, ...items.filter((item) => item.id !== restored.id)]);
      setSaved(JSON.stringify({ document: restored.document, theme: restored.theme_settings ?? {}, meta }));
      setDialog(null);
      await refreshPages();
      notify(`Revision ${revision.revision_number} restored.`);
    } catch (error) {
      notify(error instanceof Error ? error.message : 'Rollback failed.', true);
    }
  }

  async function archivePage(page: PageMeta): Promise<void> {
    if (!page.slug || !window.confirm(`Archive ${page.title}? The public URL will return 404 until restored or republished.`)) return;

    try {
      await api.archive(page.slug);
      await refreshPages();
      if (page.slug === slug) notify('This page is archived. Publish a draft or restore it to make it public again.');
      else notify('Page archived.');
    } catch (error) {
      notify(error instanceof Error ? error.message : 'The page could not be archived.', true);
    }
  }

  async function restorePage(page: PageMeta): Promise<void> {
    if (!page.slug) return;

    try {
      await api.restore(page.slug);
      await refreshPages();
      notify('Page restored.');
    } catch (error) {
      notify(error instanceof Error ? error.message : 'The page could not be restored.', true);
    }
  }

  function updateSetting(key: string, value: unknown): void {
    if (!selected) return;
    mutate((next) => {
      const node = nodeAt(next, selected);
      if (node) node.settings[key] = value;
    });
  }

  if (!catalog || !documentModel) return <div className="pb-empty">Loading Page Builder…</div>;

  const selectedNode = nodeAt(documentModel, selected);
  const selectedSchema = schemaAt(documentModel, catalog, selected);
  const canvasWidth = device === 'desktop' ? '' : device;
  const sectionsForCurrentZone = Object.fromEntries(
    Object.entries(catalog.sections).filter(([, section]) => !section.zones || section.zones.includes(structureZone)),
  ) as Record<string, ComponentSchema>;
  const allowedBlocks = selectedSchema
    ? Object.fromEntries(
      selectedSchema.blocks
        .map((type) => [type, catalog.blocks[type] ?? catalog.sections[type]] as const)
        .filter((item): item is readonly [string, ComponentSchema] => Boolean(item[1])),
    ) as Record<string, ComponentSchema>
    : {};

  return <div className="pb-shell">
    <header className="pb-topbar">
      <div className="pb-toolbar">
        <div className="pb-brand"><span className="pb-brand-mark">PB</span>Page Builder</div>
        <select className="pb-page-select" value={slug} onChange={(event) => window.location.assign(`${editorBase}/${event.target.value}`)}>
          {pages.map((page) => <option key={page.slug ?? page.title} value={page.slug}>{page.title}</option>)}
          {!pages.some((page) => page.slug === slug) && <option value={slug}>{meta.title}</option>}
        </select>
        <button className="pb-icon" title="Pages" onClick={() => setDialog('pages')}><FolderTree size={16} /></button>
      </div>
      <div className="pb-toolbar">
        <button className="pb-icon" disabled={!past.length} onClick={undo}><Undo2 size={16} /></button>
        <button className="pb-icon" disabled={!future.length} onClick={redo}><Redo2 size={16} /></button>
        <span className="pb-save-state">{saving ? 'Saving…' : dirty ? 'Unsaved changes' : 'Saved'}</span>
        <button className="pb-icon" onClick={() => setDevice('desktop')}><Monitor size={16} /></button>
        <button className="pb-icon" onClick={() => setDevice('tablet')}><Tablet size={16} /></button>
        <button className="pb-icon" onClick={() => setDevice('mobile')}><Smartphone size={16} /></button>
      </div>
      <div className="pb-toolbar">
        <button className="pb-button" onClick={() => setDialog('history')}><History size={14} />History</button>
        <button className="pb-button" onClick={() => window.open(liveUrlFor(slug), '_blank', 'noopener')}><Eye size={14} />Live</button>
        <button className="pb-button primary" onClick={() => void save()} disabled={saving}><Save size={14} />Save</button>
        <button className="pb-button dark" onClick={() => void publish()} disabled={saving}><Wand2 size={14} />Publish</button>
      </div>
    </header>

    <main className="pb-workspace">
      <aside className="pb-sidebar">
        <nav className="pb-tabs">
          <button className={`pb-tab ${tab === 'structure' ? 'active' : ''}`} onClick={() => setTab('structure')}>Structure</button>
          <button className={`pb-tab ${tab === 'page' ? 'active' : ''}`} onClick={() => setTab('page')}>Page</button>
          <button className={`pb-tab ${tab === 'theme' ? 'active' : ''}`} onClick={() => setTab('theme')}>Theme</button>
        </nav>
        {tab === 'structure' && <Structure document={documentModel} catalog={catalog} selected={selected} zone={structureZone} onZone={setStructureZone} onSelect={setSelected} onMove={move} onAdd={() => setDialog('sections')} />}
        {tab === 'page' && <PageForm meta={meta} templates={catalog.templates} onChange={(key, value) => mutate((_document, _theme, nextMeta) => { (nextMeta as Record<string, unknown>)[key] = value; })} />}
        {tab === 'theme' && <ThemeForm catalog={catalog} values={theme} onChange={(key, value) => mutate((_document, nextTheme) => { nextTheme[key] = value; })} />}
      </aside>

      <section className="pb-stage">
        <span className="pb-stage-meta">{device === 'desktop' ? 'Desktop preview' : `${device[0].toUpperCase()}${device.slice(1)} preview`}</span>
        <div className={`pb-stage-frame ${canvasWidth}`}>
          <iframe ref={iframe} title="Visual page preview" sandbox="allow-scripts allow-same-origin allow-forms" src={previewUrlFor(slug)} onLoad={pushCanvas} />
        </div>
      </section>

      <aside className="pb-inspector">
        <div className="pb-inspector-head"><h2>{selectedSchema?.label ?? 'Settings'}</h2><p>{selected ? `${selected.kind} settings` : 'Select a section or block to configure it.'}</p></div>
        <div className="pb-panel-content">
          {!selected || !selectedNode || !selectedSchema ? <div className="pb-empty">Nothing selected.</div> : <>
            <div className="pb-toolbar" style={{ justifyContent: 'space-between', marginTop: 12 }}>
              <button className="pb-button" onClick={duplicateSelected}><Copy size={14} />Duplicate</button>
              {selectedSchema.blocks.length > 0 && <button className="pb-button" onClick={() => setDialog('blocks')}><CirclePlus size={14} />Add block</button>}
            </div>
            {Object.entries(selectedSchema.fields).map(([key, field]) => <EditorField key={key} field={field} value={selectedNode.settings[key]} onChange={(value) => updateSetting(key, value)} onChooseAsset={(setter) => { setAssetSetter(() => setter); setDialog('media'); }} />)}
            <label className="pb-switch"><span>Hide this {selected.kind}</span><input type="checkbox" checked={selectedNode.disabled} onChange={(event) => mutate((next) => { const node = nodeAt(next, selected); if (node) node.disabled = event.target.checked; })} /></label>
            <button className="pb-button danger" onClick={removeSelected}><Trash2 size={14} />Delete {selected.kind}</button>
          </>}
        </div>
      </aside>
    </main>

    {dialog === 'sections' && <CatalogDialog title={`Add ${structureZone} section`} items={sectionsForCurrentZone} onClose={() => setDialog(null)} onSelect={addSection} />}
    {dialog === 'blocks' && selectedSchema && <CatalogDialog title="Add block" items={allowedBlocks} onClose={() => setDialog(null)} onSelect={addBlock} />}
    {dialog === 'media' && <MediaDialog onClose={() => setDialog(null)} onSelect={(asset) => { assetSetter(asset); setDialog(null); }} />}
    {dialog === 'history' && <HistoryDialog revisions={revisions} onClose={() => setDialog(null)} onRestore={rollback} />}
    {dialog === 'pages' && <PagesDialog pages={pages} current={slug} onClose={() => setDialog(null)} onOpen={(next) => window.location.assign(`${editorBase}/${next}`)} onArchive={archivePage} onRestore={restorePage} />}
    {message && <div className={`pb-toast ${message.error ? 'error' : ''}`}>{message.text}</div>}
  </div>;
}

function Structure({ document, catalog, selected, zone, onZone, onSelect, onMove, onAdd }: { document: DocumentModel; catalog: Catalog; selected: SelectedNode | null; zone: Zone; onZone: (zone: Zone) => void; onSelect: (item: SelectedNode) => void; onMove: (source: DragItem, target: DragItem) => void; onAdd: () => void }): React.JSX.Element {
  const map = mapFor(document, zone);
  return <div className="pb-panel-content">
    <div className="pb-panel-head" style={{ paddingInline: 0 }}><h2 className="pb-panel-title">Page structure</h2><button className="pb-button primary" onClick={onAdd}><CirclePlus size={14} />Add</button></div>
    <div className="pb-toolbar" style={{ marginBottom: 8 }}>{(['main', 'header', 'footer'] as Zone[]).map((item) => <button key={item} className={`pb-button ${zone === item ? 'primary' : ''}`} onClick={() => onZone(item)}>{item[0].toUpperCase() + item.slice(1)}</button>)}</div>
    {map.order.length === 0 && <div className="pb-empty">No sections in this zone.</div>}
    {map.order.map((id) => <SectionRow key={id} zone={zone} id={id} section={map.sections[id]} catalog={catalog} selected={selected} onSelect={onSelect} onMove={onMove} />)}
  </div>;
}

function SectionRow({ zone, id, section, catalog, selected, onSelect, onMove }: { zone: Zone; id: string; section: SectionNode; catalog: Catalog; selected: SelectedNode | null; onSelect: (item: SelectedNode) => void; onMove: (source: DragItem, target: DragItem) => void }): React.JSX.Element {
  const item: DragItem = { kind: 'section', zone, id };
  const active = selected?.kind === 'section' && selected.zone === zone && selected.id === id;
  return <div draggable onDragStart={(event) => event.dataTransfer.setData('application/pagebuilder', JSON.stringify(item))} onDragOver={(event) => event.preventDefault()} onDrop={(event) => { event.preventDefault(); const source = readDrag(event); if (source) onMove(source, item); }}>
    <TreeButton selected={active} disabled={section.disabled} title={section._name || catalog.sections[section.type]?.label || section.type} detail={section.type} onClick={() => onSelect({ kind: 'section', zone, id })} />
    {section.order.map((blockId) => <BlockRow key={blockId} zone={zone} sectionId={id} path={[blockId]} block={section.blocks[blockId]} catalog={catalog} selected={selected} onSelect={onSelect} onMove={onMove} />)}
  </div>;
}

function BlockRow({ zone, sectionId, path, block, catalog, selected, onSelect, onMove }: { zone: Zone; sectionId: string; path: string[]; block: BlockNode; catalog: Catalog; selected: SelectedNode | null; onSelect: (item: SelectedNode) => void; onMove: (source: DragItem, target: DragItem) => void }): React.JSX.Element {
  const item: DragItem = { kind: 'block', zone, sectionId, path };
  const active = selected?.kind === 'block' && selected.zone === zone && selected.sectionId === sectionId && selected.blockPath.join('.') === path.join('.');
  return <div className="pb-tree-indent" draggable onDragStart={(event) => event.dataTransfer.setData('application/pagebuilder', JSON.stringify(item))} onDragOver={(event) => event.preventDefault()} onDrop={(event) => { event.preventDefault(); const source = readDrag(event); if (source) onMove(source, item); }}>
    <TreeButton selected={active} disabled={block.disabled} title={block._name || catalog.blocks[block.type]?.label || block.type} detail={block.type} onClick={() => onSelect({ kind: 'block', zone, sectionId, blockPath: path })} />
    {block.order.map((child) => <BlockRow key={child} zone={zone} sectionId={sectionId} path={[...path, child]} block={block.blocks[child]} catalog={catalog} selected={selected} onSelect={onSelect} onMove={onMove} />)}
  </div>;
}

function TreeButton({ title, detail, selected, disabled, onClick }: { title: string; detail: string; selected: boolean; disabled: boolean; onClick: () => void }): React.JSX.Element {
  return <button className={`pb-tree-row ${selected ? 'selected' : ''} ${disabled ? 'disabled' : ''}`} onClick={onClick}><LayoutPanelLeft size={14} /><span className="pb-tree-label">{title}</span><span className="pb-tree-type">{detail}</span></button>;
}

function PageForm({ meta, templates, onChange }: { meta: PageMeta; templates: Catalog['templates']; onChange: (key: keyof PageMeta, value: string) => void }): React.JSX.Element {
  return <div className="pb-panel-content">
    <div className="pb-panel-head" style={{ paddingInline: 0 }}><h2 className="pb-panel-title">Page settings</h2></div>
    <Input label="Page title" value={meta.title} onChange={(value) => onChange('title', value)} />
    <div className="pb-field"><label>Template</label><select className="pb-select" value={meta.template ?? ''} onChange={(event) => onChange('template', event.target.value)}><option value="">Default page</option>{Object.entries(templates).map(([key, template]) => <option key={key} value={key}>{template.label}</option>)}</select></div>
    <Input label="SEO title" value={meta.meta_title ?? ''} onChange={(value) => onChange('meta_title', value)} />
    <Textarea label="SEO description" value={meta.meta_description ?? ''} onChange={(value) => onChange('meta_description', value)} />
    <Input label="SEO keywords" value={meta.meta_keywords ?? ''} onChange={(value) => onChange('meta_keywords', value)} />
  </div>;
}

function ThemeForm({ catalog, values, onChange }: { catalog: Catalog; values: Record<string, unknown>; onChange: (key: string, value: unknown) => void }): React.JSX.Element {
  return <div className="pb-panel-content"><div className="pb-panel-head" style={{ paddingInline: 0 }}><h2 className="pb-panel-title">Theme settings</h2></div>{catalog.theme_settings.map((group) => <React.Fragment key={group.name}><p style={{ color: '#6a778c', fontSize: 11, fontWeight: 800, textTransform: 'uppercase', marginTop: 18 }}>{group.name}</p>{group.settings.map((setting) => <EditorField key={setting.key} field={{ type: setting.type, label: setting.label, default: setting.default }} value={values[setting.key] ?? setting.default} onChange={(value) => onChange(setting.key, value)} onChooseAsset={() => undefined} />)}</React.Fragment>)}</div>;
}

function EditorField({ field, value, onChange, onChooseAsset }: { field: FieldSchema; value: unknown; onChange: (value: unknown) => void; onChooseAsset: (setter: (asset: Asset | null) => void) => void }): React.JSX.Element {
  if (field.type === 'checkbox') return <label className="pb-switch"><span>{field.label}</span><input type="checkbox" checked={Boolean(value)} onChange={(event) => onChange(event.target.checked)} /></label>;
  if (field.type === 'textarea' || field.type === 'rich_text' || field.type === 'inline_richtext' || field.type === 'html') return <Textarea label={field.label} value={text(value)} onChange={onChange} hint={field.type.includes('rich') || field.type === 'html' ? 'HTML is sanitized when the draft is saved.' : undefined} />;
  if (field.type === 'select' || field.type === 'radio' || field.type === 'text_alignment') return <div className="pb-field"><label>{field.label}</label><select className="pb-select" value={text(value)} onChange={(event) => onChange(event.target.value)}>{(field.options ?? []).map((option) => <option key={option} value={option}>{option.replaceAll('_', ' ')}</option>)}</select></div>;
  if (field.type === 'color' || field.type === 'color_background') return <div className="pb-field"><label>{field.label}</label><div className="pb-color-row"><input type="color" value={text(value).startsWith('#') ? text(value).slice(0, 7) : '#000000'} onChange={(event) => onChange(event.target.value)} /><input className="pb-input" value={text(value)} onChange={(event) => onChange(event.target.value)} /></div></div>;
  if (field.type === 'asset' || field.type === 'image_picker') return <AssetEditor label={field.label} value={text(value)} onChange={onChange} onChooseAsset={onChooseAsset} />;
  if (field.type === 'number' || field.type === 'range') return <div className="pb-field"><label>{field.label}</label><input className="pb-input" type="number" min={field.min} max={field.max} step={field.step} value={text(value)} onChange={(event) => onChange(Number(event.target.value || 0))} /></div>;
  if (field.type === 'heading') return <h3 style={{ margin: '16px 0 8px' }}>{field.label}</h3>;
  if (field.type === 'external') return <div className="pb-field"><label>{field.label}</label><small style={{ color: '#6a778c' }}>This setting is supplied by the application.</small></div>;
  return <Input label={field.label} type={field.type === 'url' || field.type === 'video_url' ? 'url' : 'text'} value={text(value)} onChange={onChange} />;
}

function AssetEditor({ label, value, onChange, onChooseAsset }: { label: string; value: string; onChange: (value: unknown) => void; onChooseAsset: (setter: (asset: Asset | null) => void) => void }): React.JSX.Element {
  return <div className="pb-field"><label>{label}</label><div className="pb-asset-picker"><input className="pb-input" value={value} readOnly placeholder="No asset selected" /><button className="pb-button" onClick={() => onChooseAsset((asset) => onChange(asset?.id ?? null))}><ImagePlus size={14} />Choose</button>{value && <button className="pb-icon" onClick={() => onChange(null)}><X size={14} /></button>}</div></div>;
}

function Input({ label, value, onChange, type = 'text' }: { label: string; value: string; onChange: (value: string) => void; type?: string }): React.JSX.Element {
  return <div className="pb-field"><label>{label}</label><input className="pb-input" type={type} value={value} onChange={(event) => onChange(event.target.value)} /></div>;
}

function Textarea({ label, value, onChange, hint }: { label: string; value: string; onChange: (value: string) => void; hint?: string }): React.JSX.Element {
  return <div className="pb-field"><label>{label}</label>{hint && <small style={{ color: '#6a778c' }}>{hint}</small>}<textarea className="pb-textarea" value={value} onChange={(event) => onChange(event.target.value)} /></div>;
}

function CatalogDialog({ title, items, onClose, onSelect }: { title: string; items: Record<string, ComponentSchema>; onClose: () => void; onSelect: (type: string) => void }): React.JSX.Element {
  return <Modal title={title} onClose={onClose}><div className="pb-catalog">{Object.entries(items).map(([type, item]) => <button key={type} className="pb-catalog-card" onClick={() => onSelect(type)}><strong>{item.label}</strong><span>{item.blocks.length ? 'Container component' : 'Standalone component'}</span></button>)}</div></Modal>;
}

function MediaDialog({ onClose, onSelect }: { onClose: () => void; onSelect: (asset: Asset | null) => void }): React.JSX.Element {
  const [assets, setAssets] = useState<Asset[]>([]);
  const [search, setSearch] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const file = useRef<HTMLInputElement>(null);

  const load = async (query = '') => {
    setBusy(true);
    setError(null);
    try {
      const result = await api.listAssets(query);
      setAssets(result.data);
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Media could not be loaded.');
    } finally {
      setBusy(false);
    }
  };

  useEffect(() => { void load(); }, []);

  return <Modal title="Media library" onClose={onClose}>
    <div className="pb-media-toolbar">
      <input className="pb-input" placeholder="Search media" value={search} onChange={(event) => setSearch(event.target.value)} onKeyDown={(event) => { if (event.key === 'Enter') void load(search); }} />
      <input hidden ref={file} type="file" accept="image/jpeg,image/png,image/webp,image/avif,image/gif" onChange={async (event) => {
        const selected = event.target.files?.[0];
        if (!selected) return;
        setBusy(true);
        setError(null);
        try {
          const asset = await api.uploadAsset(selected);
          setAssets((items) => [asset, ...items]);
        } catch (reason) {
          setError(reason instanceof Error ? reason.message : 'The image could not be uploaded.');
        } finally {
          setBusy(false);
          event.currentTarget.value = '';
        }
      }} />
      <button className="pb-button primary" disabled={busy} onClick={() => file.current?.click()}><ImagePlus size={14} />Upload</button>
    </div>
    {error && <p style={{ color: '#b42323', fontSize: 12 }}>{error}</p>}
    {busy && !assets.length ? <div className="pb-empty">Loading media…</div> : <div className="pb-media-grid">{assets.map((asset) => <button key={asset.id} className="pb-media-card" onClick={() => onSelect(asset)}><img src={asset.url} alt={asset.alt_text ?? ''} /><span>{asset.original_name}</span></button>)}</div>}
  </Modal>;
}

function HistoryDialog({ revisions, onClose, onRestore }: { revisions: Revision[]; onClose: () => void; onRestore: (revision: Revision) => void }): React.JSX.Element {
  return <Modal title="Revision history" onClose={onClose}>{revisions.length ? revisions.map((revision) => <div className="pb-history-row" key={revision.id}><div><strong>Revision {revision.revision_number} · {revision.status}</strong><small>{new Date(revision.created_at).toLocaleString()}</small></div>{revision.status === 'draft' ? <span style={{ color: '#8491a4', fontSize: 12 }}>Current draft</span> : <button className="pb-button" onClick={() => onRestore(revision)}><RotateCcw size={14} />Restore</button>}</div>) : <div className="pb-empty">No revisions yet.</div>}</Modal>;
}

function PagesDialog({ pages, current, onClose, onOpen, onArchive, onRestore }: { pages: PageMeta[]; current: string; onClose: () => void; onOpen: (slug: string) => void; onArchive: (page: PageMeta) => Promise<void>; onRestore: (page: PageMeta) => Promise<void> }): React.JSX.Element {
  const [newSlug, setNewSlug] = useState('');
  return <Modal title="Pages" onClose={onClose}>
    <div className="pb-media-toolbar"><input className="pb-input" value={newSlug} placeholder="New page slug" onChange={(event) => setNewSlug(event.target.value)} /><button className="pb-button primary" disabled={!newSlug.trim()} onClick={() => onOpen(newSlug.trim())}><CirclePlus size={14} />Create</button></div>
    {pages.map((page) => {
      const pageSlug = page.slug ?? '';
      const archived = page.is_active === false;
      return <div className="pb-history-row" key={pageSlug || page.title}><div><strong>{page.title}</strong><small>/{pageSlug === 'home' ? '' : pageSlug}</small></div><div className="pb-toolbar"><button className="pb-button" disabled={pageSlug === current || pageSlug === ''} onClick={() => onOpen(pageSlug || 'home')}>Open</button>{archived ? <button className="pb-icon" title="Restore page" onClick={() => void onRestore(page)}><RotateCcw size={14} /></button> : <button className="pb-icon" title="Archive page" onClick={() => void onArchive(page)}><Archive size={14} /></button>}</div></div>;
    })}
  </Modal>;
}

function Modal({ title, children, onClose }: { title: string; children: React.ReactNode; onClose: () => void }): React.JSX.Element {
  return <div className="pb-modal-backdrop" onMouseDown={onClose}><section className="pb-modal" role="dialog" aria-modal="true" onMouseDown={(event) => event.stopPropagation()}><header className="pb-modal-head"><h3>{title}</h3><button className="pb-icon" onClick={onClose}><X size={17} /></button></header><div className="pb-modal-body">{children}</div></section></div>;
}

createRoot(mount).render(<React.StrictMode><App /></React.StrictMode>);
