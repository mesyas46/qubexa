<?php
namespace local_qubexa_calendar\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

final class event_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;

        $students = $this->_customdata['students'] ?? [];

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement(
            'text',
            'title',
            get_string('title', 'local_qubexa_calendar'),
            ['maxlength' => 255]
        );
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule(
            'title',
            null,
            'required',
            null,
            'client'
        );

        $mform->addElement(
            'select',
            'eventtype',
            get_string('eventtype', 'local_qubexa_calendar'),
            [
                'lesson' => get_string(
                    'lesson',
                    'local_qubexa_calendar'
                ),
                'meeting' => get_string(
                    'meeting',
                    'local_qubexa_calendar'
                ),
                'reminder' => get_string(
                    'reminder',
                    'local_qubexa_calendar'
                ),
            ]
        );

        $mform->addElement(
            'autocomplete',
            'studentid',
            get_string('student', 'local_qubexa_calendar'),
            $students,
            [
    'multiple' => false,
    'noselectionstring' => get_string(
        'nostudent',
        'local_qubexa_calendar'
    ),
    'placeholder' => get_string('search'),
]
        );
        $mform->setType('studentid', PARAM_INT);

        $timeoptions = [
            'optional' => false,
            'step' => 300,
        ];

        $mform->addElement(
            'date_time_selector',
            'timestart',
            get_string('starttime', 'local_qubexa_calendar'),
            $timeoptions
        );

        $mform->addElement(
            'date_time_selector',
            'timeend',
            get_string('endtime', 'local_qubexa_calendar'),
            $timeoptions
        );

        $mform->addElement(
            'select',
            'status',
            get_string('status', 'local_qubexa_calendar'),
            [
                'planned' => get_string(
                    'planned',
                    'local_qubexa_calendar'
                ),
                'completed' => get_string(
                    'completed',
                    'local_qubexa_calendar'
                ),
                'cancelled' => get_string(
                    'cancelled',
                    'local_qubexa_calendar'
                ),
            ]
        );

        $mform->addElement(
            'select',
            'recurrence',
            get_string('recurrence', 'local_qubexa_calendar'),
            [
                'none' => get_string(
                    'none',
                    'local_qubexa_calendar'
                ),
                'weekly' => get_string(
                    'weekly',
                    'local_qubexa_calendar'
                ),
                'monthly' => get_string(
                    'monthly',
                    'local_qubexa_calendar'
                ),
            ]
        );

        $mform->addElement(
            'date_selector',
            'recurrenceuntil',
            get_string(
                'recurrenceuntil',
                'local_qubexa_calendar'
            ),
            ['optional' => true]
        );

        $mform->hideIf(
            'recurrenceuntil',
            'recurrence',
            'eq',
            'none'
        );

        $mform->addElement(
            'textarea',
            'description',
            get_string('description', 'local_qubexa_calendar'),
            ['rows' => 5]
        );
        $mform->setType('description', PARAM_TEXT);

        $this->add_action_buttons(
            true,
            get_string('save', 'local_qubexa_calendar')
        );
    }

    public function validation(
        $data,
        $files
    ): array {
        $errors = parent::validation($data, $files);

        if (
            !empty($data['timestart']) &&
            !empty($data['timeend']) &&
            $data['timeend'] <= $data['timestart']
        ) {
            $errors['timeend'] = get_string(
                'endbeforestart',
                'local_qubexa_calendar'
            );
        }

        return $errors;
    }
}