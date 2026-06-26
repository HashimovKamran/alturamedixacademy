export type FieldType =
  | 'text'
  | 'textarea'
  | 'rich_text'
  | 'inline_richtext'
  | 'html'
  | 'select'
  | 'radio'
  | 'checkbox'
  | 'number'
  | 'range'
  | 'color'
  | 'color_background'
  | 'url'
  | 'video_url'
  | 'asset'
  | 'image_picker'
  | 'icon'
  | 'font_awesome'
  | 'material_icon'
  | 'google_font'
  | 'text_alignment'
  | 'heading'
  | 'paragraph'
  | 'external';

export type FieldSchema = {
  type: FieldType;
  label: string;
  default?: unknown;
  required?: boolean;
  nullable?: boolean;
  options?: string[];
  placeholder?: string;
  help?: string;
  endpoint?: string;
  min?: number;
  max?: number;
  step?: number;
};

export type ComponentSchema = {
  label: string;
  fields: Record<string, FieldSchema>;
  blocks: string[];
  slots?: string[];
  zones?: Array<'main' | 'header' | 'footer'>;
  category?: string;
  system?: boolean;
};

export type BlockNode = {
  type: string;
  _name: string | null;
  disabled: boolean;
  slot_key?: string | null;
  settings: Record<string, unknown>;
  blocks: Record<string, BlockNode>;
  order: string[];
};

export type SectionNode = {
  type: string;
  _name: string | null;
  disabled: boolean;
  slot_key?: string | null;
  settings: Record<string, unknown>;
  blocks: Record<string, BlockNode>;
  order: string[];
};

export type SectionMap = {
  sections: Record<string, SectionNode>;
  order: string[];
};

export type DocumentModel = {
  schema_version: 1;
  layout: {
    type: string;
    header: SectionMap;
    footer: SectionMap;
  };
  sections: Record<string, SectionNode>;
  order: string[];
};

export type Revision = {
  id: string;
  revision_number: number;
  status: 'draft' | 'published' | 'archived';
  editor_revision: number;
  document: DocumentModel;
  theme_settings: Record<string, unknown> | null;
  created_at: string;
  published_at: string | null;
};

export type PageMeta = {
  id?: string;
  slug?: string;
  title: string;
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  template: string | null;
  is_active?: boolean;
  lock_version?: number;
};

export type Asset = {
  id: string;
  original_name: string;
  path: string;
  url: string;
  mime_type: string;
  width: number | null;
  height: number | null;
  alt_text: string | null;
};

export type Catalog = {
  sections: Record<string, ComponentSchema>;
  blocks: Record<string, ComponentSchema>;
  templates: Record<string, { label: string; view: string }>;
  theme_settings: Array<{
    name: string;
    settings: Array<{
      key: string;
      label: string;
      type: FieldType;
      default: unknown;
      css_var?: string;
    }>;
  }>;
};

export type BootstrapPayload = {
  page: PageMeta | null;
  document: DocumentModel;
  draft: Revision | null;
  catalog: Catalog;
  theme_settings: Record<string, unknown>;
  revisions: Revision[];
};

export type SelectedNode =
  | { kind: 'section'; zone: 'main' | 'header' | 'footer'; id: string }
  | { kind: 'block'; zone: 'main' | 'header' | 'footer'; sectionId: string; blockPath: string[] };

export type Snapshot = {
  document: DocumentModel;
  theme: Record<string, unknown>;
  meta: PageMeta;
};
