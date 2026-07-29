(function() {
    'use strict';

    function initialiseQubexaMobileMenu() {
        var frame = document.querySelector('[data-qubexa-frame]');
        var sidebar = document.querySelector('[data-qubexa-sidebar]');
        var openButton = document.querySelector('[data-qubexa-open]');
        var closeButton = document.querySelector('[data-qubexa-close]');
        var overlay = document.querySelector('[data-qubexa-overlay]');
        var appbar = document.querySelector('.qubexa-appbar');

        if (!frame || !sidebar || !openButton || !overlay || !appbar) {
            return;
        }

        var mobileBreakpoint = 1000;
        var originalParent = openButton.parentNode;
        var originalNextSibling = openButton.nextSibling;

        function isMobile() {
            return window.innerWidth <= mobileBreakpoint;
        }

        function applyFloatingButtonStyles() {
            openButton.style.setProperty('position', 'fixed', 'important');
            openButton.style.setProperty('top', '12px', 'important');
            openButton.style.setProperty('left', '12px', 'important');
            openButton.style.setProperty('right', 'auto', 'important');
            openButton.style.setProperty('bottom', 'auto', 'important');
            openButton.style.setProperty('margin', '0', 'important');
            openButton.style.setProperty('transform', 'none', 'important');
            openButton.style.setProperty('z-index', '7000', 'important');
            openButton.style.setProperty('display', 'inline-flex', 'important');
        }

        function clearFloatingButtonStyles() {
            [
                'position',
                'top',
                'left',
                'right',
                'bottom',
                'margin',
                'transform',
                'z-index',
                'display'
            ].forEach(function(property) {
                openButton.style.removeProperty(property);
            });
        }

        function moveButtonToViewport() {
            if (!isMobile()) {
                return;
            }

            if (openButton.parentNode !== document.body) {
                document.body.appendChild(openButton);
            }

            openButton.classList.add('qubexa-mobile-menu--floating');
            appbar.classList.add('has-floating-menu');

            applyFloatingButtonStyles();
        }

        function restoreButtonToAppbar() {
            if (isMobile()) {
                return;
            }

            clearFloatingButtonStyles();

            openButton.classList.remove('qubexa-mobile-menu--floating');
            appbar.classList.remove('has-floating-menu');

            if (openButton.parentNode === originalParent) {
                return;
            }

            if (
                originalNextSibling &&
                originalNextSibling.parentNode === originalParent
            ) {
                originalParent.insertBefore(
                    openButton,
                    originalNextSibling
                );
            } else {
                originalParent.appendChild(openButton);
            }
        }

        function openMenu() {
            if (!isMobile()) {
                return;
            }

            frame.classList.add('is-sidebar-open');
            document.body.classList.add('qubexa-menu-open');

            openButton.setAttribute('aria-expanded', 'true');
            overlay.setAttribute('aria-hidden', 'false');
        }

        function closeMenu(restoreFocus) {
            frame.classList.remove('is-sidebar-open');
            document.body.classList.remove('qubexa-menu-open');

            openButton.setAttribute('aria-expanded', 'false');
            overlay.setAttribute('aria-hidden', 'true');

            if (restoreFocus && isMobile()) {
                openButton.focus();
            }
        }

        function synchroniseLayout() {
            if (isMobile()) {
                moveButtonToViewport();
            } else {
                closeMenu(false);
                restoreButtonToAppbar();
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

        window.addEventListener('resize', synchroniseLayout);
        window.addEventListener('orientationchange', synchroniseLayout);
        window.addEventListener('pageshow', synchroniseLayout);

        synchroniseLayout();
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