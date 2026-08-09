<?php
namespace local_qubexa_students\service;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\repository\payment_repository;
use local_qubexa_students\repository\student_repository;

final class payment_service {
    private const STATUSES = [
        'paid',
        'pending',
        'overdue',
        'cancelled',
    ];

    private const METHODS = [
        'cash',
        'bank',
        'card',
        'other',
    ];

    private student_repository $students;
    private payment_repository $payments;

    public function __construct(
        ?student_repository $students = null,
        ?payment_repository $payments = null
    ) {
        $this->students = $students ?? new student_repository();
        $this->payments = $payments ?? new payment_repository();
    }

    private function require_owned_student(
        int $studentid,
        int $userid
    ): \stdClass {
        $student = $this->students->find_owned_student(
            $studentid,
            $userid
        );

        if (!$student) {
            throw new \moodle_exception('invalidrecord', 'error');
        }

        return $student;
    }

    private function normalise_date(string $value): int {
        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value
        );

        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new \invalid_parameter_exception(
                'Geçerli bir ödeme tarihi girin.'
            );
        }

        return $date->getTimestamp();
    }

    private function money(float $amount): string {
        return number_format($amount, 2, ',', '.') . ' ₺';
    }

    private function status_label(string $status): string {
        return [
            'paid' => 'Ödendi',
            'pending' => 'Bekliyor',
            'overdue' => 'Gecikmiş',
            'cancelled' => 'İptal',
        ][$status] ?? $status;
    }

    private function method_label(string $method): string {
        return [
            'cash' => 'Nakit',
            'bank' => 'Banka',
            'card' => 'Kart',
            'other' => 'Diğer',
        ][$method] ?? $method;
    }

    private function view_model(\stdClass $record): array {
        return [
            'id' => (int) $record->id,
            'paymentdate' => userdate(
                $record->paymentdate,
                get_string('strftimedate', 'langconfig')
            ),
            'amountlabel' => $this->money((float) $record->amount),
            'status' => (string) $record->status,
            'statuslabel' => $this->status_label(
                (string) $record->status
            ),
            'methodlabel' => $this->method_label(
                (string) $record->method
            ),
            'description' => (string) ($record->description ?? ''),
        ];
    }

    public function list_payments(
        int $studentid,
        int $userid
    ): array {
        $this->require_owned_student($studentid, $userid);

        $items = [];
        $paid = 0.0;
        $pending = 0.0;
        $overdue = 0.0;

        foreach (
            $this->payments->find_for_student(
                $studentid,
                $userid
            ) as $record
        ) {
            $items[] = $this->view_model($record);

            if ($record->status === 'paid') {
                $paid += (float) $record->amount;
            } elseif ($record->status === 'pending') {
                $pending += (float) $record->amount;
            } elseif ($record->status === 'overdue') {
                $overdue += (float) $record->amount;
            }
        }

        return [
            'items' => $items,
            'summary' => [
                'paid' => $this->money($paid),
                'pending' => $this->money($pending),
                'overdue' => $this->money($overdue),
                'receivable' => $this->money($pending + $overdue),
            ],
        ];
    }

    public function add_payment(
        int $studentid,
        int $userid,
        string $date,
        float $amount,
        string $status,
        string $method,
        string $description = ''
    ): int {
        $this->require_owned_student($studentid, $userid);

        if (!is_finite($amount) || $amount <= 0) {
            throw new \invalid_parameter_exception(
                'Tutar sıfırdan büyük olmalıdır.'
            );
        }

        if ($amount > 9999999999.99) {
            throw new \invalid_parameter_exception(
                'Tutar en fazla 9.999.999.999,99 olabilir.'
            );
        }

        if (!in_array($status, self::STATUSES, true)) {
            throw new \invalid_parameter_exception(
                'Geçersiz ödeme durumu.'
            );
        }

        if (!in_array($method, self::METHODS, true)) {
            throw new \invalid_parameter_exception(
                'Geçersiz ödeme yöntemi.'
            );
        }

        $description = trim($description);

        if (\core_text::strlen($description) > 2000) {
            throw new \invalid_parameter_exception(
                'Açıklama en fazla 2000 karakter olabilir.'
            );
        }

        $now = time();

        return $this->payments->create((object) [
            'studentid' => $studentid,
            'userid' => $userid,
            'paymentdate' => $this->normalise_date($date),
            'amount' => round($amount, 2),
            'status' => $status,
            'method' => $method,
            'description' => $description,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    public function delete_payment(
        int $paymentid,
        int $studentid,
        int $userid
    ): void {
        $this->require_owned_student($studentid, $userid);

        if (!$this->payments->delete_owned(
            $paymentid,
            $studentid,
            $userid
        )) {
            throw new \moodle_exception('invalidrecord', 'error');
        }
    }
}
