<?php
namespace local_qubexa_classes\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

final class class_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;

        $mform->addElement(
            'hidden',
            'id',
            0
        );
        $mform->setType('id', PARAM_INT);

        $mform->addElement(
            'text',
            'schoolname',
            get_string(
                'schoolname',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'schoolname',
            PARAM_TEXT
        );

        $mform->addElement(
            'text',
            'classname',
            get_string(
                'classname',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'classname',
            PARAM_TEXT
        );
        $mform->addRule(
            'classname',
            null,
            'required',
            null,
            'client'
        );
        $mform->addElement(
            'text',
            'sectionname',
            get_string(
                'sectionname',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'sectionname',
            PARAM_TEXT
        );

        $mform->addElement(
            'text',
            'coursename',
            get_string(
                'coursename',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'coursename',
            PARAM_TEXT
        );
        $mform->addRule(
            'coursename',
            null,
            'required',
            null,
            'client'
        );
        $currentyear = (int) date('Y');

        $mform->addElement(
            'text',
            'academicyear',
            get_string(
                'academicyear',
                'local_qubexa_classes'
            )
        );
        $mform->setType(
            'academicyear',
            PARAM_TEXT
        );
        $mform->setDefault(
            'academicyear',
            $currentyear . '-' . ($currentyear + 1)
        );
        $mform->addRule(
            'academicyear',
            null,
            'required',
            null,
            'client'
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
                'classstatus',
                'local_qubexa_classes'
            ),
            $statusoptions
        );
        $mform->setDefault('status', 1);
        $colouroptions = [
            '#1769c2' => '🔵',
            '#15966b' => '🟢',
            '#d98b17' => '🟠',
            '#dc3f52' => '🔴',
            '#7655c5' => '🟣',
            '#56677f' => '⚫',
        ];

        $mform->addElement(
            'select',
            'color',
            get_string(
                'color',
                'local_qubexa_classes'
            ),
            $colouroptions
        );
        $mform->setDefault(
            'color',
            '#1769c2'
        );

        $this->add_action_buttons(
            true,
            get_string(
                'saveclass',
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

        if (trim($data['classname'] ?? '') === '') {
            $errors['classname'] =
                get_string('required');
        }

        if (trim($data['coursename'] ?? '') === '') {
            $errors['coursename'] =
                get_string('required');
        }

        if (!preg_match(
            '/^\d{4}-\d{4}$/',
            trim($data['academicyear'] ?? '')
        )) {
            $errors['academicyear'] =
                get_string('invaliddata');
        }

        $validcolours = [
            '#1769c2',
            '#15966b',
            '#d98b17',
            '#dc3f52',
            '#7655c5',
            '#56677f',
        ];

        if (!in_array(
            $data['color'] ?? '',
            $validcolours,
            true
        )) {
            $errors['color'] =
                get_string('invaliddata');
        }

        return $errors;
    }
}