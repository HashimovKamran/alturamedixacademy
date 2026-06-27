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
        if (!art || art.dataset.bioneRipplesReady === '1') return Boolean(art?.dataset.bioneRipplesReady);

        const imageUrl = imageUrlFrom(art);
        if (!imageUrl) return false;

        try {
            art.classList.add('aa-bione-ripples');
            $(art).ripples({
                imageUrl,
                resolution: 512,
                dropRadius: 34,
                perturbance: 0.04,
                // The text/CTA layer sits above the background. We manually forward coordinates from the slider below.
                interactive: false,
                crossOrigin: 'anonymous',
            });
            art.dataset.bioneRipplesReady = '1';
            return true;
        } catch (error) {
            // WebGL can be disabled by the browser. Leave the normal CSS background untouched in that case.
            art.classList.remove('aa-bione-ripples');
            console.warn('Hero water effect is unavailable in this browser.', error);
            return false;
        }
    };

    const activeArt = () => slider.querySelector('.aa-hero-slide.active .aa-hero-art:not(.is-empty)');
    const forwardDrop = (event, radius, strength, force = false) => {
        const art = activeArt();
        if (!art || !initialize(art)) return;

        const rect = art.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        if (x < 0 || y < 0 || x > rect.width || y > rect.height) return;

        const now = performance.now();
        const previous = forwardDrop.previous;
        const moved = previous ? Math.hypot(x - previous.x, y - previous.y) : Number.POSITIVE_INFINITY;
        if (!force && moved < 18 && now - (previous?.time || 0) < 54) return;

        try {
            $(art).ripples('drop', x, y, radius, strength);
            forwardDrop.previous = { x, y, time: now };
        } catch (error) {
            console.warn('Hero water drop could not be rendered.', error);
        }
    };

    // Each slide remains sized in the DOM, so initializing once prevents flicker on slider changes.
    slider.querySelectorAll('.aa-hero-art:not(.is-empty)').forEach(initialize);

    // The foreground content covers the background element, therefore it receives the pointer events.
    // Forward them explicitly to the currently visible jquery-ripples canvas.
    slider.addEventListener('pointermove', (event) => {
        if (event.pointerType && event.pointerType !== 'mouse') return;
        forwardDrop(event, 34, 0.055);
    }, { passive: true });

    slider.addEventListener('pointerdown', (event) => {
        if (event.pointerType && event.pointerType !== 'mouse') return;
        forwardDrop(event, 52, 0.18, true);
    }, { passive: true });

    slider.addEventListener('mouseleave', () => {
        forwardDrop.previous = null;
    }, { passive: true });
})();
