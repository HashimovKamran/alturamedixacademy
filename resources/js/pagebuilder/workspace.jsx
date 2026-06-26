import React, { useEffect, useRef, useState } from 'react';
import AssetPicker from './asset-picker.jsx';

const clone = (value) => JSON.parse(JSON.stringify(value));
const createId = (type) => `${type}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;

function mapFor(documentModel, zone) {
    return zone === 'main'
        ? { sections: documentModel.sections, order: documentModel.order }
        : documentModel.layout[zone];
}

function writeMap(documentModel, zone, map) {
    if (zone === 'main') {
        documentModel.sections = map.sections;
        documentModel.order = map.order;
    } else {
        documentModel.layout[zone] = map;
    }
}

function nodeAt(documentModel, zone, path) {
    if (!path?.length) return null;
    let node = mapFor(documentModel, zone).sections[path[0]];
    for (const id of path.slice(1)) node = node?.blocks?.[id];
    return node || null;
}

function containerFor(documentModel, zone, path) {
    const map = mapFor(documentModel, zone);
    if (path.length === 1) return map;
    const parent = nodeAt(documentModel, zone, path.slice(0, -1));
    return { sections: parent.blocks, order: parent.order };
}

function writeContainer(documentModel, zone, path, container) {
    if (path.length === 1) {
        writeMap(documentModel, zone, container);
        return;
    }
    const parent = nodeAt(documentModel, zone, path.slice(0, -1));
    parent.blocks = container.sections;
    parent.order = container.order;
}

export default function Workspace({ mount }) {
    const apiBase = mount.dataset.apiBase;
    const pageKey = mount.dataset.pageKey || 'index';
    const language = mount.dataset.language || 'az';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const canEdit = mount.dataset.canEdit === '1';
    const canPublish = mount.dataset.canPublish === '1';
    const previewUrl = mount.dataset.previewUrl || mount.dataset.canvasUrl;

    const [catalog, setCatalog] = useState(null);
    const [documentModel, setDocumentModel] = useState(null);
    const [page, setPage] = useState(null);
    const [draft, setDraft] = useState(null);
    const [pages, setPages] = useState([]);
    const [zone, setZone] = useState('main');
    const [selectedPath, setSelectedPath] = useState([]);
    const [past, setPast] = useState([]);
    const [future, setFuture] = useState([]);
    const [modal, setModal] = useState('');
    const [history, setHistory] = useState([]);
    const [notice, setNotice] = useState('');
    const [device, setDevice] = useState('desktop');
    const frame = useRef(null);

    const api = async (path, options = {}) => {
        const response = await fetch(apiBase + path, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, ...(options.headers || {}) },
            ...options,
        });
        const body = response.status === 204 ? null : await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body?.message || 'Əməliyyat uğursuz oldu.');
        return body?.data;
    };

    useEffect(() => {
        Promise.all([
            api(`/bootstrap?page_key=${encodeURIComponent(pageKey)}&lang_code=${encodeURIComponent(language)}`),
            api(`/pages?lang_code=${encodeURIComponent(language)}`),
        ]).then(([boot, list]) => {
            setCatalog(boot.catalog);
            setDocumentModel(boot.document);
            setPage(boot.page);
            setDraft(boot.draft);
            setPages(list || []);
            setHistory(boot.revisions || []);
        }).catch((error) => setNotice(error.message));
    }, []);

    if (!catalog || !documentModel || !page) {
        return <div className="apb-loading">Page Builder yüklənir…</div>;
    }

    const selectedNode = nodeAt(documentModel, zone, selectedPath);
    const selectedDefinition = selectedNode
        ? (selectedPath.length === 1 ? catalog.sections[selectedNode.type] : (catalog.blocks[selectedNode.type] || catalog.sections[selectedNode.type]))
        : null;
    const currentMap = mapFor(documentModel, zone);

    const mutate = (callback) => {
        if (!canEdit) return;
        const before = clone(documentModel);
        const next = clone(documentModel);
        callback(next);
        setPast((items) => [...items.slice(-79), before]);
        setFuture([]);
        setDocumentModel(next);
    };

    const undo = () => {
        if (!canEdit) return;
        const previous = past.at(-1);
        if (!previous) return;
        setPast((items) => items.slice(0, -1));
        setFuture((items) => [...items, documentModel]);
        setDocumentModel(previous);
    };

    const redo = () => {
        if (!canEdit) return;
        const next = future.at(-1);
        if (!next) return;
        setFuture((items) => items.slice(0, -1));
        setPast((items) => [...items, documentModel]);
        setDocumentModel(next);
    };

    const setField = (key, value) => mutate((next) => {
        const node = nodeAt(next, zone, selectedPath);
        if (node) node.settings[key] = value;
    });

    const makeNode = (type, definition, slotKey = 'default') => ({
        type,
        _name: null,
        disabled: false,
        slot_key: slotKey,
        settings: Object.fromEntries(Object.entries(definition.fields || {}).map(([key, field]) => [key, field.default ?? null])),
        blocks: {},
        order: [],
    });

    const addSection = (type) => {
        if (!canEdit) return;
        const definition = catalog.sections[type];
        const id = createId(type);
        mutate((next) => {
            const map = mapFor(next, zone);
            map.sections[id] = makeNode(type, definition);
            map.order.push(id);
            writeMap(next, zone, map);
        });
        setSelectedPath([id]);
        setModal('');
    };

    const addBlock = (type) => {
        if (!canEdit || !selectedNode || !selectedDefinition) return;
        const definition = catalog.blocks[type] || catalog.sections[type];
        const id = createId(type);
        const slotKey = (selectedDefinition.slots || ['default'])[0] || 'default';
        mutate((next) => {
            const parent = nodeAt(next, zone, selectedPath);
            parent.blocks[id] = makeNode(type, definition, slotKey);
            parent.order.push(id);
        });
        setSelectedPath((path) => [...path, id]);
        setModal('');
    };

    const remove = () => {
        if (!canEdit || !selectedPath.length || !window.confirm('Seçilən komponent silinsin?')) return;
        mutate((next) => {
            const container = containerFor(next, zone, selectedPath);
            const id = selectedPath.at(-1);
            delete container.sections[id];
            container.order = container.order.filter((item) => item !== id);
            writeContainer(next, zone, selectedPath, container);
        });
        setSelectedPath((path) => path.slice(0, -1));
    };

    const duplicate = () => {
        if (!canEdit || !selectedNode || !selectedPath.length) return;
        const id = createId(selectedNode.type);
        mutate((next) => {
            const container = containerFor(next, zone, selectedPath);
            const sourceId = selectedPath.at(-1);
            container.sections[id] = clone(container.sections[sourceId]);
            container.order.splice(container.order.indexOf(sourceId) + 1, 0, id);
            writeContainer(next, zone, selectedPath, container);
        });
        setSelectedPath((path) => [...path.slice(0, -1), id]);
    };

    const move = (direction) => {
        if (!canEdit || !selectedPath.length) return;
        mutate((next) => {
            const container = containerFor(next, zone, selectedPath);
            const id = selectedPath.at(-1);
            const from = container.order.indexOf(id);
            const to = direction === 'up' ? from - 1 : from + 1;
            if (to >= 0 && to < container.order.length) {
                [container.order[from], container.order[to]] = [container.order[to], container.order[from]];
            }
            writeContainer(next, zone, selectedPath, container);
        });
    };

    const saveDraft = async () => {
        if (!canEdit) {
            setNotice('Bu hesab draft redaktə edə bilməz.');
            return null;
        }
        try {
            const result = await api('/draft', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    page_key: page.page_key,
                    document: documentModel,
                    theme_settings: {},
                    meta: page,
                    expected_editor_revision: draft?.editor_revision || 0,
                }),
            });
            setPage(result.page);
            setDraft(result.draft);
            setHistory((items) => [result.draft, ...items.filter((item) => item.id !== result.draft.id)]);
            frame.current?.contentWindow?.location.reload();
            setNotice('Draft yadda saxlanıldı.');
            return result.draft;
        } catch (error) {
            setNotice(error.message);
            return null;
        }
    };

    const publish = async () => {
        if (!canPublish) {
            setNotice('Bu hesabın dərc icazəsi yoxdur.');
            return;
        }
        const revision = canEdit ? await saveDraft() : draft;
        if (!revision) {
            setNotice('Dərc ediləcək draft yoxdur.');
            return;
        }
        try {
            const result = await api('/publish', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ page_key: page.page_key, revision_id: revision.id }),
            });
            setDraft(null);
            setHistory((items) => [result, ...items.filter((item) => item.id !== result.id)]);
            frame.current?.contentWindow?.location.reload();
            setNotice(`Revision ${result.revision_number} dərc edildi.`);
        } catch (error) {
            setNotice(error.message);
        }
    };

    const openHistory = async () => {
        try {
            const result = await api(`/history?page_key=${encodeURIComponent(page.page_key)}`);
            setHistory(result.revisions || []);
            setModal('history');
        } catch (error) {
            setNotice(error.message);
        }
    };

    const rollback = async (revision) => {
        if (!canPublish || !window.confirm(`Revision ${revision.revision_number} bərpa edilsin?`)) return;
        try {
            const result = await api('/rollback', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ page_key: page.page_key, revision_id: revision.id }),
            });
            setDocumentModel(result.document);
            setDraft(null);
            setHistory((items) => [result, ...items.filter((item) => item.id !== result.id)]);
            setModal('');
            frame.current?.contentWindow?.location.reload();
            setNotice(`Revision ${result.revision_number} bərpa edildi.`);
        } catch (error) {
            setNotice(error.message);
        }
    };

    const availableSections = Object.entries(catalog.sections)
        .filter(([, item]) => !item.system && (!item.zones?.length || item.zones.includes(zone)));
    const availableBlocks = (selectedDefinition?.blocks || [])
        .map((type) => [type, catalog.blocks[type] || catalog.sections[type]])
        .filter(([, item]) => item);

    return <div className="apb-shell">
        <header className="apb-topbar">
            <b className="apb-brand">AA Visual Builder</b>
            <select value={page.page_key} onChange={(event) => location.assign(`${location.pathname}?page=${encodeURIComponent(event.target.value)}`)}>
                {pages.map((item) => <option key={item.page_key} value={item.page_key}>{item.title}</option>)}
            </select>
            <span>{notice}</span>
            <div className="apb-actions">
                <button onClick={undo} disabled={!canEdit || !past.length}>↶</button>
                <button onClick={redo} disabled={!canEdit || !future.length}>↷</button>
                <button onClick={openHistory}>Tarixçə</button>
                {canEdit && <button onClick={() => setModal('sections')}>+ Section</button>}
                {canEdit && <button onClick={saveDraft}>Draft</button>}
                {canPublish && <button className="publish" onClick={publish}>Dərc et</button>}
            </div>
        </header>
        <main className="apb-workspace">
            <aside className="apb-panel">
                <div className="apb-zones">
                    {['main', 'header', 'footer'].map((item) => <button key={item} className={item === zone ? 'active' : ''} onClick={() => { setZone(item); setSelectedPath([]); }}>{item}</button>)}
                </div>
                {currentMap.order.map((id) => <TreeNode key={id} node={currentMap.sections[id]} path={[id]} selectedPath={selectedPath} choose={setSelectedPath} catalog={catalog} />)}
            </aside>
            <section className="apb-stage">
                <div>
                    <button onClick={() => setDevice('desktop')}>Desktop</button>
                    <button onClick={() => setDevice('tablet')}>Tablet</button>
                    <button onClick={() => setDevice('mobile')}>Mobile</button>
                    <button onClick={() => frame.current?.contentWindow?.location.reload()}>↻</button>
                </div>
                <iframe ref={frame} className={device} title="Preview" src={previewUrl} />
            </section>
            <aside className="apb-panel apb-inspector">
                {!selectedNode ? <p>Section və ya block seçin.</p> : <>
                    <h3>{selectedDefinition?.label || selectedNode.type}</h3>
                    {canEdit && <div><button onClick={() => move('up')}>↑</button><button onClick={() => move('down')}>↓</button><button onClick={duplicate}>Kopyala</button><button onClick={remove}>Sil</button></div>}
                    <label>Gizlət<input type="checkbox" disabled={!canEdit} checked={selectedNode.disabled} onChange={(event) => mutate((next) => { const node = nodeAt(next, zone, selectedPath); node.disabled = event.target.checked; })} /></label>
                    {Object.entries(selectedDefinition?.fields || {}).map(([field, item]) => <Field key={field} field={item} value={selectedNode.settings[field]} disabled={!canEdit} change={(value) => setField(field, value)} />)}
                    {canEdit && availableBlocks.length > 0 && <button className="primary" onClick={() => setModal('blocks')}>+ İç blok</button>}
                </>}
            </aside>
        </main>
        {modal === 'sections' && <Catalog title="Section əlavə et" rows={availableSections} choose={addSection} close={() => setModal('')} />}
        {modal === 'blocks' && <Catalog title="İç blok əlavə et" rows={availableBlocks} choose={addBlock} close={() => setModal('')} />}
        {modal === 'history' && <History rows={history} canPublish={canPublish} rollback={rollback} close={() => setModal('')} />}
    </div>;
}

function TreeNode({ node, path, selectedPath, choose, catalog }) {
    if (!node) return null;
    const active = path.length === selectedPath.length && path.every((id, index) => id === selectedPath[index]);
    const definition = path.length === 1 ? catalog.sections[node.type] : (catalog.blocks[node.type] || catalog.sections[node.type]);
    return <div className="apb-tree-wrap">
        <button className={'apb-tree ' + (active ? 'selected' : '')} onClick={() => choose(path)}>{node.disabled ? '○' : '●'} {definition?.label || node.type}</button>
        {(node.order || []).map((id) => <div className="apb-indent" key={id}><TreeNode node={node.blocks[id]} path={[...path, id]} selectedPath={selectedPath} choose={choose} catalog={catalog} /></div>)}
    </div>;
}

function Field({ field, value, change, disabled }) {
    if (field.type === 'checkbox') return <label>{field.label}<input type="checkbox" disabled={disabled} checked={!!value} onChange={(event) => change(event.target.checked)} /></label>;
    if (field.type === 'asset') return <label>{field.label}<AssetPicker value={value} onChange={change} disabled={disabled} /></label>;
    if (field.type === 'select') return <label>{field.label}<select disabled={disabled} value={value ?? ''} onChange={(event) => change(event.target.value)}>{(field.options || []).map((option) => <option key={option} value={option}>{option}</option>)}</select></label>;
    if (field.key === 'image_path') return <label>{field.label}<AssetPicker value={value} onChange={change} disabled={disabled} returnUrl /></label>;
    return <label>{field.label}{['textarea', 'rich_text'].includes(field.type)
        ? <textarea disabled={disabled} value={value ?? ''} onChange={(event) => change(event.target.value)} />
        : <input disabled={disabled} type={field.type === 'number' ? 'number' : field.type === 'color' ? 'color' : 'text'} min={field.min} max={field.max} value={value ?? ''} onChange={(event) => change(field.type === 'number' ? Number(event.target.value) : event.target.value)} />}</label>;
}

function Catalog({ title, rows, choose, close }) {
    return <div className="apb-modal-backdrop"><div className="apb-modal"><button type="button" onClick={close}>×</button><h3>{title}</h3>{rows.map(([id, item]) => <button className="apb-catalog-item" type="button" key={id} onClick={() => choose(id)}>{item.label}<small>{item.category}</small></button>)}</div></div>;
}

function History({ rows, canPublish, rollback, close }) {
    return <div className="apb-modal-backdrop"><div className="apb-modal"><header><b>Revision tarixçəsi</b><button type="button" onClick={close}>×</button></header>{rows.length === 0 ? <p>Hələ revision yoxdur.</p> : rows.map((revision) => <div className="apb-history" key={revision.id}><span>v{revision.revision_number} · {revision.status}</span><small>{revision.published_at || revision.created_at}</small>{canPublish && revision.status !== 'draft' && <button type="button" onClick={() => rollback(revision)}>Bərpa et</button>}</div>)}</div></div>;
}
