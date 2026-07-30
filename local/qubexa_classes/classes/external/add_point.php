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
            [-1, 0, 1],
            true
        )) {
            throw new \invalid_parameter_exception(
                'Point value must be -1, 0, or 1.'
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

        $timezone =
            \core_date::get_user_timezone_object();

        $today = new \DateTimeImmutable(
            'today',
            $timezone
        );

        $tomorrow = $today->modify('+1 day');

        $daystart = $today->getTimestamp();
        $dayend = $tomorrow->getTimestamp();

        if ($params['pointvalue'] === 0) {
            $lastpoint = $DB->get_record_sql(
                "SELECT id
                   FROM {local_qubexa_class_points}
                  WHERE userid = :undouserid
                    AND classid = :undoclassid
                    AND studentid = :undostudentid
                    AND timecreated >= :undodaystart
                    AND timecreated < :undodayend
               ORDER BY id DESC",
                [
                    'undouserid' => $USER->id,
                    'undoclassid' => $classrecord->id,
                    'undostudentid' => $studentrecord->id,
                    'undodaystart' => $daystart,
                    'undodayend' => $dayend,
                ],
                IGNORE_MISSING
            );

            if ($lastpoint) {
                $DB->delete_records(
                    'local_qubexa_class_points',
                    ['id' => $lastpoint->id]
                );
            }
        } else {
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
        }

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
            IGNORE_MISSING
        );
        $todaystats = $DB->get_record_sql(
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
              WHERE userid = :todayuserid
                AND classid = :todayclassid
                AND studentid = :todaystudentid
                AND timecreated >= :daystart
                AND timecreated < :dayend
           GROUP BY studentid",
            [
                'todayuserid' => $USER->id,
                'todayclassid' => $classrecord->id,
                'todaystudentid' => $studentrecord->id,
                'daystart' => $daystart,
                'dayend' => $dayend,
            ],
            IGNORE_MISSING
        );

        return [
            'pluscount' =>
                $todaystats
                    ? (int) $todaystats->pluscount
                    : 0,

            'minuscount' =>
                $todaystats
                    ? (int) $todaystats->minuscount
                    : 0,

            'pointtotal' =>
                $todaystats
                    ? (int) $todaystats->pointtotal
                    : 0,

            'totalpluscount' =>
                $stats
                    ? (int) $stats->pluscount
                    : 0,

            'totalminuscount' =>
                $stats
                    ? (int) $stats->minuscount
                    : 0,

            'totalpointtotal' =>
                $stats
                    ? (int) $stats->pointtotal
                    : 0,
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

            'totalpluscount' => new external_value(
                PARAM_INT,
                'All-time plus count'
            ),

            'totalminuscount' => new external_value(
                PARAM_INT,
                'All-time minus count'
            ),

            'totalpointtotal' => new external_value(
                PARAM_INT,
                'All-time net point result'
            ),
        ]);
    }
}
