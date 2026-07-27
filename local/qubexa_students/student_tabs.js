document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const tabsContainer = panel.querySelector(
        '.qubexa-student-panel-tabs'
    );

    if (!tabsContainer) {
        return;
    }

    const buttons = Array.from(
        tabsContainer.querySelectorAll('[data-student-tab]')
    );
    const contents = Array.from(
        panel.querySelectorAll('[data-student-tab-content]')
    );

    const contentByName = new Map();

    contents.forEach(function (content) {
        const name = content.dataset.studentTabContent;

        if (name) {
            contentByName.set(name, content);
        }
    });

    const validButtons = buttons.filter(function (button) {
        const name = button.dataset.studentTab;
        const valid = Boolean(
            name && contentByName.has(name)
        );

        if (!valid) {
            button.hidden = true;
            button.disabled = true;
            button.setAttribute('aria-hidden', 'true');
        }

        return valid;
    });

    const validNames = new Set(
        validButtons.map(function (button) {
            return button.dataset.studentTab;
        })
    );

    contents.forEach(function (content) {
        if (
            !validNames.has(
                content.dataset.studentTabContent
            )
        ) {
            content.hidden = true;
            content.classList.remove('is-active');
        }
    });

    tabsContainer.style.setProperty(
        '--qubexa-panel-tab-count',
        String(Math.max(validButtons.length, 1))
    );

    const fallback = validButtons.length
        ? validButtons[0].dataset.studentTab
        : '';

    const show = function (requestedName) {
        const name = validNames.has(requestedName)
            ? requestedName
            : fallback;

        if (!name) {
            return;
        }

        validButtons.forEach(function (button) {
            const active =
                button.dataset.studentTab === name;

            button.classList.toggle('is-active', active);
            button.setAttribute(
                'aria-selected',
                active ? 'true' : 'false'
            );
            button.setAttribute(
                'tabindex',
                active ? '0' : '-1'
            );
        });

        contents.forEach(function (content) {
            const active =
                content.dataset.studentTabContent === name;

            content.classList.toggle('is-active', active);
            content.hidden = !active;
        });

        api.state.activeTab = name;

        api.emit('qubexa:student-tab-changed', {
            studentId: api.getStudentId(),
            tab: name
        });
    };

    api.showTab = show;

    document.addEventListener('click', function (event) {
        const tabButton = event.target.closest(
            '[data-student-tab]'
        );

        if (
            tabButton &&
            tabsContainer.contains(tabButton) &&
            !tabButton.disabled
        ) {
            show(tabButton.dataset.studentTab);
            return;
        }

        const featureButton = event.target.closest(
            '[data-open-student-tab]'
        );

        if (
            featureButton &&
            panel.contains(featureButton)
        ) {
            show(featureButton.dataset.openStudentTab);
        }
    });

    tabsContainer.addEventListener(
        'keydown',
        function (event) {
            const allowed = [
                'ArrowLeft',
                'ArrowRight',
                'Home',
                'End'
            ];

            if (!allowed.includes(event.key)) {
                return;
            }

            const currentIndex = validButtons.indexOf(
                document.activeElement
            );

            if (currentIndex < 0) {
                return;
            }

            event.preventDefault();

            let nextIndex = currentIndex;

            if (event.key === 'ArrowRight') {
                nextIndex =
                    (currentIndex + 1) % validButtons.length;
            } else if (event.key === 'ArrowLeft') {
                nextIndex =
                    (
                        currentIndex -
                        1 +
                        validButtons.length
                    ) % validButtons.length;
            } else if (event.key === 'Home') {
                nextIndex = 0;
            } else if (event.key === 'End') {
                nextIndex = validButtons.length - 1;
            }

            validButtons[nextIndex].focus();
            show(
                validButtons[nextIndex].dataset.studentTab
            );
        }
    );

    document.addEventListener(
        'qubexa:student-panel-opened',
        function () {
            show(fallback);
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            show(fallback);
        }
    );

    show(fallback);
});
