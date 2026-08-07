(function() {
    'use strict';

    const initialisePoints = function() {
        document.addEventListener('click', function(event) {
            const button = event.target.closest(
                '[data-action="qubexa-point"]'
            );

            if (!button) {
                return;
            }

            event.preventDefault();

            const card = button.closest(
                '.qubexa-class-student'
            );

            if (!card ||
                    card.classList.contains('is-updating')) {
                return;
            }

            card.classList.add('is-updating');

            const buttons = card.querySelectorAll(
                '[data-action="qubexa-point"]'
            );

            buttons.forEach(function(item) {
                item.setAttribute(
                    'aria-disabled',
                    'true'
                );
            });

            require(
                ['core/ajax', 'core/notification'],
                function(Ajax, Notification) {
                    const request = Ajax.call([{
                        methodname:
                            'local_qubexa_classes_add_point',

                        args: {
                            classid: Number(
                                button.dataset.classid
                            ),
                            studentid: Number(
                                button.dataset.studentid
                            ),
                            pointvalue: Number(
                                button.dataset.pointvalue
                            ),
                        },
                    }])[0];

                    request.then(function(result) {
                        const minusCount =
                            card.querySelector(
                                '[data-role="minus-count"]'
                            );

                        const plusCount =
                            card.querySelector(
                                '[data-role="plus-count"]'
                            );

                        const netTotal =
                            card.querySelector(
                                '[data-role="net-total"]'
                            );

                        const netBox =
                            card.querySelector(
                                '.qubexa-point-net'
                            );
                        const totalMinusCount =
                            card.querySelector(
                                '[data-role="total-minus-count"]'
                            );

                        const totalPlusCount =
                            card.querySelector(
                                '[data-role="total-plus-count"]'
                            );

                        const totalNet =
                            card.querySelector(
                                '[data-role="total-net"]'
                            );

                        minusCount.textContent =
                            result.minuscount;

                        plusCount.textContent =
                            result.pluscount;

                        const total =
                            Number(result.pointtotal);

                        netTotal.textContent =
                            total > 0
                                ? '+' + total
                                : String(total);

                        netBox.classList.toggle(
                            'is-positive',
                            total > 0
                        );
                        if (totalMinusCount &&
                                totalPlusCount &&
                                totalNet) {

                            totalMinusCount.textContent =
                                '−' + result.totalminuscount;

                            totalPlusCount.textContent =
                                '+' + result.totalpluscount;

                            const allTotal = Number(
                                result.totalpointtotal
                            );

                            totalNet.textContent =
                                'Net ' +
                                (
                                    allTotal > 0
                                        ? '+' + allTotal
                                        : String(allTotal)
                                );

                            totalNet.classList.toggle(
                                'is-positive',
                                allTotal > 0
                            );

                            totalNet.classList.toggle(
                                'is-negative',
                                allTotal < 0
                            );
                        }

                        netBox.classList.toggle(
                            'is-negative',
                            total < 0
                        );

                        const undoButton =
                            card.querySelector(
                                '[data-role="undo-point"]'
                            );

                        if (undoButton) {
                            const hasTodayPoints =
                                Number(result.pluscount) +
                                Number(result.minuscount) > 0;

                            undoButton.disabled =
                                !hasTodayPoints;

                            undoButton.classList.toggle(
                                'is-disabled',
                                !hasTodayPoints
                            );
                        }
                    }, function(error) {
                        Notification.exception(error);
                    }).then(function() {
                        card.classList.remove(
                            'is-updating'
                        );

                        buttons.forEach(function(item) {
                            item.removeAttribute(
                                'aria-disabled'
                            );
                        });
                    });
                }
            );
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initialisePoints
        );
    } else {
        initialisePoints();
    }
})();