<?php
namespace local_qubexa_calendar\output;

defined('MOODLE_INTERNAL') || die();

use moodle_url;

final class calendar_page {
    private int $userid;
    private int $viewdate;

    public function __construct(
        int $userid,
        int $viewdate
    ) {
        $this->userid = $userid;
        $this->viewdate = $viewdate;
    }

    public function export_for_template(): array {
        global $DB;

        $timezone = \core_date::get_user_timezone_object();

        $selecteddate = (
            new \DateTimeImmutable('@' . $this->viewdate)
        )->setTimezone($timezone);

        $monthstart = $selecteddate
            ->modify('first day of this month')
            ->setTime(0, 0);

        $weekdayoffset =
            (int) $monthstart->format('N') - 1;

        $gridstart = $monthstart->modify(
            '-' . $weekdayoffset . ' days'
        );

        $gridend = $gridstart->modify('+42 days');

        $today = (
            new \DateTimeImmutable('now', $timezone)
        )->setTime(0, 0);

        $calendarevents = $DB->get_records_select(
            'local_qubexa_cal_events',
            'userid = :userid
                AND timestart < :gridend
                AND (
                    timestart >= :gridstartevent
                    OR (
                        recurrence <> :none
                        AND (
                            recurrenceuntil IS NULL
                            OR recurrenceuntil >= :gridstartuntil
                        )
                    )
                )',
            [
                'userid' => $this->userid,
                'gridstartevent' =>
    $gridstart->getTimestamp(),

'gridstartuntil' =>
    $gridstart->getTimestamp(),
                'gridend' => $gridend->getTimestamp(),
                'none' => 'none',
            ],
            'timestart ASC'
        );
$studentids = [];

foreach ($calendarevents as $calendarevent) {
    if (!empty($calendarevent->studentid)) {
        $studentids[(int) $calendarevent->studentid] =
            (int) $calendarevent->studentid;
    }
}

$studentnames = [];

if ($studentids) {
    [$studentsql, $studentparams] =
        $DB->get_in_or_equal(
            array_values($studentids),
            SQL_PARAMS_NAMED,
            'calendarstudent'
        );

    $studentparams['calendaruserid'] =
        $this->userid;

    $studentrecords = $DB->get_records_select(
        'local_qubexa_students',
        'userid = :calendaruserid
            AND id ' . $studentsql,
        $studentparams,
        'firstname ASC, lastname ASC',
        'id, firstname, lastname'
    );

    foreach ($studentrecords as $student) {
        $studentnames[$student->id] =
            format_string(
                trim(
                    $student->firstname .
                    ' ' .
                    $student->lastname
                )
            );
    }
}
        $eventsbydate = [];

        $addoccurrence = static function (
            \stdClass $calendarevent,
            \DateTimeImmutable $eventdate
        ) use (
    &$eventsbydate,
    $studentnames
): void {
            $datekey = $eventdate->format('Y-m-d');

            $statusclass = in_array(
                $calendarevent->status,
                ['planned', 'completed', 'cancelled'],
                true
            )
                ? 'is-' . $calendarevent->status
                : 'is-planned';
				$duration = max(
    0,
    (int) $calendarevent->timeend -
    (int) $calendarevent->timestart
);

$eventend = $eventdate->setTimestamp(
    $eventdate->getTimestamp() + $duration
);
$studentname = null;

if (
    !empty($calendarevent->studentid) &&
    isset(
        $studentnames[
            $calendarevent->studentid
        ]
    )
) {
    $studentname =
        $studentnames[
            $calendarevent->studentid
        ];
}
            $eventsbydate[$datekey][] = [
                'title' => format_string(
                    $calendarevent->title
                ),
                'time' => $eventdate->format('H:i'),
				'endtime' => $eventend->format('H:i'),
				'hasstudent' => $studentname !== null,
'studentname' => $studentname,
                'statusclass' => $statusclass,
                'isrecurring' =>
                    $calendarevent->recurrence !== 'none',
                'editurl' => (
                    new moodle_url(
                        '/local/qubexa_calendar/edit.php',
                        ['id' => $calendarevent->id]
                    )
                )->out(false),
            ];
        };

        foreach ($calendarevents as $calendarevent) {
    $eventdate = (
        new \DateTimeImmutable(
            '@' . $calendarevent->timestart
        )
    )->setTimezone($timezone);

    $recurrence = in_array(
        $calendarevent->recurrence,
        ['weekly', 'monthly'],
        true
    )
        ? $calendarevent->recurrence
        : 'none';

    if ($recurrence === 'none') {
        if (
            $eventdate >= $gridstart &&
            $eventdate < $gridend
        ) {
            $addoccurrence(
                $calendarevent,
                $eventdate
            );
        }

        continue;
    }

    $recurrenceuntil = null;

    if (!empty($calendarevent->recurrenceuntil)) {
        $recurrenceuntil = (
            new \DateTimeImmutable(
                '@' . $calendarevent->recurrenceuntil
            )
        )
            ->setTimezone($timezone)
            ->setTime(23, 59, 59);
    }

    $cursor = $eventdate;
    $originalday = (int) $eventdate->format('j');

    $advance = static function (
        \DateTimeImmutable $date
    ) use (
        $recurrence,
        $originalday
    ): \DateTimeImmutable {
        if ($recurrence === 'weekly') {
            return $date->modify('+7 days');
        }

        $nextmonth = $date->modify(
            'first day of next month'
        );

        $targetday = min(
            $originalday,
            (int) $nextmonth->format('t')
        );

        return $nextmonth->setDate(
            (int) $nextmonth->format('Y'),
            (int) $nextmonth->format('m'),
            $targetday
        );
    };

    $iterations = 0;

    while (
        $cursor < $gridstart &&
        $iterations < 5000
    ) {
        $cursor = $advance($cursor);
        $iterations++;
    }

    while (
        $cursor < $gridend &&
        (
            !$recurrenceuntil ||
            $cursor <= $recurrenceuntil
        ) &&
        $iterations < 5050
    ) {
        $addoccurrence(
            $calendarevent,
            $cursor
        );

        $cursor = $advance($cursor);
        $iterations++;
    }
}

        $weekdays = [];

        for ($index = 0; $index < 7; $index++) {
            $weekday = $gridstart->modify(
                '+' . $index . ' days'
            );

            $weekdays[] = [
                'label' => userdate(
                    $weekday->getTimestamp(),
                    '%a'
                ),
            ];
        }

        $days = [];

        for ($index = 0; $index < 42; $index++) {
            $day = $gridstart->modify(
                '+' . $index . ' days'
            );

            $datekey = $day->format('Y-m-d');
            $dayevents = $eventsbydate[$datekey] ?? [];

            $days[] = [
                'date' => $datekey,
				'dayurl' => (
    new moodle_url(
        '/local/qubexa/index.php',
        [
            'page' => 'calendar',
            'viewdate' => $day->getTimestamp(),
        ]
    )
)->out(false),

'isselected' =>
    $datekey ===
    $selecteddate->format('Y-m-d'),
                'daynumber' => (int) $day->format('j'),
                'iscurrentmonth' =>
                    $day->format('Y-m') ===
                    $monthstart->format('Y-m'),
                'istoday' =>
                    $datekey === $today->format('Y-m-d'),
                'hasevents' => !empty($dayevents),
                'events' => $dayevents,
            ];
        }
$selecteddatekey =
    $selecteddate->format('Y-m-d');

$selectedevents =
    $eventsbydate[$selecteddatekey] ?? [];
        return [
            'monthtitle' => userdate(
                $monthstart->getTimestamp(),
                '%B %Y'
            ),
            'weekdays' => $weekdays,
            'days' => $days,
'selecteddaytitle' => userdate(
    $selecteddate->getTimestamp(),
    '%A, %d %B %Y'
),

'hasselectedevents' =>
    !empty($selectedevents),

'selectedevents' => $selectedevents,
            'previousurl' => (
                new moodle_url('/local/qubexa/index.php', [
                    'page' => 'calendar',
                    'viewdate' => $monthstart
                        ->modify('-1 month')
                        ->getTimestamp(),
                ])
            )->out(false),

            'nexturl' => (
                new moodle_url('/local/qubexa/index.php', [
                    'page' => 'calendar',
                    'viewdate' => $monthstart
                        ->modify('+1 month')
                        ->getTimestamp(),
                ])
            )->out(false),

            'todayurl' => (
                new moodle_url('/local/qubexa/index.php', [
                    'page' => 'calendar',
                    'viewdate' => time(),
                ])
            )->out(false),

            'addeventurl' => (
    new moodle_url(
        '/local/qubexa_calendar/edit.php',
        [
            'date' =>
                $selecteddate->format('Y-m-d'),
        ]
    )
)->out(false),
        ];
    }
}
