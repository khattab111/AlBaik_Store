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

const initializeHeaderNavigation = () => {
    const menu = document.querySelector('[data-mobile-menu]');
    const languageMenu = document.querySelector('.store-language-menu');
    const languageToggle = document.querySelector('[data-language-toggle]');
    const currencyToggle = document.querySelector('[data-currency-toggle]');
    const currencyMenu = currencyToggle?.closest('.store-language-menu');

    document.querySelectorAll('[data-mobile-menu-open]').forEach((button) => {
        button.addEventListener('click', () => {
            menu?.classList.add('is-open');
            menu?.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        });
    });

    document.querySelectorAll('[data-mobile-menu-close]').forEach((button) => {
        button.addEventListener('click', () => {
            menu?.classList.remove('is-open');
            menu?.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        });
    });

    languageToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = languageMenu?.classList.toggle('is-open') || false;
        languageToggle.setAttribute('aria-expanded', String(isOpen));
        currencyMenu?.classList.remove('is-open');
        currencyToggle?.setAttribute('aria-expanded', 'false');
    });

    currencyToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = currencyMenu?.classList.toggle('is-open') || false;
        currencyToggle.setAttribute('aria-expanded', String(isOpen));
        languageMenu?.classList.remove('is-open');
        languageToggle?.setAttribute('aria-expanded', 'false');
    });

    document.addEventListener('click', (event) => {
        if (languageMenu?.contains(event.target) || currencyMenu?.contains(event.target)) {
            return;
        }

        languageMenu.classList.remove('is-open');
        languageToggle?.setAttribute('aria-expanded', 'false');
        currencyMenu?.classList.remove('is-open');
        currencyToggle?.setAttribute('aria-expanded', 'false');
    });
};

const updateCount = (selector, value) => {
    if (value === undefined || value === null) {
        return;
    }

    document.querySelectorAll(selector).forEach((element) => {
        element.textContent = String(value);
        element.classList.remove('store-count-pop');
        window.requestAnimationFrame(() => element.classList.add('store-count-pop'));
    });
};

const initializeAsyncStoreActions = (root = document) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    root.querySelectorAll('[data-ajax-store-action]').forEach((form) => {
        if (form.dataset.ajaxStoreBound === 'true') {
            return;
        }

        form.dataset.ajaxStoreBound = 'true';

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"], button:not([type])');

            button?.setAttribute('disabled', 'disabled');
            form.classList.add('is-submitting');

            try {
                const response = await window.fetch(form.action, {
                    method: form.method || 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                });

                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Request failed.');
                }

                updateCount('[data-cart-count]', payload.cart_count);
                updateCount('[data-wishlist-count]', payload.wishlist_count);

                if (payload.action === 'removed') {
                    form.closest('[data-favorite-item]')?.remove();
                }

                window.dispatchEvent(new CustomEvent('store:notice', {
                    detail: { message: payload.message || '' },
                }));
            } catch (error) {
                window.dispatchEvent(new CustomEvent('store:notice', {
                    detail: { message: error.message || 'Unable to complete the request.' },
                }));
            } finally {
                button?.removeAttribute('disabled');
                form.classList.remove('is-submitting');
            }
        });
    });
};

