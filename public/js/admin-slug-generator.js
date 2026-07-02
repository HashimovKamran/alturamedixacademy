(() => {
    'use strict';

    const slugify = (value) => String(value || '')
        .normalize('NFKD')
        .toLocaleLowerCase('az')
        .replace(/ə/g, 'e')
        .replace(/ö/g, 'o')
        .replace(/ü/g, 'u')
        .replace(/ı/g, 'i')
        .replace(/ğ/g, 'g')
        .replace(/ş/g, 's')
        .replace(/ç/g, 'c')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .replace(/-{2,}/g, '-');

    const bindSlugPair = (source, slugInput) => {
        if (!source || !slugInput || slugInput.dataset.aaSlugBound === '1') return;

        const generatedFromCurrentTitle = slugify(source.value);
        const existingSlug = String(slugInput.value || '').trim();
        const wasExplicitlyCustom = existingSlug !== '' && existingSlug !== generatedFromCurrentTitle;

        // An existing record is not automatically "manual". It is manual only when it differs from its title-derived slug.
        slugInput.dataset.slugDirty = wasExplicitlyCustom ? '1' : '0';
        slugInput.dataset.aaSlugBound = '1';

        slugInput.addEventListener('input', () => {
            slugInput.dataset.slugDirty = '1';
        });

        const updateSlug = () => {
            if (slugInput.dataset.slugDirty === '1') return;
            slugInput.value = slugify(source.value);
        };

        source.addEventListener('input', updateSlug);
        source.addEventListener('change', updateSlug);
    };

    const bindAll = () => {
        document.querySelectorAll('form').forEach((form) => {
            bindSlugPair(
                form.querySelector('[data-slug-source]'),
                form.querySelector('[data-slug-input]'),
            );
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindAll, { once: true });
    } else {
        bindAll();
    }
})();
