document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const buttons = panel.querySelectorAll(
        '[data-student-tab]'
    );
    const contents = panel.querySelectorAll(
        '[data-student-tab-content]'
    );

    const show = function (name) {
        buttons.forEach(function (button) {
            const active =
                button.dataset.studentTab === name;

            button.classList.toggle('is-active', active);
            button.setAttribute(
                'aria-selected',
                active ? 'true' : 'false'
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

        if (tabButton && panel.contains(tabButton)) {
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

    document.addEventListener(
        'qubexa:student-panel-opened',
        function () {
            show('general');
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            show('general');
        }
    );
});
