<?php
namespace local_qubexa_classes\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

final class add_point extends external_api {
    public static function execute_parameters():
            external_function_parameters {

        return new external_function_parameters([
            'classid' => new external_value(
                PARAM_INT,
                'Class ID'
            ),
            'studentid' => new external_value(
                PARAM_INT,
                'Student ID'
            ),
            'pointvalue' => new external_value(
                PARAM_INT,
                'Point value'
            ),
        ]);
    }
    public static function execute(
        int $classid,
        int $studentid,
        int $pointvalue
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'classid' => $classid,
                'studentid' => $studentid,
                'pointvalue' => $pointvalue,
            ]
        );

        require_login();

        $context = \context_system::instance();

        self::validate_context($context);

        require_capability(
            'local/qubexa_classes:manage',
            $context
        );

        if (!in_array(
            $params['pointvalue'],
            [-1, 1],
            true
        )) {
            throw new \invalid_parameter_exception(
                'Point value must be 1 or -1.'
            );
        }

        $classrecord = $DB->get_record(
            'local_qubexa_classes',
            [
                'id' => $params['classid'],
                'userid' => $USER->id,
            ],
            '*',
            MUST_EXIST
        );

        $studentrecord = $DB->get_record(
            'local_qubexa_class_students',
            [
                'id' => $params['studentid'],
                'classid' => $classrecord->id,
                'userid' => $USER->id,
            ],
            '*',
            MUST_EXIST
        );
        $pointrecord = new \stdClass();

        $pointrecord->userid =
            (int) $USER->id;

        $pointrecord->classid =
            (int) $classrecord->id;

        $pointrecord->studentid =
            (int) $studentrecord->id;

        $pointrecord->pointvalue =
            (int) $params['pointvalue'];

        $pointrecord->timecreated = time();

        $DB->insert_record(
            'local_qubexa_class_points',
            $pointrecord
        );

        $stats = $DB->get_record_sql(
            "SELECT studentid,
                    SUM(
                        CASE
                            WHEN pointvalue = 1 THEN 1
                            ELSE 0
                        END
                    ) AS pluscount,
                    SUM(
                        CASE
                            WHEN pointvalue = -1 THEN 1
                            ELSE 0
                        END
                    ) AS minuscount,
                    SUM(pointvalue) AS pointtotal
               FROM {local_qubexa_class_points}
              WHERE userid = :pointuserid
                AND classid = :pointclassid
                AND studentid = :pointstudentid
           GROUP BY studentid",
            [
                'pointuserid' => $USER->id,
                'pointclassid' => $classrecord->id,
                'pointstudentid' => $studentrecord->id,
            ],
            MUST_EXIST
        );

        return [
            'pluscount' => (int) $stats->pluscount,
            'minuscount' => (int) $stats->minuscount,
            'pointtotal' => (int) $stats->pointtotal,
        ];
    }
    public static function execute_returns():
            external_single_structure {

        return new external_single_structure([
            'pluscount' => new external_value(
                PARAM_INT,
                'Total plus count'
            ),
            'minuscount' => new external_value(
                PARAM_INT,
                'Total minus count'
            ),
            'pointtotal' => new external_value(
                PARAM_INT,
                'Net point result'
            ),
        ]);
    }
}