document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const endpoint = panel.dataset.lessonsEndpoint || '';
    const sesskey = panel.dataset.sesskey || '';

    const form = panel.querySelector(
        '[data-student-lesson-form]'
    );
    const status = panel.querySelector(
        '[data-student-lesson-status]'
    );
    const loading = panel.querySelector(
        '[data-student-lessons-loading]'
    );
    const empty = panel.querySelector(
        '[data-student-lessons-empty]'
    );
    const list = panel.querySelector(
        '[data-student-lessons-list]'
    );

    const total = panel.querySelector(
        '[data-lesson-total]'
    );
    const attendance = panel.querySelector(
        '[data-lesson-attendance]'
    );
    const absent = panel.querySelector(
        '[data-lesson-absent]'
    );
    const thismonth = panel.querySelector(
        '[data-lesson-thismonth]'
    );
    const latest = panel.querySelector(
        '[data-lesson-latest]'
    );

    const homeworkToggle = panel.querySelector(
        '[data-homework-toggle]'
    );
    const homeworkWrap = panel.querySelector(
        '[data-homework-note-wrap]'
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

    const updateHomeworkVisibility = function () {
        if (!homeworkToggle || !homeworkWrap) {
            return;
        }

        homeworkWrap.hidden = !homeworkToggle.checked;
    };

    const createDetail = function (
        icon,
        label,
        value,
        modifier
    ) {
        if (!value) {
            return null;
        }

        const row = document.createElement('div');
        row.className = 'qubexa-lesson-detail';

        if (modifier) {
            row.classList.add(modifier);
        }

        const iconElement = document.createElement('span');
        iconElement.className = 'qubexa-lesson-detail-icon';
        iconElement.textContent = icon;

        const content = document.createElement('div');

        const strong = document.createElement('strong');
        strong.textContent = label;

        const text = document.createElement('p');
        text.textContent = value;

        content.appendChild(strong);
        content.appendChild(text);
        row.appendChild(iconElement);
        row.appendChild(content);

        return row;
    };

    const render = function (data) {
        const items = data.items || [];
        const summary = data.summary || {};

        list.innerHTML = '';
        empty.hidden = items.length > 0;

        if (total) {
            total.textContent = summary.total ?? 0;
        }

        if (attendance) {
            attendance.textContent =
                summary.attendance || '0%';
        }

        if (absent) {
            absent.textContent = summary.absent ?? 0;
        }

        if (thismonth) {
            thismonth.textContent =
                summary.thismonth ?? 0;
        }

        if (latest) {
            latest.textContent = summary.latest || '—';
        }

        items.forEach(function (lesson) {
            const article = document.createElement('article');
            article.className = 'qubexa-lesson-item';

            const header = document.createElement('div');
            header.className = 'qubexa-lesson-item-header';

            const titleArea = document.createElement('div');

            const date = document.createElement('time');
            date.className = 'qubexa-lesson-date';
            date.textContent =
                '📅 ' + (lesson.lessondate || '');

            const title = document.createElement('h4');
            title.className = 'qubexa-lesson-topic';
            title.textContent =
                '📚 ' + (lesson.topic || '');

            const meta = document.createElement('div');
            meta.className = 'qubexa-lesson-meta';

            const timeText = document.createElement('span');
            timeText.textContent =
                '🕒 ' +
                (lesson.starttime || '') +
                ' – ' +
                (lesson.endtime || '');

            const durationText = document.createElement('span');
            durationText.textContent =
                '⏱ ' + (lesson.durationlabel || '');

            meta.appendChild(timeText);
            meta.appendChild(durationText);

            titleArea.appendChild(date);
            titleArea.appendChild(title);
            titleArea.appendChild(meta);

            const actions = document.createElement('div');
            actions.className = 'qubexa-lesson-actions';

            const badge = document.createElement('span');
            badge.className =
                'qubexa-lesson-status is-' +
                (lesson.status || 'attended');
            badge.textContent = lesson.statuslabel || '';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'qubexa-module-delete';
            remove.dataset.deleteStudentLesson =
                String(lesson.id);
            remove.textContent = 'Sil';

            actions.appendChild(badge);
            actions.appendChild(remove);

            header.appendChild(titleArea);
            header.appendChild(actions);
            article.appendChild(header);

            const details = document.createElement('div');
            details.className = 'qubexa-lesson-details';

            if (lesson.homeworkgiven) {
                const homework = createDetail(
                    '📖',
                    'Verilen Ödev',
                    lesson.homeworknote ||
                    'Ödev verildi.',
                    'is-homework'
                );

                if (homework) {
                    details.appendChild(homework);
                }
            }

            [
                createDetail(
                    '📝',
                    'Öğretmen Notu',
                    lesson.teachernote,
                    'is-note'
                ),
                createDetail(
                    '➡',
                    'Bir Sonraki Ders',
                    lesson.nextlesson,
                    'is-next'
                )
            ].forEach(function (detail) {
                if (detail) {
                    details.appendChild(detail);
                }
            });

            if (details.children.length > 0) {
                article.appendChild(details);
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
                    'Ders geçmişi yüklenemedi.'
                );
            }

            render(result.data || {});
        } catch (error) {
            setStatus(error.message, true);
        } finally {
            loading.hidden = true;
        }
    };

    if (homeworkToggle) {
        homeworkToggle.addEventListener(
            'change',
            updateHomeworkVisibility
        );
    }

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
                lessondate: values.get('lessondate') || '',
                starttime: values.get('starttime') || '',
                endtime: values.get('endtime') || '',
                topic: values.get('topic') || '',
                status: values.get('status') || 'attended',
                homeworkgiven:
                    values.get('homeworkgiven') ? '1' : '0',
                homeworknote:
                    values.get('homeworknote') || '',
                teachernote:
                    values.get('teachernote') || '',
                nextlesson:
                    values.get('nextlesson') || ''
            });

            if (!result.success) {
                throw new Error(
                    result.message ||
                    'Ders kaydı oluşturulamadı.'
                );
            }

            form.reset();
            updateHomeworkVisibility();
            render(result.data || {});
            setStatus('Ders kaydedildi.', false);
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
            '[data-delete-student-lesson]'
        );

        if (!button || !panel.contains(button)) {
            return;
        }

        if (!window.confirm('Bu ders kaydı silinsin mi?')) {
            return;
        }

        button.disabled = true;
        setStatus('Siliniyor...', false);

        try {
            const result = await request('delete', {
                studentid: api.getStudentId(),
                lessonid: button.dataset.deleteStudentLesson
            });

            if (!result.success) {
                throw new Error(
                    result.message ||
                    'Ders kaydı silinemedi.'
                );
            }

            render(result.data || {});
            setStatus('Ders kaydı silindi.', false);
        } catch (error) {
            button.disabled = false;
            setStatus(error.message, true);
        }
    });

    document.addEventListener(
        'qubexa:student-tab-changed',
        function (event) {
            if (event.detail.tab === 'lessons') {
                load();
            }
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            form.reset();
            updateHomeworkVisibility();
            setStatus('', false);
        }
    );

    updateHomeworkVisibility();
});