const initializeAjaxFilters = (root = document) => {
    const loadTarget = async (url, targetSelector, pushState = true) => {
        const target = document.querySelector(targetSelector);

        if (!target) {
            window.location.href = url;
            return;
        }

        target.classList.add('is-loading');

        try {
            const response = await window.fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            });

            const html = await response.text();

            if (!response.ok) {
                throw new Error('Unable to load results.');
            }

            const parser = new DOMParser();
            const nextDocument = parser.parseFromString(html, 'text/html');
            const nextTarget = nextDocument.querySelector(targetSelector);

            if (!nextTarget) {
                window.location.href = url;
                return;
            }

            target.innerHTML = nextTarget.innerHTML;

            const currentFilterForm = document.getElementById('product-filter-form');
            const nextFilterForm = nextDocument.getElementById('product-filter-form');

            if (currentFilterForm && nextFilterForm && !target.contains(currentFilterForm)) {
                currentFilterForm.replaceWith(nextFilterForm);
            }

            if (pushState) {
                window.history.pushState({}, '', url);
            }

            initializeAjaxFilters(document);
            initializeAsyncStoreActions(target);
            initializeBrandFilterSearch(document);
            initializeLoadMoreProducts(document);
        } catch (error) {
            window.dispatchEvent(new CustomEvent('store:notice', {
                detail: { message: error.message || 'Unable to load results.' },
            }));
        } finally {
            target.classList.remove('is-loading');
        }
    };

    root.querySelectorAll('[data-ajax-filter]').forEach((form) => {
        if (form.dataset.ajaxFilterBound === 'true') {
            return;
        }

        form.dataset.ajaxFilterBound = 'true';

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const targetSelector = form.dataset.ajaxTarget;

            if (!targetSelector) {
                form.submit();
                return;
            }

            const url = new URL(form.action || window.location.href, window.location.origin);
            const formData = new FormData(form);

            Array.from(url.searchParams.keys()).forEach((key) => url.searchParams.delete(key));

            formData.forEach((value, key) => {
                if (String(value).trim() !== '') {
                    url.searchParams.set(key, String(value));
                }
            });

            loadTarget(url.toString(), targetSelector);
        });

        form.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => form.requestSubmit());
        });
    });

    root.querySelectorAll('[data-filter-link]').forEach((link) => {
        if (link.dataset.ajaxFilterLinkBound === 'true') {
            return;
        }

        link.dataset.ajaxFilterLinkBound = 'true';

        link.addEventListener('click', (event) => {
            const targetSelector = link.dataset.ajaxTarget;

            if (!targetSelector) {
                return;
            }

            event.preventDefault();
            loadTarget(link.href, targetSelector);
        });
    });

    root.querySelectorAll('[data-ajax-filter-target] nav a, [data-ajax-filter-target] .pagination a').forEach((link) => {
        if (link.dataset.ajaxPaginationBound === 'true') {
            return;
        }

        const target = link.closest('[data-ajax-filter-target]');

        if (!target) {
            return;
        }

        link.dataset.ajaxPaginationBound = 'true';
        link.addEventListener('click', (event) => {
            event.preventDefault();
            loadTarget(link.href, `[data-ajax-filter-target="${target.dataset.ajaxFilterTarget}"]`);
        });
    });

    window.onpopstate = () => {
        const target = document.querySelector('[data-ajax-filter-target]');

        if (target?.dataset.ajaxFilterTarget) {
            loadTarget(window.location.href, `[data-ajax-filter-target="${target.dataset.ajaxFilterTarget}"]`, false);
        }
    };
};

