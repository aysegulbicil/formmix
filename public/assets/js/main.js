/* FORMMIX — Arayüz etkileşimleri */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    document.addEventListener('DOMContentLoaded', function () {

        /* --- Mobil menü (hamburger) --- */
        var hamburger = document.getElementById('hamburger');
        var mobileMenu = document.getElementById('mobileMenu');

        var backdrop = null;
        function ensureBackdrop() {
            if (backdrop) return backdrop;
            backdrop = document.createElement('div');
            backdrop.className = 'menu-backdrop';
            backdrop.addEventListener('click', closeMenu);
            document.body.appendChild(backdrop);
            return backdrop;
        }

        function closeMenu() {
            if (!mobileMenu || !hamburger) return;
            mobileMenu.classList.remove('is-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-open');
            if (backdrop) { backdrop.classList.remove('is-open'); }
            if (reduceMotion) {
                mobileMenu.hidden = true;
            } else {
                window.setTimeout(function () {
                    if (!mobileMenu.classList.contains('is-open')) { mobileMenu.hidden = true; }
                }, 300);
            }
        }
        function openMenu() {
            if (!mobileMenu || !hamburger) return;
            ensureBackdrop();
            mobileMenu.hidden = false;
            void mobileMenu.offsetHeight; /* reflow: açılış geçişini tetikle */
            mobileMenu.classList.add('is-open');
            hamburger.classList.add('is-open');
            hamburger.setAttribute('aria-expanded', 'true');
            document.body.classList.add('menu-open');
            backdrop.classList.add('is-open');
        }

        if (hamburger && mobileMenu) {
            hamburger.addEventListener('click', function () {
                if (mobileMenu.classList.contains('is-open')) { closeMenu(); } else { openMenu(); }
            });
            mobileMenu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });
            window.addEventListener('resize', function () {
                if (window.innerWidth > 860) closeMenu();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMenu();
            });
        }

        /* --- Sticky header gölgesi --- */
        var header = document.getElementById('siteHeader');
        if (header) {
            var onScroll = function () {
                if (window.scrollY > 8) { header.classList.add('is-scrolled'); }
                else { header.classList.remove('is-scrolled'); }
            };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        }

        /* --- Scroll-reveal animasyonları --- */
        if (!reduceMotion && 'IntersectionObserver' in window) {
            document.body.classList.add('reveal-on');

            var selectors = [
                '.section__head', '.value-card', '.ba-card', '.product-card',
                '.sector-card', '.price-card', '.step', '.cta-band',
                '.about-grid > div', '.stat', '.feature-list', '.quote',
                '.catalog-bar', '.pdf-frame', '.catalog-grid', '.card-box'
            ];
            selectors.forEach(function (sel) {
                document.querySelectorAll(sel).forEach(function (el) { el.classList.add('reveal'); });
            });

            // Izgara içi kademeli gecikme (stagger)
            ['.values-grid', '.products-grid', '.sectors-grid', '.pricing-grid', '.steps', '.stat-grid'].forEach(function (gs) {
                document.querySelectorAll(gs).forEach(function (grid) {
                    Array.prototype.forEach.call(grid.children, function (child, i) {
                        if (child.classList.contains('reveal')) {
                            child.style.transitionDelay = ((i % 4) * 70) + 'ms';
                        }
                    });
                });
            });

            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('is-visible');
                        io.unobserve(e.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

            document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
        }

        /* --- Fiyat sayaç (count-up) --- */
        if (!reduceMotion && 'IntersectionObserver' in window) {
            var amounts = document.querySelectorAll('.price-card__amount');
            var countItems = [];
            Array.prototype.forEach.call(amounts, function (el) {
                var node = el.firstChild;
                if (!node || node.nodeType !== 3) return;
                var target = parseInt(node.nodeValue.replace(/\D/g, ''), 10);
                if (!target) return;
                el.setAttribute('data-count-to', target);
                node.nodeValue = '0 ';
                countItems.push(el);
            });

            if (countItems.length) {
                var runCount = function (el) {
                    var node = el.firstChild;
                    var target = parseInt(el.getAttribute('data-count-to'), 10);
                    var dur = 750, startT = null;
                    var step = function (ts) {
                        if (startT === null) startT = ts;
                        var p = Math.min((ts - startT) / dur, 1);
                        var v = Math.round(target * (1 - Math.pow(1 - p, 3)));
                        node.nodeValue = v.toLocaleString('tr-TR') + ' ';
                        if (p < 1) { requestAnimationFrame(step); }
                    };
                    requestAnimationFrame(step);
                };
                var countObs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) {
                        if (e.isIntersecting) {
                            countObs.unobserve(e.target);
                            runCount(e.target);
                        }
                    });
                }, { threshold: 0.4 });
                countItems.forEach(function (el) { countObs.observe(el); });
            }
        }

        /* --- Özel noktalı imleç --- */
        if (finePointer && !reduceMotion) {
            var dot = document.createElement('div');
            var ring = document.createElement('div');
            dot.className = 'cursor-dot';
            ring.className = 'cursor-ring';
            document.body.appendChild(dot);
            document.body.appendChild(ring);
            document.body.classList.add('has-cursor');

            var mx = window.innerWidth / 2, my = window.innerHeight / 2;
            var rx = mx, ry = my;

            window.addEventListener('mousemove', function (e) {
                mx = e.clientX; my = e.clientY;
                dot.style.left = mx + 'px';
                dot.style.top = my + 'px';
            }, { passive: true });

            (function animate() {
                rx += (mx - rx) * 0.18;
                ry += (my - ry) * 0.18;
                ring.style.left = rx + 'px';
                ring.style.top = ry + 'px';
                requestAnimationFrame(animate);
            })();

            // Etkileşimli ögelerde halkayı büyüt
            var hoverSel = 'a, button, .btn, input, textarea, select, .product-card, .sector-card, .value-card, .price-card';
            document.addEventListener('mouseover', function (e) {
                if (e.target.closest(hoverSel)) ring.classList.add('is-active');
            });
            document.addEventListener('mouseout', function (e) {
                if (e.target.closest(hoverSel)) ring.classList.remove('is-active');
            });
            // Sekmeden çıkınca gizle
            document.addEventListener('mouseleave', function () { dot.style.opacity = '0'; ring.style.opacity = '0'; });
            document.addEventListener('mouseenter', function () { dot.style.opacity = '1'; ring.style.opacity = '1'; });
        }

    });
})();
