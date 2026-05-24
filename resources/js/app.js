import './bootstrap';

const themeKey = 'storefront-theme';

const getPreferredTheme = () => {
    try {
        const storedTheme = window.localStorage.getItem(themeKey);

        if (storedTheme === 'dark' || storedTheme === 'light') {
            return storedTheme;
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    } catch (error) {
        return 'light';
    }
};

const applyTheme = (theme, persist = true) => {
    const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
    const root = document.documentElement;

    root.dataset.theme = normalizedTheme;
    root.classList.toggle('dark', normalizedTheme === 'dark');

    try {
        if (persist) {
            window.localStorage.setItem(themeKey, normalizedTheme);
        }
    } catch (error) {
        // Storage can be unavailable in private or restricted browsing modes.
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const isDark = normalizedTheme === 'dark';
        const lightIcon = button.querySelector('[data-theme-icon-light]');
        const darkIcon = button.querySelector('[data-theme-icon-dark]');
        const nextLabel = isDark ? button.dataset.lightLabel : button.dataset.darkLabel;

        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('aria-label', nextLabel || '');

        if (lightIcon) {
            lightIcon.hidden = isDark;
        }

        if (darkIcon) {
            darkIcon.hidden = !isDark;
        }
    });
};

const initializeStorefrontMotion = () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        return;
    }

    const revealSelectors = [
        '.store-section > *',
        '.store-page-hero',
        '.store-panel',
        '.store-product-toolbar',
        '.store-filter-panel',
        '.store-product-grid > *',
        '.store-scroll-row > *',
        '.store-trust-strip > *',
        '.store-footer-newsletter > div',
        '.store-footer-main > *',
        '.store-footer-trust-grid > *',
        '.store-footer-bottom',
        'main [data-product-view] > *',
    ];

    const elements = Array.from(document.querySelectorAll(revealSelectors.join(',')))
        .filter((element, index, collection) => collection.indexOf(element) === index)
        .filter((element) => !element.closest('[data-no-motion]'));

    if (elements.length === 0) {
        return;
    }

    document.documentElement.classList.add('store-motion-ready');

    elements.forEach((element, index) => {
        element.classList.add('store-reveal');
        element.style.setProperty('--store-reveal-delay', `${Math.min(index % 10, 8) * 55}ms`);
    });

    if (!('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.12,
    });

    elements.forEach((element) => observer.observe(element));
};

const initializeStorefrontEffects = () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const header = document.querySelector('[data-store-header]');
    const progress = document.querySelector('[data-scroll-progress]');
    let ticking = false;

    const updateScrollState = () => {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollHeight = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
        const progressValue = Math.min(Math.max(scrollTop / scrollHeight, 0), 1);

        if (header) {
            header.classList.toggle('is-scrolled', scrollTop > 10);
        }

        if (progress) {
            progress.style.transform = `scaleX(${progressValue})`;
        }

        ticking = false;
    };

    const requestScrollUpdate = () => {
        if (ticking) {
            return;
        }

        window.requestAnimationFrame(updateScrollState);
        ticking = true;
    };

    updateScrollState();
    window.addEventListener('scroll', requestScrollUpdate, { passive: true });

    if (prefersReducedMotion) {
        return;
    }

    const interactiveSelectors = [
        '.store-panel',
        '.store-page-hero',
        '.store-filter-panel',
        '.store-product-toolbar',
        '.store-trust-strip',
        '.store-footer-newsletter-form',
        '.store-icon-button',
        '.store-button-primary',
        '.store-button-secondary',
        'article.group',
    ];

    document.querySelectorAll(interactiveSelectors.join(',')).forEach((element) => {
        element.classList.add('store-spotlight');

        element.addEventListener('pointermove', (event) => {
            const rect = element.getBoundingClientRect();

            element.style.setProperty('--spotlight-x', `${event.clientX - rect.left}px`);
            element.style.setProperty('--spotlight-y', `${event.clientY - rect.top}px`);
            element.classList.add('is-pointer-active');
        });

        element.addEventListener('pointerleave', () => {
            element.classList.remove('is-pointer-active');
        });
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            form.classList.add('is-submitting');
        });
    });
};

const initializeHeroSliders = () => {
    document.querySelectorAll('[data-hero-slider]').forEach((slider) => {
        const slides = Array.from(slider.querySelectorAll('[data-hero-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-hero-dot]'));

        if (slides.length <= 1) {
            return;
        }

        let activeIndex = 0;
        let timer = null;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const showSlide = (nextIndex) => {
            activeIndex = (nextIndex + slides.length) % slides.length;

            slides.forEach((slide, index) => {
                const isActive = index === activeIndex;

                slide.classList.toggle('relative', isActive);
                slide.classList.toggle('absolute', !isActive);
                slide.classList.toggle('opacity-100', isActive);
                slide.classList.toggle('opacity-0', !isActive);
                slide.setAttribute('aria-hidden', String(!isActive));
            });

            dots.forEach((dot, index) => {
                dot.dataset.active = String(index === activeIndex);
            });
        };

        const start = () => {
            if (prefersReducedMotion) {
                return;
            }

            window.clearInterval(timer);
            timer = window.setInterval(() => showSlide(activeIndex + 1), 6500);
        };

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                showSlide(Number(dot.dataset.index || 0));
                start();
            });
        });

        slider.addEventListener('mouseenter', () => window.clearInterval(timer));
        slider.addEventListener('mouseleave', start);
        start();
    });
};

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getPreferredTheme(), false);
    initializeStorefrontMotion();
    initializeStorefrontEffects();
    initializeHeroSliders();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

            applyTheme(nextTheme);
        });
    });
});
