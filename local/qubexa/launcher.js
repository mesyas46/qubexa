(function() {
    'use strict';

    function initialiseQubexaLauncher() {
        var launcher = document.querySelector('[data-qubexa-launcher]');
        var frame = document.querySelector('[data-qubexa-frame]');

        var reducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;

        /*
         * İlk açılış ekranı.
         * Aynı sekmede yalnızca bir kez gösterilir.
         */
        if (launcher) {
            var storageKey = 'qubexa-launcher-shown';
            var alreadyShown = false;

            try {
                alreadyShown = sessionStorage.getItem(storageKey) === '1';
            } catch (error) {
                alreadyShown = false;
            }

            function hideLauncher() {
                launcher.classList.add('is-hidden');
                launcher.setAttribute('aria-hidden', 'true');

                window.setTimeout(function() {
                    if (launcher.parentNode) {
                        launcher.parentNode.removeChild(launcher);
                    }
                }, reducedMotion ? 0 : 360);
            }

            if (alreadyShown || reducedMotion) {
                hideLauncher();
            } else {
                try {
                    sessionStorage.setItem(storageKey, '1');
                } catch (error) {
                    // Session storage kapalı olsa da launcher çalışır.
                }

                window.setTimeout(hideLauncher, 900);
            }
        }

        if (!frame) {
            return;
        }

        /*
         * Sayfa geçiş perdesini oluştur.
         */
        var transitionLayer = document.createElement('div');

        transitionLayer.className = 'qubexa-page-transition';
        transitionLayer.setAttribute('aria-hidden', 'true');

        transitionLayer.innerHTML =
            '<div class="qubexa-page-transition__inner">' +
                '<span class="qubexa-page-transition__mark">Q</span>' +
                '<span class="qubexa-page-transition__text">QUBEXA</span>' +
                '<span class="qubexa-page-transition__line"></span>' +
            '</div>';

        document.body.appendChild(transitionLayer);

        function startPageTransition(destination) {
            frame.classList.add('is-page-leaving');
            transitionLayer.classList.add('is-visible');
            transitionLayer.setAttribute('aria-hidden', 'false');

            window.setTimeout(function() {
                window.location.href = destination;
            }, reducedMotion ? 0 : 480);
        }

        frame
            .querySelectorAll('.qubexa-nav-item')
            .forEach(function(link) {
                link.addEventListener('click', function(event) {
                    if (
                        event.defaultPrevented ||
                        event.ctrlKey ||
                        event.metaKey ||
                        event.shiftKey ||
                        event.altKey ||
                        link.target === '_blank'
                    ) {
                        return;
                    }

                    var destination = link.href;

                    if (!destination) {
                        return;
                    }

                    var currentUrl = window.location.href.split('#')[0];
                    var destinationUrl = destination.split('#')[0];

                    if (currentUrl === destinationUrl) {
                        return;
                    }

                    event.preventDefault();
                    startPageTransition(destination);
                });
            });

        window.addEventListener('pageshow', function() {
            frame.classList.remove('is-page-leaving');
            transitionLayer.classList.remove('is-visible');
            transitionLayer.setAttribute('aria-hidden', 'true');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initialiseQubexaLauncher
        );
    } else {
        initialiseQubexaLauncher();
    }
})();