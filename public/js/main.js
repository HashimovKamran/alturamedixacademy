document.addEventListener('DOMContentLoaded', function () {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');

    const setMobileMenu = function (open) {
        if (!mobileBtn || !mainNav) return;
        document.body.classList.toggle('mobile-menu-open', open);
        mainNav.classList.toggle('open', open);
        mobileBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        const icon = mobileBtn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-bars', !open);
            icon.classList.toggle('fa-xmark', open);
        }
    };

    const closeMobileMenu = function () {
        setMobileMenu(false);
        const openAboutMenu = document.querySelector('[data-about-menu]');
        const openAboutToggle = document.querySelector('[data-about-submenu-toggle]');
        const headerNav = openAboutMenu ? openAboutMenu.closest('.header-nav') : null;
        if (openAboutMenu) openAboutMenu.classList.remove('is-open');
        if (headerNav) headerNav.classList.remove('about-submenu-open');
        if (openAboutToggle) openAboutToggle.setAttribute('aria-expanded', 'false');
    };

    if (mobileBtn && mainNav) {
        mobileBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            setMobileMenu(!mainNav.classList.contains('open'));
        });

        mainNav.querySelectorAll('a').forEach(function (item) {
            item.addEventListener('click', closeMobileMenu);
        });

        document.addEventListener('click', function (event) {
            const clickedInsidePanel = mainNav.contains(event.target);
            const clickedButton = mobileBtn.contains(event.target);
            if (!clickedInsidePanel && !clickedButton) closeMobileMenu();
        });

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeMobileMenu();
        });
    }

    const aboutMenu = document.querySelector('[data-about-menu]');
    const aboutToggle = document.querySelector('[data-about-submenu-toggle]');
    const isDesktopNav = function () {
        return window.matchMedia('(min-width: 992px)').matches;
    };

    const setAboutMenu = function (open) {
        if (!aboutMenu || !aboutToggle) return;
        const headerNav = aboutMenu.closest('.header-nav');
        if (headerNav && open) {
            const navRect = headerNav.getBoundingClientRect();
            const menuRect = aboutMenu.getBoundingClientRect();
            headerNav.style.setProperty('--about-submenu-center', (menuRect.left + (menuRect.width / 2) - navRect.left) + 'px');
        }
        aboutMenu.classList.toggle('is-open', open);
        if (headerNav) headerNav.classList.toggle('about-submenu-open', open);
        aboutToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    if (aboutMenu && aboutToggle) {
        aboutToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setAboutMenu(!aboutMenu.classList.contains('is-open'));
        });

        aboutMenu.addEventListener('mouseenter', function () {
            if (isDesktopNav()) setAboutMenu(true);
        });

        aboutMenu.addEventListener('mouseleave', function () {
            if (isDesktopNav()) setAboutMenu(false);
        });

        document.addEventListener('click', function (event) {
            if (!aboutMenu.contains(event.target)) setAboutMenu(false);
        });

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') setAboutMenu(false);
        });

        window.addEventListener('resize', function () {
            setAboutMenu(false);
            if (isDesktopNav()) setMobileMenu(false);
        });
    }

    const slider = document.querySelector('[data-slider]');
    const slides = slider ? slider.querySelectorAll('.hero-slide') : [];
    const dots = slider ? slider.querySelectorAll('[data-slider-dot]') : [];
    const prevBtn = slider ? slider.querySelector('[data-slider-prev]') : null;
    const nextBtn = slider ? slider.querySelector('[data-slider-next]') : null;

    let currentSlide = 0;
    let timer = null;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const autoplayMs = slider ? Math.max(2500, parseInt(slider.dataset.autoplayMs || '6200', 10)) : 6200;

    function showSlide(index) {
        if (!slides.length) return;

        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;

        slides.forEach(function (slide, slideIndex) {
            slide.classList.toggle('active', slideIndex === index);
            slide.setAttribute('aria-hidden', slideIndex === index ? 'false' : 'true');
        });

        dots.forEach(function (dot, dotIndex) {
            dot.classList.toggle('active', dotIndex === index);
            dot.setAttribute('aria-selected', dotIndex === index ? 'true' : 'false');
        });

        currentSlide = index;
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function stopSlider() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    function startSlider() {
        stopSlider();

        if (slides.length > 1 && !reducedMotion) {
            timer = setInterval(nextSlide, autoplayMs);
        }
    }

    if (slider && slides.length > 1) {
        slider.addEventListener('mouseenter', stopSlider);
        slider.addEventListener('mouseleave', startSlider);
        slider.addEventListener('focusin', stopSlider);
        slider.addEventListener('focusout', startSlider);
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            showSlide(currentSlide - 1);
            startSlider();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            nextSlide();
            startSlider();
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            const index = parseInt(dot.getAttribute('data-slider-dot'), 10);

            if (!Number.isNaN(index)) {
                showSlide(index);
                startSlider();
            }
        });
    });

    showSlide(0);
    startSlider();

    const partnersTrack = document.getElementById('partnersTrack');
    const partnerPrev = document.querySelector('[data-partner-prev]');
    const partnerNext = document.querySelector('[data-partner-next]');

    if (partnersTrack && partnerPrev && partnerNext) {
        partnerPrev.addEventListener('click', function () {
            partnersTrack.scrollBy({
                left: -240,
                behavior: 'smooth'
            });
        });

        partnerNext.addEventListener('click', function () {
            partnersTrack.scrollBy({
                left: 240,
                behavior: 'smooth'
            });
        });
    }
});
