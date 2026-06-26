import React from 'react';
import { createRoot } from 'react-dom/client';
import Workspace from './workspace.jsx';

const mount = document.getElementById('altura-page-builder-root');
if (!mount) throw new Error('Page Builder mount point is missing.');
createRoot(mount).render(<Workspace mount={mount} />);
