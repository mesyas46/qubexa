document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const endpoint = panel.dataset.notesEndpoint || '';
    const sesskey = panel.dataset.sesskey || '';

    const form = panel.querySelector(
        '[data-student-note-form]'
    );
    const input = panel.querySelector(
        '[data-student-note-input]'
    );
    const status = panel.querySelector(
        '[data-student-note-status]'
    );
    const loading = panel.querySelector(
        '[data-student-notes-loading]'
    );
    const empty = panel.querySelector(
        '[data-student-notes-empty]'
    );
    const list = panel.querySelector(
        '[data-student-notes-list]'
    );

    if (
        !endpoint ||
        !form ||
        !input ||
        !status ||
        !loading ||
        !empty ||
        !list
    ) {
        return;
    }

    const setStatus = function (message, error) {
        status.textContent = message || '';
        status.classList.toggle(
            'is-error',
            Boolean(error)
        );
    };

    const render = function (notes) {
        list.innerHTML = '';
        empty.hidden = notes.length > 0;

        notes.forEach(function (note) {
            const article = document.createElement('article');
            article.className = 'qubexa-note-item';

            const meta = document.createElement('div');
            meta.className = 'qubexa-note-meta';

            const time = document.createElement('time');
            time.textContent = note.date || '';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'qubexa-module-delete';
            remove.dataset.deleteStudentNote = String(note.id);
            remove.textContent = 'Sil';

            const text = document.createElement('p');
            text.textContent = note.note || '';

            meta.appendChild(time);
            meta.appendChild(remove);
            article.appendChild(meta);
            article.appendChild(text);
            list.appendChild(article);
        });
    };

    const request = async function (action, values) {
        const params = new URLSearchParams();
        params.set('action', action);

        Object.keys(values || {}).forEach(function (key) {
            params.set(key, values[key]);
        });

        const options = {
            credentials: 'same-origin'
        };

        if (action === 'list') {
            const response = await fetch(
                endpoint + '?' + params.toString(),
                options
            );

            return response.json();
        }

        params.set('sesskey', sesskey);
        options.method = 'POST';
        options.headers = {
            'Content-Type':
                'application/x-www-form-urlencoded;charset=UTF-8'
        };
        options.body = params.toString();

        const response = await fetch(endpoint, options);
        return response.json();
    };

    const load = async function () {
        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        loading.hidden = false;
        setStatus('', false);

        try {
            const result = await request('list', {
                studentid: studentId
            });

            if (!result.success) {
                throw new Error(
                    result.message || 'Notlar yüklenemedi.'
                );
            }

            render(result.notes || []);
        } catch (error) {
            setStatus(error.message, true);
        } finally {
            loading.hidden = true;
        }
    };

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const studentId = api.getStudentId();
        const note = input.value.trim();

        if (!studentId) {
            return;
        }

        if (!note) {
            setStatus('Lütfen bir not yazın.', true);
            return;
        }

        const button = form.querySelector(
            'button[type="submit"]'
        );

        if (button) {
            button.disabled = true;
        }

        setStatus('Kaydediliyor...', false);

        try {
            const result = await request('add', {
                studentid: studentId,
                note: note
            });

            if (!result.success) {
                throw new Error(
                    result.message || 'Not kaydedilemedi.'
                );
            }

            input.value = '';
            render(result.notes || []);
            setStatus('Not kaydedildi.', false);
        } catch (error) {
            setStatus(error.message, true);
        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    });

    document.addEventListener('click', async function (event) {
        const button = event.target.closest(
            '[data-delete-student-note]'
        );

        if (!button || !panel.contains(button)) {
            return;
        }

        if (!window.confirm('Bu not silinsin mi?')) {
            return;
        }

        button.disabled = true;
        setStatus('Siliniyor...', false);

        try {
            const result = await request('delete', {
                studentid: api.getStudentId(),
                noteid: button.dataset.deleteStudentNote
            });

            if (!result.success) {
                throw new Error(
                    result.message || 'Not silinemedi.'
                );
            }

            render(result.notes || []);
            setStatus('Not silindi.', false);
        } catch (error) {
            button.disabled = false;
            setStatus(error.message, true);
        }
    });

    document.addEventListener(
        'qubexa:student-tab-changed',
        function (event) {
            if (event.detail.tab === 'notes') {
                load();
            }
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            input.value = '';
            setStatus('', false);
        }
    );
});