const initializeLoadMoreProducts = (root = document) => {
    root.querySelectorAll('[data-load-more]').forEach((link) => {
        if (link.dataset.loadMoreBound === 'true') {
            return;
        }

        link.dataset.loadMoreBound = 'true';

        link.addEventListener('click', async (event) => {
            event.preventDefault();

            const targetSelector = link.dataset.ajaxTarget;
            const target = targetSelector ? document.querySelector(targetSelector) : link.closest('[data-ajax-filter-target]');
            const list = target?.querySelector('[data-product-list]');

            if (!target || !list) {
                window.location.href = link.href;
                return;
            }

            link.setAttribute('aria-busy', 'true');
            link.classList.add('is-loading');

            try {
                const response = await window.fetch(link.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                const html = await response.text();

                if (!response.ok) {
                    throw new Error('Unable to load products.');
                }

                const parser = new DOMParser();
                const nextDocument = parser.parseFromString(html, 'text/html');
                const nextTarget = targetSelector
                    ? nextDocument.querySelector(targetSelector)
                    : nextDocument.querySelector(`[data-ajax-filter-target="${target.dataset.ajaxFilterTarget}"]`);
                const nextList = nextTarget?.querySelector('[data-product-list]');

                if (!nextList) {
                    window.location.href = link.href;
                    return;
                }

                nextList.querySelectorAll('article').forEach((article) => list.appendChild(article));

                const currentMore = target.querySelector('.premium-load-more-wrap');
                const nextMore = nextTarget.querySelector('.premium-load-more-wrap');

                if (currentMore && nextMore) {
                    currentMore.innerHTML = nextMore.innerHTML;
                }

                initializeAsyncStoreActions(list);
                initializeLoadMoreProducts(target);
            } catch (error) {
                window.dispatchEvent(new CustomEvent('store:notice', {
                    detail: { message: error.message || 'Unable to load products.' },
                }));
            } finally {
                link.removeAttribute('aria-busy');
                link.classList.remove('is-loading');
            }
        });
    });
};

const initializeBrandFilterSearch = (root = document) => {
    root.querySelectorAll('[data-brand-filter-search]').forEach((input) => {
        if (input.dataset.brandFilterBound === 'true') {
            return;
        }

        input.dataset.brandFilterBound = 'true';

        input.addEventListener('input', () => {
            const panel = input.closest('.product-filter-accordion');
            const query = input.value.trim().toLowerCase();

            panel?.querySelectorAll('[data-brand-filter-item]').forEach((item) => {
                item.hidden = query !== '' && !item.textContent.toLowerCase().includes(query);
            });
        });
    });
};

const initializeStoreNotices = () => {
    const toast = document.querySelector('[data-store-toast]');
    let timer = null;

    window.addEventListener('store:notice', (event) => {
        if (!toast || !event.detail?.message) {
            return;
        }

        toast.textContent = event.detail.message;
        toast.classList.add('is-visible');
        window.clearTimeout(timer);
        timer = window.setTimeout(() => toast.classList.remove('is-visible'), 2600);
    });
};

const initializeDocumentationNavigation = () => {
    document.querySelectorAll('[data-documentation-jump]').forEach((select) => {
        select.addEventListener('change', () => {
            if (select.value) {
                window.location.hash = select.value;
            }
        });
    });
};

const initializeWholesaleTierPicker = () => {
    const quantityInput = document.querySelector('[data-product-quantity]');

    if (!quantityInput) {
        return;
    }

    document.querySelectorAll('[data-wholesale-tier]').forEach((button) => {
        button.addEventListener('click', () => {
            const quantity = Number(button.dataset.quantity || 1);

            quantityInput.value = String(Math.max(1, quantity));
            quantityInput.dispatchEvent(new Event('change', { bubbles: true }));

            document.querySelectorAll('[data-wholesale-tier]').forEach((tierButton) => {
                tierButton.dataset.selected = 'false';
                tierButton.classList.remove('ring-2', 'ring-emerald-500');
            });

            button.dataset.selected = 'true';
            button.classList.add('ring-2', 'ring-emerald-500');
            quantityInput.focus({ preventScroll: true });
        });
    });
};

const initializeProductGallery = () => {
    const mainImage = document.getElementById('product-main-image');

    if (!mainImage) {
        return;
    }

    document.querySelectorAll('[data-product-gallery-thumb]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.image) {
                mainImage.src = button.dataset.image;
            }
        });
    });
};

