<?php
namespace local_qubexa_classes\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

final class student_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;

        $mform->addElement(
            'hidden',
            'id',
            0
        );
        $mform->setType('id', PARAM_INT);

        $mform->addElement(
            'hidden',
            'classid',
            0
        );
        $mform->setType(
            'classid',
            PARAM_INT
        );

        $mform->addElement(
            'text',
            'firstname',
            get_string(
                'studentfirstname',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'firstname',
            PARAM_TEXT
        );
        $mform->addRule(
            'firstname',
            null,
            'required',
            null,
            'client'
        );

        $mform->addElement(
            'text',
            'lastname',
            get_string(
                'studentlastname',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'lastname',
            PARAM_TEXT
        );
        $mform->addRule(
            'lastname',
            null,
            'required',
            null,
            'client'
        );
        $mform->addElement(
            'text',
            'studentnumber',
            get_string(
                'studentnumber',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'studentnumber',
            PARAM_TEXT
        );

        $statusoptions = [
            1 => get_string(
                'active',
                'local_qubexa_classes'
            ),
            0 => get_string(
                'inactive',
                'local_qubexa_classes'
            ),
        ];

        $mform->addElement(
            'select',
            'status',
            get_string(
                'studentstatus',
                'local_qubexa_classes'
            ),
            $statusoptions
        );
        $mform->setDefault('status', 1);

        $this->add_action_buttons(
            true,
            get_string(
                'savestudent',
                'local_qubexa_classes'
            )
        );
    }
    public function validation(
        $data,
        $files
    ): array {
        $errors = parent::validation(
            $data,
            $files
        );

        if (trim($data['firstname'] ?? '') === '') {
            $errors['firstname'] =
                get_string('required');
        }

        if (trim($data['lastname'] ?? '') === '') {
            $errors['lastname'] =
                get_string('required');
        }

        return $errors;
    }
}