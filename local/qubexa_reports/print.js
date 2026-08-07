(function() {
    'use strict';

    var initialise = function() {
        var button = document.querySelector(
            '[data-action="print-report"]'
        );

        if (!button) {
            return;
        }

        button.addEventListener('click', function() {
            window.print();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initialise
        );
    } else {
        initialise();
    }
}());
