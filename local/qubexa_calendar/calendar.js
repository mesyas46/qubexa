(function() {
    'use strict';

    function initialiseCalendar() {
        var calendar = document.querySelector(
            '[data-qubexa-calendar]'
        );

        if (!calendar) {
            return;
        }

        calendar.addEventListener('click', function(event) {
            var button = event.target.closest(
                '[data-add-calendar-event]'
            );

            if (!button) {
                return;
            }

            var url = new URL(
                M.cfg.wwwroot +
                '/local/qubexa_calendar/edit.php'
            );

            url.searchParams.set(
                'date',
                button.dataset.date || ''
            );

            window.location.href = url.toString();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initialiseCalendar
        );
    } else {
        initialiseCalendar();
    }
})();