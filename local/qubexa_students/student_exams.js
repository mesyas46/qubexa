document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const endpoint = panel.dataset.examsEndpoint || '';
    const sesskey = panel.dataset.sesskey || '';

    const form = panel.querySelector(
        '[data-student-exam-form]'
    );
    const status = panel.querySelector(
        '[data-student-exam-status]'
    );
    const loading = panel.querySelector(
        '[data-student-exams-loading]'
    );
    const empty = panel.querySelector(
        '[data-student-exams-empty]'
    );
    const list = panel.querySelector(
        '[data-student-exams-list]'
    );
    const average = panel.querySelector(
        '[data-exam-average]'
    );
    const highest = panel.querySelector(
        '[data-exam-highest]'
    );
    const latest = panel.querySelector(
        '[data-exam-latest]'
    );

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

    const setStatus = function (message, error) {
        status.textContent = message || '';
        status.classList.toggle(
            'is-error',
            Boolean(error)
        );
    };

    const render = function (data) {
        const items = data.items || [];
        const summary = data.summary || {};

        list.innerHTML = '';
        empty.hidden = items.length > 0;

        if (average) {
            average.textContent = summary.average || '0,00';
        }

        if (highest) {
            highest.textContent = summary.highest || '0,00';
        }

        if (latest) {
            latest.textContent = summary.latest || '0,00';
        }

        items.forEach(function (exam) {
            const article = document.createElement('article');
            article.className = 'qubexa-exam-item';

            const header = document.createElement('div');
            header.className = 'qubexa-exam-item-header';

            const titleArea = document.createElement('div');

            const time = document.createElement('time');
            time.textContent = exam.examdate || '';

            const title = document.createElement('h4');
            title.textContent = exam.examname || '';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'qubexa-module-delete';
            remove.dataset.deleteStudentExam = String(exam.id);
            remove.textContent = 'Sil';

            titleArea.appendChild(time);
            titleArea.appendChild(title);
            header.appendChild(titleArea);
            header.appendChild(remove);

            const result = document.createElement('div');
            result.className = 'qubexa-exam-result-grid';

            [
                ['Doğru', exam.correct, false],
                ['Yanlış', exam.wrong, false],
                ['Boş', exam.blank, false],
                ['Net', exam.net, true]
            ].forEach(function (item) {
                const box = document.createElement('span');

                if (item[2]) {
                    box.classList.add('is-net');
                }

                const label = document.createTextNode(
                    item[0] + ' '
                );
                const strong = document.createElement('strong');
                strong.textContent = item[1];

                box.appendChild(label);
                box.appendChild(strong);
                result.appendChild(box);
            });

            article.appendChild(header);
            article.appendChild(result);

            if (exam.description) {
                const description = document.createElement('p');
                description.className =
                    'qubexa-exam-description-text';
                description.textContent = exam.description;
                article.appendChild(description);
            }

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
                    result.message ||
                    'Sınav sonuçları yüklenemedi.'
                );
            }

            render(result.data || {});
        } catch (error) {
            setStatus(error.message, true);
        } finally {
            loading.hidden = true;
        }
    };

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        const values = new FormData(form);
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
                examname: values.get('examname') || '',
                examdate: values.get('examdate') || '',
                correct: values.get('correct') || '0',
                wrong: values.get('wrong') || '0',
                blank: values.get('blank') || '0',
                description: values.get('description') || ''
            });

            if (!result.success) {
                throw new Error(
                    result.message ||
                    'Sınav sonucu kaydedilemedi.'
                );
            }

            form.reset();
            render(result.data || {});
            setStatus(
                'Sınav sonucu kaydedildi.',
                false
            );
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
            '[data-delete-student-exam]'
        );

        if (!button || !panel.contains(button)) {
            return;
        }

        if (!window.confirm('Bu sınav sonucu silinsin mi?')) {
            return;
        }

        button.disabled = true;
        setStatus('Siliniyor...', false);

        try {
            const result = await request('delete', {
                studentid: api.getStudentId(),
                examid: button.dataset.deleteStudentExam
            });

            if (!result.success) {
                throw new Error(
                    result.message ||
                    'Sınav sonucu silinemedi.'
                );
            }

            render(result.data || {});
            setStatus(
                'Sınav sonucu silindi.',
                false
            );
        } catch (error) {
            button.disabled = false;
            setStatus(error.message, true);
        }
    });

    document.addEventListener(
        'qubexa:student-tab-changed',
        function (event) {
            if (event.detail.tab === 'exams') {
                load();
            }
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            form.reset();
            setStatus('', false);
        }
    );
});
