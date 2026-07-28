(function() {
    'use strict';

    function initialiseQubexaMobileMenu() {
        var frame = document.querySelector('[data-qubexa-frame]');
        var sidebar = document.querySelector('[data-qubexa-sidebar]');
        var openButton = document.querySelector('[data-qubexa-open]');
        var closeButton = document.querySelector('[data-qubexa-close]');
        var overlay = document.querySelector('[data-qubexa-overlay]');

        if (!frame || !sidebar || !openButton || !overlay) {
            return;
        }

        var mobileBreakpoint = 1000;

        function isMobile() {
            return window.innerWidth <= mobileBreakpoint;
        }

        function openMenu() {
            if (!isMobile()) {
                return;
            }

            frame.classList.add('is-sidebar-open');
            document.body.classList.add('qubexa-menu-open');

            openButton.setAttribute('aria-expanded', 'true');
            overlay.setAttribute('aria-hidden', 'false');

            var firstLink = sidebar.querySelector('.qubexa-nav-item');

            if (firstLink) {
                window.setTimeout(function() {
                    firstLink.focus();
                }, 180);
            }
        }

        function closeMenu(restoreFocus) {
            frame.classList.remove('is-sidebar-open');
            document.body.classList.remove('qubexa-menu-open');

            openButton.setAttribute('aria-expanded', 'false');
            overlay.setAttribute('aria-hidden', 'true');

            if (restoreFocus) {
                openButton.focus();
            }
        }

        openButton.addEventListener('click', function() {
            if (frame.classList.contains('is-sidebar-open')) {
                closeMenu(false);
            } else {
                openMenu();
            }
        });

        if (closeButton) {
            closeButton.addEventListener('click', function() {
                closeMenu(true);
            });
        }

        overlay.addEventListener('click', function() {
            closeMenu(true);
        });

        sidebar
            .querySelectorAll('.qubexa-nav-item')
            .forEach(function(link) {
                link.addEventListener('click', function() {
                    if (isMobile()) {
                        closeMenu(false);
                    }
                });
            });

        document.addEventListener('keydown', function(event) {
            if (
                event.key === 'Escape' &&
                frame.classList.contains('is-sidebar-open')
            ) {
                closeMenu(true);
            }
        });

        window.addEventListener('resize', function() {
            if (!isMobile()) {
                closeMenu(false);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initialiseQubexaMobileMenu
        );
    } else {
        initialiseQubexaMobileMenu();
    }
})();