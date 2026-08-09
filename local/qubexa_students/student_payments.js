document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const endpoint = panel.dataset.paymentsEndpoint || '';
    const sesskey = panel.dataset.sesskey || '';
    const form = panel.querySelector('[data-student-payment-form]');
    const status = panel.querySelector('[data-student-payment-status]');
    const loading = panel.querySelector(
        '[data-student-payments-loading]'
    );
    const empty = panel.querySelector('[data-student-payments-empty]');
    const list = panel.querySelector('[data-student-payments-list]');

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

    const query = function (selector) {
        return panel.querySelector(selector);
    };
    const summary = {
        paid: query('[data-payment-paid]'),
        pending: query('[data-payment-pending]'),
        overdue: query('[data-payment-overdue]'),
        receivable: query('[data-payment-receivable]')
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
                summary[key].textContent = values[key] || '0,00 ₺';
            }
        });

        items.forEach(function (payment) {
            const article = document.createElement('article');
            const header = document.createElement('div');
            const details = document.createElement('div');
            const date = document.createElement('time');
            const amount = document.createElement('h4');
            const method = document.createElement('span');
            const actions = document.createElement('div');
            const badge = document.createElement('span');
            const remove = document.createElement('button');

            article.className = 'qubexa-payment-item';
            header.className = 'qubexa-payment-item-header';
            method.className = 'qubexa-payment-method';
            actions.className = 'qubexa-payment-actions';
            badge.className =
                'qubexa-payment-status is-' +
                (payment.status || 'paid');

            date.textContent = payment.paymentdate || '';
            amount.textContent = payment.amountlabel || '';
            method.textContent = payment.methodlabel || '';
            badge.textContent = payment.statuslabel || '';

            remove.type = 'button';
            remove.className = 'qubexa-module-delete';
            remove.dataset.deleteStudentPayment = String(payment.id);
            remove.textContent = 'Sil';
            remove.setAttribute(
                'aria-label',
                (payment.amountlabel || 'Ödeme') + ' kaydını sil'
            );

            details.append(date, amount, method);
            actions.append(badge, remove);
            header.append(details, actions);
            article.append(header);

            if (payment.description) {
                const description = document.createElement('p');

                description.textContent = payment.description;
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
        list.innerHTML = '';
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
                    result.message || 'Ödemeler yüklenemedi.'
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
                paymentdate: values.get('paymentdate') || '',
                amount: values.get('amount') || '0',
                status: values.get('status') || 'paid',
                method: values.get('method') || 'cash',
                description: values.get('description') || ''
            });

            if (!isCurrentRequest(version, studentId)) {
                return;
            }

            if (!result.success) {
                throw new Error(
                    result.message || 'Ödeme kaydedilemedi.'
                );
            }

            form.reset();
            render(result.data || {});
            setStatus('Ödeme kaydedildi.', false);
        } catch (error) {
            if (isCurrentRequest(version, studentId)) {
                setStatus(error.message, true);
            }
        } finally {
            if (
                button &&
                isCurrentRequest(version, studentId)
            ) {
                button.disabled = false;
            }
        }
    });

    document.addEventListener('click', async function (event) {
        const button = event.target.closest(
            '[data-delete-student-payment]'
        );

        if (!button || !panel.contains(button)) {
            return;
        }

        const studentId = api.getStudentId();

        if (!studentId || !confirm('Bu ödeme kaydı silinsin mi?')) {
            return;
        }

        const version = ++requestVersion;

        button.disabled = true;
        setStatus('Siliniyor...', false);

        try {
            const result = await request('delete', {
                studentid: studentId,
                paymentid: button.dataset.deleteStudentPayment
            });

            if (!isCurrentRequest(version, studentId)) {
                return;
            }

            if (!result.success) {
                throw new Error(
                    result.message || 'Ödeme silinemedi.'
                );
            }

            render(result.data || {});
            setStatus('Ödeme silindi.', false);
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
            if (event.detail && event.detail.tab === 'payments') {
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
