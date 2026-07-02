import type { BlockNode, Catalog, ComponentSchema, DocumentModel, SectionMap, SectionNode, SelectedNode } from './types';

export type Zone = 'main' | 'header' | 'footer';

export function clone<T>(value: T): T {
  return structuredClone(value);
}

export function createId(prefix: string): string {
  const suffix = crypto.randomUUID ? crypto.randomUUID().replaceAll('-', '').slice(0, 14) : Math.random().toString(36).slice(2, 16);
  return `${prefix}_${suffix}`;
}

export function mapFor(document: DocumentModel, zone: Zone): SectionMap {
  return zone === 'main' ? { sections: document.sections, order: document.order } : document.layout[zone];
}

export function setMap(document: DocumentModel, zone: Zone, value: SectionMap): void {
  if (zone === 'main') {
    document.sections = value.sections;
    document.order = value.order;
  } else {
    document.layout[zone] = value;
  }
}

export function componentDefaults(schema: ComponentSchema): Record<string, unknown> {
  return Object.fromEntries(Object.entries(schema.fields).map(([key, field]) => [key, clone(field.default ?? null)]));
}

export function sectionAt(document: DocumentModel, zone: Zone, sectionId: string): SectionNode | null {
  return mapFor(document, zone).sections[sectionId] ?? null;
}

export function blockAt(section: SectionNode, path: string[]): BlockNode | null {
  let collection = section.blocks;
  let block: BlockNode | null = null;
  for (const id of path) {
    block = collection[id] ?? null;
    if (!block) return null;
    collection = block.blocks;
  }
  return block;
}

export function parentCollection(section: SectionNode, path: string[]): Record<string, BlockNode> | null {
  return path.length < 2 ? section.blocks : blockAt(section, path.slice(0, -1))?.blocks ?? null;
}

export function parentOrder(section: SectionNode, path: string[]): string[] | null {
  return path.length < 2 ? section.order : blockAt(section, path.slice(0, -1))?.order ?? null;
}

export function nodeAt(document: DocumentModel, selected: SelectedNode | null): SectionNode | BlockNode | null {
  if (!selected) return null;
  const section = sectionAt(document, selected.zone, selected.kind === 'section' ? selected.id : selected.sectionId);
  if (!section) return null;
  return selected.kind === 'section' ? section : blockAt(section, selected.blockPath);
}

export function schemaAt(document: DocumentModel, catalog: Catalog, selected: SelectedNode | null): ComponentSchema | null {
  const node = nodeAt(document, selected);
  if (!node || !selected) return null;
  return selected.kind === 'section' ? catalog.sections[node.type] ?? null : (catalog.blocks[node.type] ?? catalog.sections[node.type] ?? null);
}

export function selectionKey(selected: SelectedNode | null): string | null {
  if (!selected) return null;
  return selected.kind === 'section'
    ? `${selected.zone}|section|${selected.id}`
    : `${selected.zone}|block|${selected.sectionId}|${selected.blockPath.join('.')}`;
}

export function fromSelectionKey(value: string): SelectedNode | null {
  const [zone, kind, sectionOrId, path] = value.split('|');
  if (zone !== 'main' && zone !== 'header' && zone !== 'footer') return null;
  if (kind === 'section' && sectionOrId) return { kind: 'section', zone, id: sectionOrId };
  if (kind === 'block' && sectionOrId && path) return { kind: 'block', zone, sectionId: sectionOrId, blockPath: path.split('.') };
  return null;
}

export function reorder(order: string[], source: string, destination: string): void {
  const from = order.indexOf(source);
  const to = order.indexOf(destination);
  if (from < 0 || to < 0 || from === to) return;
  order.splice(from, 1);
  order.splice(to, 0, source);
}

export function sameParent(first: string[], second: string[]): boolean {
  return first.slice(0, -1).join('.') === second.slice(0, -1).join('.');
}
