(() => {
    'use strict';

    const supportsFineHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!supportsFineHover || prefersReducedMotion) return;

    const hero = document.querySelector('body.aa-home-page .aa-home-hero');
    const slider = hero?.querySelector('.hero-slider');
    if (!hero || !slider) return;

    const deviceScale = Math.min(window.devicePixelRatio || 1, 2);
    const canvases = new Map();
    let lastPoint = null;
    let lastRippleAt = 0;
    let frameId = 0;

    const getActiveArt = () => slider.querySelector('.aa-hero-slide.active .aa-hero-art');

    const createSurface = (art) => {
        if (canvases.has(art)) return canvases.get(art);

        const canvas = document.createElement('canvas');
        canvas.className = 'aa-water-ripple-canvas';
        canvas.setAttribute('aria-hidden', 'true');
        art.appendChild(canvas);

        const surface = { art, canvas, context: canvas.getContext('2d'), ripples: [], pointer: null };
        canvases.set(art, surface);
        resizeSurface(surface);
        return surface;
    };

    const resizeSurface = (surface) => {
        const rect = surface.art.getBoundingClientRect();
        const width = Math.max(1, Math.round(rect.width * deviceScale));
        const height = Math.max(1, Math.round(rect.height * deviceScale));
        if (surface.canvas.width === width && surface.canvas.height === height) return;

        surface.canvas.width = width;
        surface.canvas.height = height;
        surface.canvas.style.width = `${rect.width}px`;
        surface.canvas.style.height = `${rect.height}px`;
        surface.context.setTransform(deviceScale, 0, 0, deviceScale, 0, 0);
    };

    const addRipple = (surface, x, y, strength = 1) => {
        const now = performance.now();
        surface.ripples.push({ x, y, startedAt: now, strength });
        if (surface.ripples.length > 16) surface.ripples.splice(0, surface.ripples.length - 16);
    };

    const paintSurface = (surface, now) => {
        resizeSurface(surface);

        const rect = surface.art.getBoundingClientRect();
        const ctx = surface.context;
        ctx.clearRect(0, 0, rect.width, rect.height);

        const alive = [];
        for (const ripple of surface.ripples) {
            const elapsed = now - ripple.startedAt;
            const duration = 920;
            if (elapsed >= duration) continue;

            const progress = elapsed / duration;
            const fade = Math.pow(1 - progress, 1.65) * ripple.strength;
            const radius = 10 + progress * 114;
            const yRadius = Math.max(4, radius * .43);

            ctx.save();
            ctx.translate(ripple.x, ripple.y);
            ctx.rotate(-.08);

            const outer = ctx.createLinearGradient(-radius, 0, radius, 0);
            outer.addColorStop(0, 'rgba(72, 201, 255, 0)');
            outer.addColorStop(.42, `rgba(126, 230, 255, ${.17 * fade})`);
            outer.addColorStop(.56, `rgba(255, 255, 255, ${.34 * fade})`);
            outer.addColorStop(1, 'rgba(72, 201, 255, 0)');
            ctx.strokeStyle = outer;
            ctx.lineWidth = Math.max(.55, 1.5 - progress);
            ctx.beginPath();
            ctx.ellipse(0, 0, radius, yRadius, 0, 0, Math.PI * 2);
            ctx.stroke();

            if (progress > .12) {
                ctx.strokeStyle = `rgba(79, 193, 255, ${.12 * fade})`;
                ctx.lineWidth = .75;
                ctx.beginPath();
                ctx.ellipse(0, 0, radius * .72, Math.max(3, yRadius * .72), 0, 0, Math.PI * 2);
                ctx.stroke();
            }

            ctx.restore();
            alive.push(ripple);
        }
        surface.ripples = alive;

        if (surface.pointer) {
            const glow = ctx.createRadialGradient(surface.pointer.x, surface.pointer.y, 0, surface.pointer.x, surface.pointer.y, 115);
            glow.addColorStop(0, 'rgba(182, 247, 255, .16)');
            glow.addColorStop(.22, 'rgba(70, 202, 255, .07)');
            glow.addColorStop(1, 'rgba(35, 157, 221, 0)');
            ctx.fillStyle = glow;
            ctx.beginPath();
            ctx.arc(surface.pointer.x, surface.pointer.y, 115, 0, Math.PI * 2);
            ctx.fill();
        }

        return surface.ripples.length > 0 || surface.pointer;
    };

    const render = (now) => {
        let needsNextFrame = false;
        canvases.forEach((surface) => {
            if (paintSurface(surface, now)) needsNextFrame = true;
        });
        frameId = needsNextFrame ? window.requestAnimationFrame(render) : 0;
    };

    const requestRender = () => {
        if (!frameId) frameId = window.requestAnimationFrame(render);
    };

    const updateHeroGlow = (x, y, rect) => {
        hero.style.setProperty('--aa-water-x', `${(x / rect.width) * 100}%`);
        hero.style.setProperty('--aa-water-y', `${(y / rect.height) * 100}%`);
    };

    slider.addEventListener('pointermove', (event) => {
        if (event.pointerType && event.pointerType !== 'mouse') return;

        const art = getActiveArt();
        if (!art) return;

        const rect = art.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        if (x < 0 || y < 0 || x > rect.width || y > rect.height) return;

        const surface = createSurface(art);
        surface.pointer = { x, y };
        hero.classList.add('is-water-active');
        updateHeroGlow(x, y, rect);

        const now = performance.now();
        const distance = lastPoint ? Math.hypot(x - lastPoint.x, y - lastPoint.y) : 100;
        if (distance > 38 || now - lastRippleAt > 150) {
            addRipple(surface, x, y, Math.min(1.25, .62 + distance / 110));
            lastPoint = { x, y };
            lastRippleAt = now;
        }

        requestRender();
    }, { passive: true });

    slider.addEventListener('pointerleave', () => {
        hero.classList.remove('is-water-active');
        canvases.forEach((surface) => { surface.pointer = null; });
        lastPoint = null;
        requestRender();
    }, { passive: true });

    window.addEventListener('resize', () => {
        canvases.forEach(resizeSurface);
        requestRender();
    }, { passive: true });
})();
