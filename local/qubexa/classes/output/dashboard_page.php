<?php
namespace local_qubexa\output;

defined('MOODLE_INTERNAL') || die();

use local_qubexa\service\dashboard_service;

final class dashboard_page implements \renderable, \templatable {
    private int $userid;

    public function __construct(int $userid) {
        $this->userid = $userid;
    }

    public function export_for_template($output): array {
        global $USER;

        $service = new dashboard_service();
        $data = $service->build($this->userid);

        return array_merge($data, [
            'greeting' => get_string(
                'dashboardgreeting',
                'local_qubexa',
                s($USER->firstname ?: fullname($USER))
            ),
            'summary' => get_string('dashboardsummary', 'local_qubexa'),
            'todaylabel' => userdate(
                time(),
                get_string('strftimedaydate', 'langconfig')
            ),
            'totalstudentslabel' => get_string('dashboardtotalstudents', 'local_qubexa'),
            'activestudentslabel' => get_string('dashboardactivestudents', 'local_qubexa'),
            'monthlycollectionlabel' => get_string('dashboardmonthlycollection', 'local_qubexa'),
            'pendingpaymentslabel' => get_string('dashboardpendingpayments', 'local_qubexa'),
            'examaveragelabel' => get_string('dashboardexamaverage', 'local_qubexa'),
            'todaylessonslabel' => get_string('dashboardtodaylessons', 'local_qubexa'),
            'quickactionslabel' => get_string('dashboardquickactions', 'local_qubexa'),
            'recentactivitylabel' => get_string('dashboardrecentactivity', 'local_qubexa'),
            'nodatalabel' => get_string('dashboardnodata', 'local_qubexa'),
            'currencycode' => get_string('dashboardcurrency', 'local_qubexa'),
            'studentsurl' => \local_qubexa\workspace::page_url('students')->out(false),
            'calendarurl' => \local_qubexa\workspace::page_url('calendar')->out(false),
            'assessmenturl' => \local_qubexa\workspace::page_url('assessment')->out(false),
            'reportsurl' => \local_qubexa\workspace::page_url('reports')->out(false),
            'addstudenturl' => (new \moodle_url('/local/qubexa_students/edit.php'))->out(false),
            'addstudentlabel' => get_string('dashboardaddstudent', 'local_qubexa'),
            'studentslabel' => get_string('dashboardstudents', 'local_qubexa'),
            'assessmentlabel' => get_string('dashboardassessment', 'local_qubexa'),
            'calendarlabel' => get_string('dashboardcalendar', 'local_qubexa'),
            'reportslabel' => get_string('dashboardreports', 'local_qubexa'),
        ]);
    }
}