const initializeCheckoutShipping = () => {
    const form = document.querySelector('[data-checkout-shipping]');

    if (!form) {
        return;
    }

    const citySelect = form.querySelector('[data-shipping-city]');
    const carrierContainer = form.querySelector('[data-shipping-carriers]');
    const subtotalElement = form.querySelector('[data-checkout-subtotal]');
    const shippingCostElement = form.querySelector('[data-checkout-shipping-cost]');
    const totalElement = form.querySelector('[data-checkout-total]');
    const carriersUrl = form.dataset.carriersUrl;
    const quoteUrl = form.dataset.quoteUrl;
    const subtotal = Number(subtotalElement?.dataset.checkoutSubtotal || 0);

    const money = (value) => {
        const currency = window.storeCurrency || { code: 'USD', symbol: '$', rate: 1, decimals: 2 };
        const amount = Number(value || 0) * Number(currency.rate || 1);

        return `${currency.symbol || ''} ${amount.toLocaleString(undefined, {
            minimumFractionDigits: Number(currency.decimals || 0),
            maximumFractionDigits: Number(currency.decimals || 0),
        })} ${currency.code || ''}`.trim();
    };

    const updateTotals = (shippingCost) => {
        if (shippingCostElement) {
            shippingCostElement.textContent = money(shippingCost);
        }

        if (totalElement) {
            totalElement.textContent = money(subtotal + Number(shippingCost || 0));
        }
    };

    const carrierMarkup = (carrier, checked = false) => `
        <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
            <input type="radio" name="shipping_carrier_id" value="${carrier.id}" data-shipping-cost="${carrier.cost}" class="sr-only" ${checked ? 'checked' : ''}>
            <span class="block font-black">${carrier.name}</span>
            <span class="mt-1 block text-sm text-slate-600">${carrier.estimated_delivery_time || ''}</span>
            <span class="mt-3 block text-lg font-black text-red-700">${money(carrier.cost)}</span>
        </label>
    `;

    const loadQuote = async () => {
        const carrier = form.querySelector('input[name="shipping_carrier_id"]:checked');

        if (!quoteUrl || !citySelect?.value || !carrier?.value) {
            updateTotals(0);
            return;
        }

        const url = new URL(quoteUrl, window.location.origin);
        url.searchParams.set('city_id', citySelect.value);
        url.searchParams.set('shipping_carrier_id', carrier.value);

        const response = await window.fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message || 'Unable to calculate shipping.');
        }

        updateTotals(payload.cost || 0);
    };

    const loadCarriers = async () => {
        if (!carriersUrl || !citySelect?.value || !carrierContainer) {
            return;
        }

        const url = new URL(carriersUrl, window.location.origin);
        url.searchParams.set('city_id', citySelect.value);

        const response = await window.fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message || 'Unable to load shipping carriers.');
        }

        if (!payload.requires_shipping) {
            carrierContainer.innerHTML = `<div class="rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-800">${window.storeTranslations?.noShippingRequired || 'This cart does not require shipping.'}</div>`;
            updateTotals(0);
            return;
        }

        if (!payload.carriers || payload.carriers.length === 0) {
            carrierContainer.innerHTML = `<div class="rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800">${window.storeTranslations?.noCarriers || 'No shipping carriers are available for this city right now.'}</div>`;
            updateTotals(0);
            return;
        }

        carrierContainer.innerHTML = payload.carriers.map((carrier, index) => carrierMarkup(carrier, index === 0)).join('');
        updateTotals(payload.carriers[0]?.cost || 0);
        await loadQuote();
    };

    const addressModeInput = form.querySelector('[data-address-mode-input]');
    const newAddressForm = form.querySelector('[data-new-address-form]');

    form.querySelectorAll('input[name="user_address_id"]').forEach((input) => {
        input.addEventListener('change', async () => {
            if (!input.checked) {
                return;
            }

            if (addressModeInput) {
                addressModeInput.value = input.dataset.addressMode || 'saved';
            }

            if (newAddressForm) {
                newAddressForm.classList.toggle('hidden', input.dataset.addressMode !== 'new');
            }

            if (input.dataset.addressMode === 'saved' && input.dataset.addressCityId && citySelect) {
                citySelect.value = input.dataset.addressCityId;
                await loadCarriers();
            }
        });
    });

    citySelect?.addEventListener('change', loadCarriers);
    carrierContainer?.addEventListener('change', (event) => {
        if (event.target?.matches('input[name="shipping_carrier_id"]')) {
            loadQuote().catch((error) => {
                window.dispatchEvent(new CustomEvent('store:notice', {
                    detail: { message: error.message },
                }));
            });
        }
    });

    const checkedAddress = form.querySelector('input[name="user_address_id"]:checked');

    if (checkedAddress?.dataset.addressCityId && citySelect) {
        citySelect.value = checkedAddress.dataset.addressCityId;
        loadCarriers().catch(() => updateTotals(0));
    } else {
        updateTotals(0);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getPreferredTheme(), false);
    initializeStorefrontMotion();
    initializeStorefrontEffects();
    initializeHeroSliders();
    initializeHeaderNavigation();
    initializeStoreNotices();
    initializeAsyncStoreActions();
    initializeAjaxFilters();
    initializeBrandFilterSearch();
    initializeLoadMoreProducts();
    initializeDocumentationNavigation();
    initializeWholesaleTierPicker();
    initializeProductGallery();
    initializeCheckoutShipping();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

            applyTheme(nextTheme);
        });
    });
});
