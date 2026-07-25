document.addEventListener('DOMContentLoaded', function () {
    const api = window.QubexaStudentPanel;

    if (!api) {
        return;
    }

    const panel = api.panel;
    const examsEndpoint = panel.dataset.examsEndpoint || '';
    const lessonsEndpoint = panel.dataset.lessonsEndpoint || '';

    const loading = panel.querySelector(
        '[data-student-progress-loading]'
    );
    const status = panel.querySelector(
        '[data-student-progress-status]'
    );
    const averageNet = panel.querySelector(
        '[data-progress-average-net]'
    );
    const attendance = panel.querySelector(
        '[data-progress-attendance]'
    );
    const totalLessons = panel.querySelector(
        '[data-progress-total-lessons]'
    );
    const examCount = panel.querySelector(
        '[data-progress-exam-count]'
    );
    const chart = panel.querySelector(
        '[data-progress-chart]'
    );
    const trendTitle = panel.querySelector(
        '[data-progress-trend-title]'
    );
    const trendText = panel.querySelector(
        '[data-progress-trend-text]'
    );

    if (
        !examsEndpoint ||
        !lessonsEndpoint ||
        !loading ||
        !status ||
        !chart
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

    const request = async function (endpoint, studentId) {
        const params = new URLSearchParams();
        params.set('action', 'list');
        params.set('studentid', String(studentId));

        const response = await fetch(
            endpoint + '?' + params.toString(),
            {
                credentials: 'same-origin'
            }
        );

        const text = await response.text();

        try {
            return JSON.parse(text);
        } catch (error) {
            throw new Error(
                'İlerleme verileri okunamadı.'
            );
        }
    };

    const parseNet = function (value) {
        return Number(
            String(value || '0')
                .replace(/\./g, '')
                .replace(',', '.')
        ) || 0;
    };

    const renderChart = function (items) {
        chart.innerHTML = '';

        const chronological = items
            .slice()
            .reverse()
            .slice(-8);

        if (!chronological.length) {
            const empty = document.createElement('div');
            empty.className = 'qubexa-progress-chart-empty';
            empty.textContent =
                'Grafik için en az bir sınav sonucu gereklidir.';
            chart.appendChild(empty);
            return;
        }

        const values = chronological.map(function (item) {
            return parseNet(item.net);
        });

        const max = Math.max.apply(null, values.concat([1]));
        const min = Math.min.apply(null, values.concat([0]));
        const range = Math.max(max - min, 1);
        const width = 420;
        const height = 180;
        const padding = 24;

        const svg = document.createElementNS(
            'http://www.w3.org/2000/svg',
            'svg'
        );

        svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', 'Sınav net gelişim grafiği');

        const points = values.map(function (value, index) {
            const usableWidth = width - (padding * 2);
            const usableHeight = height - (padding * 2);
            const x = padding + (
                chronological.length === 1
                    ? usableWidth / 2
                    : (index / (chronological.length - 1)) *
                        usableWidth
            );
            const y = height - padding -
                (((value - min) / range) * usableHeight);

            return {
                x: x,
                y: y,
                value: value,
                label: chronological[index].examname || ''
            };
        });

        const polyline = document.createElementNS(
            'http://www.w3.org/2000/svg',
            'polyline'
        );

        polyline.setAttribute(
            'points',
            points.map(function (point) {
                return point.x + ',' + point.y;
            }).join(' ')
        );
        polyline.setAttribute('fill', 'none');
        polyline.setAttribute('stroke', 'currentColor');
        polyline.setAttribute('stroke-width', '3');
        polyline.setAttribute('stroke-linecap', 'round');
        polyline.setAttribute('stroke-linejoin', 'round');
        polyline.classList.add('qubexa-progress-line');

        svg.appendChild(polyline);

        points.forEach(function (point) {
            const circle = document.createElementNS(
                'http://www.w3.org/2000/svg',
                'circle'
            );

            circle.setAttribute('cx', String(point.x));
            circle.setAttribute('cy', String(point.y));
            circle.setAttribute('r', '5');
            circle.classList.add('qubexa-progress-point');

            const title = document.createElementNS(
                'http://www.w3.org/2000/svg',
                'title'
            );
            title.textContent =
                point.label + ': ' +
                point.value.toFixed(2) + ' net';

            circle.appendChild(title);
            svg.appendChild(circle);
        });

        chart.appendChild(svg);

        const labels = document.createElement('div');
        labels.className = 'qubexa-progress-chart-labels';

        chronological.forEach(function (item) {
            const label = document.createElement('span');
            label.textContent = item.examname || 'Sınav';
            labels.appendChild(label);
        });

        chart.appendChild(labels);
    };

    const renderTrend = function (items) {
        if (!trendTitle || !trendText) {
            return;
        }

        if (items.length < 2) {
            trendTitle.textContent = 'Daha fazla sınav sonucu gerekli';
            trendText.textContent =
                'En az iki sınav sonucu eklendiğinde net değişimi yorumlanacaktır.';
            return;
        }

        const latest = parseNet(items[0].net);
        const previous = parseNet(items[1].net);
        const difference = latest - previous;

        if (difference > 0.01) {
            trendTitle.textContent = 'Net yükselişi var';
            trendText.textContent =
                'Son sınav neti bir önceki sınava göre ' +
                difference.toFixed(2).replace('.', ',') +
                ' net arttı.';
            return;
        }

        if (difference < -0.01) {
            trendTitle.textContent = 'Net düşüşü gözlendi';
            trendText.textContent =
                'Son sınav neti bir önceki sınava göre ' +
                Math.abs(difference).toFixed(2).replace('.', ',') +
                ' net azaldı.';
            return;
        }

        trendTitle.textContent = 'Net seviyesi dengeli';
        trendText.textContent =
            'Son iki sınav sonucu arasında belirgin bir değişim yok.';
    };

    const load = async function () {
        const studentId = api.getStudentId();

        if (!studentId) {
            return;
        }

        loading.hidden = false;
        setStatus('', false);

        try {
            const results = await Promise.all([
                request(examsEndpoint, studentId),
                request(lessonsEndpoint, studentId)
            ]);

            const examsResult = results[0];
            const lessonsResult = results[1];

            if (!examsResult.success) {
                throw new Error(
                    examsResult.message ||
                    'Sınav verileri yüklenemedi.'
                );
            }

            if (!lessonsResult.success) {
                throw new Error(
                    lessonsResult.message ||
                    'Ders verileri yüklenemedi.'
                );
            }

            const examsData = examsResult.data || {};
            const lessonsData = lessonsResult.data || {};
            const examItems = examsData.items || [];
            const examSummary = examsData.summary || {};
            const lessonSummary = lessonsData.summary || {};

            if (averageNet) {
                averageNet.textContent =
                    examSummary.average || '0,00';
            }

            if (attendance) {
                attendance.textContent =
                    lessonSummary.attendance || '0%';
            }

            if (totalLessons) {
                totalLessons.textContent =
                    lessonSummary.total ?? 0;
            }

            if (examCount) {
                examCount.textContent =
                    examSummary.count ?? examItems.length;
            }

            renderChart(examItems);
            renderTrend(examItems);
        } catch (error) {
            setStatus(error.message, true);
        } finally {
            loading.hidden = true;
        }
    };

    document.addEventListener(
        'qubexa:student-tab-changed',
        function (event) {
            if (event.detail.tab === 'progress') {
                load();
            }
        }
    );

    document.addEventListener(
        'qubexa:student-panel-closed',
        function () {
            setStatus('', false);
        }
    );
});
