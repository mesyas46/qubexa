document.addEventListener('DOMContentLoaded', function () {
    const panel = document.querySelector(
        '[data-qubexa-student-panel]'
    );
    const overlay = document.querySelector(
        '[data-qubexa-student-overlay]'
    );

    if (!panel || !overlay) {
        return;
    }

    const state = {
        studentId: 0,
        activeTab: 'general'
    };

    const emit = function (name, detail) {
        document.dispatchEvent(
            new CustomEvent(name, {
                detail: detail || {}
            })
        );
    };

    const setText = function (selector, value) {
        const element = panel.querySelector(selector);

        if (element) {
            element.textContent = value || 'Belirtilmemiş';
        }
    };

    const close = function () {
        panel.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        document.body.classList.remove('qubexa-panel-open');
        panel.setAttribute('aria-hidden', 'true');

        emit('qubexa:student-panel-closed', {
            studentId: state.studentId
        });

        state.studentId = 0;
    };

    const open = function (button) {
        state.studentId = Number(
            button.dataset.studentId || 0
        );

        setText(
            '[data-panel-initials]',
            button.dataset.initials
        );
        setText(
            '[data-panel-fullname]',
            button.dataset.fullname
        );
        setText(
            '[data-panel-grade]',
            button.dataset.grade
        );
        setText(
            '[data-panel-group]',
            button.dataset.group
        );
        setText(
            '[data-panel-phone]',
            button.dataset.phone
        );
        setText(
            '[data-panel-parent]',
            button.dataset.parent
        );

        const status = panel.querySelector(
            '[data-panel-status]'
        );

        if (status) {
            const isActive =
                button.dataset.status === 'active';

            status.textContent = isActive ? 'Aktif' : 'Pasif';
            status.classList.toggle('is-active', isActive);
            status.classList.toggle('is-passive', !isActive);
        }

        const editLink = panel.querySelector(
            '[data-panel-edit]'
        );

        if (editLink) {
            editLink.href = button.dataset.editurl || '#';
        }

        panel.classList.add('is-open');
        overlay.classList.add('is-visible');
        document.body.classList.add('qubexa-panel-open');
        panel.setAttribute('aria-hidden', 'false');

        emit('qubexa:student-panel-opened', {
            studentId: state.studentId
        });
    };

    window.QubexaStudentPanel = {
        panel: panel,
        overlay: overlay,
        state: state,
        getStudentId: function () {
            return state.studentId;
        },
        emit: emit,
        open: open,
        close: close
    };

    document.addEventListener('click', function (event) {
        const openButton = event.target.closest(
            '[data-open-student-panel]'
        );

        if (openButton) {
            event.preventDefault();
            open(openButton);
            return;
        }

        if (
            event.target.closest(
                '[data-close-student-panel]'
            ) ||
            event.target === overlay
        ) {
            close();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            panel.classList.contains('is-open')
        ) {
            close();
        }
    });
});
