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

    let requestVersion = 0;

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
            remove.setAttribute(
                'aria-label',
                (lesson.topic || 'Ders') +
                    ' ders kaydını sil'
            );

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

    const isCurrentRequest = function (version, studentId) {
        return version === requestVersion &&
            studentId === api.getStudentId();
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

        let response;

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

        let result;

        try {
            result = await response.json();
        } catch (error) {
            throw new Error(
                'Sunucudan geçerli bir yanıt alınamadı.'
            );
        }

        if (!response.ok) {
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
        empty.hidden = true;
        list.innerHTML = '';
        list.setAttribute('aria-busy', 'true');
        render({});
        empty.hidden = true;
        setStatus('', false);

        try {
            const result = await request('list', {
                studentid: studentId
            });

            if (!isCurrentRequest(version, studentId)) {
                return;
            }

            if (!result.success) {
                throw new Error(
                    result.message ||
                    'Ders geçmişi yüklenemedi.'
                );
            }

            render(result.data || {});
        } catch (error) {
            if (isCurrentRequest(version, studentId)) {
                setStatus(error.message, true);
            }
        } finally {
            if (isCurrentRequest(version, studentId)) {
                loading.hidden = true;
                list.setAttribute('aria-busy', 'false');
            }
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

        const version = ++requestVersion;
        const values = new FormData(form);
        const button = form.querySelector(
            'button[type="submit"]'
        );

        if (button) {
            button.disabled = true;
            button.dataset.lessonRequestVersion =
                String(version);
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

            if (!isCurrentRequest(version, studentId)) {
                return;
            }

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
            if (isCurrentRequest(version, studentId)) {
                setStatus(error.message, true);
            }
        } finally {
            if (
                button &&
                button.dataset.lessonRequestVersion ===
                    String(version)
            ) {
                button.disabled = false;
                delete button.dataset.lessonRequestVersion;
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

        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        const version = ++requestVersion;

        button.disabled = true;
        setStatus('Siliniyor...', false);

        try {
            const result = await request('delete', {
                studentid: studentId,
                lessonid: button.dataset.deleteStudentLesson
            });

            if (!isCurrentRequest(version, studentId)) {
                return;
            }

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

            if (isCurrentRequest(version, studentId)) {
                setStatus(error.message, true);
            }
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
            requestVersion += 1;
            form.reset();

            const submit = form.querySelector(
                'button[type="submit"]'
            );

            if (submit) {
                submit.disabled = false;
                delete submit.dataset.lessonRequestVersion;
            }

            updateHomeworkVisibility();
            render({});
            list.setAttribute('aria-busy', 'false');
            empty.hidden = true;
            loading.hidden = true;
            setStatus('', false);
        }
    );

    updateHomeworkVisibility();
});
