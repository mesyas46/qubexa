document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const endpoint = panel.dataset.homeworksEndpoint || '';
    const sesskey = panel.dataset.sesskey || '';
    const form = panel.querySelector('[data-student-homework-form]');
    const status = panel.querySelector('[data-student-homework-status]');
    const loading = panel.querySelector(
        '[data-student-homeworks-loading]'
    );
    const empty = panel.querySelector('[data-student-homeworks-empty]');
    const list = panel.querySelector('[data-student-homeworks-list]');

    if (
        !endpoint ||
        !form ||
        !status ||
        !loading ||
        !empty ||
        !list
    ) {
        return;
    }

    const summary = {
        total: panel.querySelector('[data-homework-total]'),
        pending: panel.querySelector('[data-homework-pending]'),
        completed: panel.querySelector('[data-homework-completed]'),
        overdue: panel.querySelector('[data-homework-overdue]')
    };
    let requestVersion = 0;

    const setStatus = function (message, error) {
        status.textContent = message || '';
        status.classList.toggle('is-error', Boolean(error));
    };

    const render = function (data) {
        const items = data.items || [];
        const values = data.summary || {};

        list.innerHTML = '';
        empty.hidden = items.length > 0;

        Object.keys(summary).forEach(function (key) {
            if (summary[key]) {
                const value = Object.prototype.hasOwnProperty.call(
                    values,
                    key
                ) ? values[key] : 0;

                summary[key].textContent = String(value);
            }
        });

        items.forEach(function (homework) {
            const article = document.createElement('article');
            const header = document.createElement('div');
            const body = document.createElement('div');
            const title = document.createElement('h4');
            const due = document.createElement('time');
            const actions = document.createElement('div');
            const badge = document.createElement('span');
            const toggle = document.createElement('button');
            const remove = document.createElement('button');

            article.className =
                'qubexa-homework-item is-' +
                (homework.statusclass || 'pending');
            header.className = 'qubexa-homework-item-header';
            body.className = 'qubexa-homework-item-body';
            actions.className = 'qubexa-homework-actions';
            badge.className =
                'qubexa-homework-status is-' +
                (homework.statusclass || 'pending');

            title.textContent = homework.title || 'Ödev';
            due.textContent = 'Son tarih: ' + (homework.duedate || '—');
            badge.textContent = homework.statuslabel || 'Bekliyor';

            toggle.type = 'button';
            toggle.className = 'qubexa-homework-toggle-status';
            toggle.dataset.homeworkStatus = String(homework.id);
            toggle.dataset.nextStatus = homework.iscompleted
                ? 'pending'
                : 'completed';
            toggle.textContent = homework.iscompleted
                ? 'Bekliyor yap'
                : 'Tamamlandı yap';

            remove.type = 'button';
            remove.className = 'qubexa-module-delete';
            remove.dataset.deleteStudentHomework = String(homework.id);
            remove.textContent = 'Sil';
            remove.setAttribute(
                'aria-label',
                (homework.title || 'Ödev') + ' kaydını sil'
            );

            body.append(title, due);
            actions.append(badge, toggle, remove);
            header.append(body, actions);
            article.append(header);

            if (homework.description) {
                const description = document.createElement('p');

                description.textContent = homework.description;
                article.append(description);
            }

            list.append(article);
        });
    };

    const isCurrentRequest = function (version, studentId) {
        return version === requestVersion &&
            studentId === api.getStudentId();
    };

    const request = async function (action, values) {
        const params = new URLSearchParams();
        const options = {
            credentials: 'same-origin'
        };
        let response;
        let result;

        params.set('action', action);

        Object.keys(values || {}).forEach(function (key) {
            params.set(key, values[key]);
        });

        if (action === 'list') {
            response = await fetch(
                endpoint + '?' + params.toString(),
                options
            );
        } else {
            params.set('sesskey', sesskey);
            options.method = 'POST';
            options.headers = {
                'Content-Type':
                    'application/x-www-form-urlencoded;charset=UTF-8'
            };
            options.body = params.toString();
            response = await fetch(endpoint, options);
        }

        try {
            result = await response.json();
        } catch (error) {
            throw new Error(
                'Sunucudan geçerli bir yanıt alınamadı.'
            );
        }

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'İşlem tamamlanamadı.'
            );
        }

        return result;
    };

    const load = async function () {
        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        const version = ++requestVersion;

        loading.hidden = false;
        list.innerHTML = '';
        empty.hidden = true;
        setStatus('', false);

        try {
            const result = await request('list', {
                studentid: studentId
            });

            if (isCurrentRequest(version, studentId)) {
                render(result.data || {});
            }
        } catch (error) {
            if (isCurrentRequest(version, studentId)) {
                setStatus(error.message, true);
            }
        } finally {
            if (isCurrentRequest(version, studentId)) {
                loading.hidden = true;
            }
        }
    };

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        const version = ++requestVersion;
        const values = new FormData(form);
        const button = form.querySelector('button[type="submit"]');

        if (button) {
            button.disabled = true;
        }

        setStatus('Kaydediliyor...', false);

        try {
            const result = await request('add', {
                studentid: studentId,
                title: values.get('title') || '',
                duedate: values.get('duedate') || '',
                description: values.get('description') || ''
            });

            if (!isCurrentRequest(version, studentId)) {
                return;
            }

            form.reset();
            render(result.data || {});
            setStatus('Ödev kaydedildi.', false);
        } catch (error) {
            if (isCurrentRequest(version, studentId)) {
                setStatus(error.message, true);
            }
        } finally {
            if (button && isCurrentRequest(version, studentId)) {
                button.disabled = false;
            }
        }
    });

    document.addEventListener('click', async function (event) {
        const toggle = event.target.closest('[data-homework-status]');
        const remove = event.target.closest(
            '[data-delete-student-homework]'
        );

        if (!toggle && !remove) {
            return;
        }

        const button = toggle || remove;

        if (!panel.contains(button)) {
            return;
        }

        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        if (remove && !confirm('Bu ödev kaydı silinsin mi?')) {
            return;
        }

        const version = ++requestVersion;
        const action = toggle ? 'status' : 'delete';
        const homeworkId = toggle
            ? toggle.dataset.homeworkStatus
            : remove.dataset.deleteStudentHomework;

        button.disabled = true;
        setStatus(toggle ? 'Güncelleniyor...' : 'Siliniyor...', false);

        try {
            const values = {
                studentid: studentId,
                homeworkid: homeworkId
            };

            if (toggle) {
                values.status = toggle.dataset.nextStatus;
            }

            const result = await request(action, values);

            if (!isCurrentRequest(version, studentId)) {
                return;
            }

            render(result.data || {});
            setStatus(
                toggle ? 'Ödev durumu güncellendi.' : 'Ödev silindi.',
                false
            );
        } catch (error) {
            if (isCurrentRequest(version, studentId)) {
                button.disabled = false;
                setStatus(error.message, true);
            }
        }
    });

    document.addEventListener(
        'qubexa:student-tab-changed',
        function (event) {
            if (event.detail && event.detail.tab === 'homeworks') {
                load();
            }
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            requestVersion += 1;
            form.reset();
            render({});
            empty.hidden = true;
            loading.hidden = true;
            setStatus('', false);
        }
    );
});
