import './pagebuilder/editor.jsx';
import '../css/page-builder-react.css';

const builderRoot = document.getElementById('altura-page-builder-root');
if (builderRoot?.dataset.previewUrl) {
    const applyPublicPreview = () => {
        const frame = builderRoot.querySelector('iframe[title="Preview"]');
        if (frame && frame.getAttribute('src') !== builderRoot.dataset.previewUrl) {
            frame.setAttribute('src', builderRoot.dataset.previewUrl);
        }
    };
    new MutationObserver(applyPublicPreview).observe(builderRoot, { childList: true, subtree: true });
    applyPublicPreview();
}
