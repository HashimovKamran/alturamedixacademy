(() => {
    'use strict';

    const supportsFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!supportsFinePointer || reducedMotion || !window.jQuery || !window.jQuery.fn.ripples) return;

    const $ = window.jQuery;
    const slider = document.querySelector('body.aa-home-page .aa-home-hero .hero-slider');
    if (!slider) return;

    const imageUrlFrom = (element) => {
        const background = element.style.backgroundImage || window.getComputedStyle(element).backgroundImage || '';
        const urls = [...background.matchAll(/url\(\s*(['"]?)(.*?)\1\s*\)/gi)]
            .map((match) => String(match[2] || '').trim())
            .filter(Boolean);

        // Hero backgrounds contain a gradient followed by the actual uploaded slider image.
        return urls.at(-1) || '';
    };

    const initialize = (art) => {
        if (!art || art.dataset.bioneRipplesReady === '1') return;

        const imageUrl = imageUrlFrom(art);
        if (!imageUrl) return;

        try {
            art.classList.add('aa-bione-ripples');
            $(art).ripples({
                imageUrl,
                resolution: 512,
                dropRadius: 20,
                perturbance: 0.018,
                interactive: true,
                crossOrigin: 'anonymous',
            });
            art.dataset.bioneRipplesReady = '1';
        } catch (error) {
            // WebGL can be disabled by the browser. Leave the normal CSS background untouched in that case.
            art.classList.remove('aa-bione-ripples');
            console.warn('Hero water effect is unavailable in this browser.', error);
        }
    };

    // Each slide remains sized in the DOM, so initializing once prevents flicker on slider changes.
    slider.querySelectorAll('.aa-hero-art:not(.is-empty)').forEach(initialize);
})();
