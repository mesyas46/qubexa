document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const endpoint = panel.dataset.timelineEndpoint || '';

    const loading = panel.querySelector(
        '[data-student-timeline-loading]'
    );
    const empty = panel.querySelector(
        '[data-student-timeline-empty]'
    );
    const list = panel.querySelector(
        '[data-student-timeline-list]'
    );
    const status = panel.querySelector(
        '[data-student-timeline-status]'
    );
    const filterButtons = panel.querySelectorAll(
        '[data-timeline-filter]'
    );
    const refreshButton = panel.querySelector(
        '[data-timeline-refresh]'
    );

    if (
        !endpoint ||
        !loading ||
        !empty ||
        !list ||
        !status
    ) {
        return;
    }

    let activeFilter = 'all';

    const setStatus = function (message, error) {
        status.textContent = message || '';
        status.classList.toggle(
            'is-error',
            Boolean(error)
        );
    };

    const render = function (data) {
        const items = data.items || [];

        list.innerHTML = '';
        empty.hidden = items.length > 0;

        items.forEach(function (event) {
            const article = document.createElement('article');
            article.className =
                'qubexa-timeline-item is-' +
                (event.type || 'student');

            const rail = document.createElement('div');
            rail.className = 'qubexa-timeline-rail';

            const icon = document.createElement('span');
            icon.className = 'qubexa-timeline-icon';
            icon.textContent = event.icon || '•';

            rail.appendChild(icon);

            const card = document.createElement('div');
            card.className = 'qubexa-timeline-card';

            const header = document.createElement('div');
            header.className = 'qubexa-timeline-card-header';

            const titleArea = document.createElement('div');

            const type = document.createElement('span');
            type.className = 'qubexa-timeline-type';
            type.textContent = event.subtitle || '';

            const title = document.createElement('h4');
            title.textContent = event.title || '';

            titleArea.appendChild(type);
            titleArea.appendChild(title);

            const date = document.createElement('time');
            date.textContent = event.date || '';

            header.appendChild(titleArea);
            header.appendChild(date);
            card.appendChild(header);

            if (event.meta) {
                const meta = document.createElement('div');
                meta.className = 'qubexa-timeline-meta';
                meta.textContent = event.meta;
                card.appendChild(meta);
            }

            if (event.content) {
                const content = document.createElement('p');
                content.className = 'qubexa-timeline-content';
                content.textContent = event.content;
                card.appendChild(content);
            }

            article.appendChild(rail);
            article.appendChild(card);
            list.appendChild(article);
        });
    };

    const selectFilter = function (filter) {
        activeFilter = filter || 'all';

        filterButtons.forEach(function (button) {
            button.classList.toggle(
                'is-active',
                button.dataset.timelineFilter === activeFilter
            );
        });
    };

    const load = async function () {
        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        loading.hidden = false;
        setStatus('', false);

        try {
            const params = new URLSearchParams();
            params.set('studentid', String(studentId));
            params.set('filter', activeFilter);
            params.set('limit', '100');

            const response = await fetch(
                endpoint + '?' + params.toString(),
                {
                    credentials: 'same-origin'
                }
            );

            const text = await response.text();
            let result;

            try {
                result = JSON.parse(text);
            } catch (error) {
                throw new Error(
                    'Timeline verileri okunamadı.'
                );
            }

            if (!result.success) {
                throw new Error(
                    result.message ||
                    'Timeline yüklenemedi.'
                );
            }

            render(result.data || {});
        } catch (error) {
            setStatus(error.message, true);
        } finally {
            loading.hidden = true;
        }
    };

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            selectFilter(
                button.dataset.timelineFilter
            );
            load();
        });
    });

    if (refreshButton) {
        refreshButton.addEventListener(
            'click',
            function () {
                load();
            }
        );
    }

    document.addEventListener(
        'qubexa:student-tab-changed',
        function (event) {
            if (event.detail.tab === 'timeline') {
                load();
            }
        }
    );

    document.addEventListener(
        'qubexa:student-panel-opened',
        function () {
            selectFilter('all');
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            list.innerHTML = '';
            empty.hidden = true;
            setStatus('', false);
            selectFilter('all');
        }
    );
});
