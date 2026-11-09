<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Database;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Client self-service payments ("Send Payment" in the client portal), through the simulated
 * mobile money adapter (EVC Plus, Zaad, eDahab).
 *
 * Rule: a client can pay any amount above $0 up to what they owe on that financing
 * (remaining installments + unpaid charity late charges), never more. Owing $500 and sending $501
 * is refused with "only send $500". The payment is then allocated by RepaymentEngine exactly like
 * a repayment recorded by staff (charity charges first, then the oldest unpaid installment).
 */
final class ClientPaymentService
{
    public const PROVIDERS = ['EVC Plus', 'Zaad', 'eDahab'];

    /** What the client owes on a financing right now, in cents (installments + unpaid charity charges). */
    public static function owedCents(int $financingId, ?PDO $pdo = null): int
    {
        $db = $pdo ?? Database::connection();
        $st = $db->prepare("
            SELECT f.outstanding_balance,
                   COALESCE((SELECT SUM(cc.remaining_amount) FROM charity_charges cc
                             WHERE cc.financing_id = f.id AND cc.status NOT IN ('waived', 'paid')), 0) AS charity_owed
            FROM financings f
            WHERE f.id = :id
        ");
        $st->execute([':id' => $financingId]);
        $row = $st->fetch();
        if (!$row) {
            return 0;
        }
        return max(0, ScheduleEngine::toCents((string) $row['outstanding_balance'])) + max(0, ScheduleEngine::toCents((string) $row['charity_owed']));
    }

    /**
     * The client's financings that can still be paid, with the amount owed and the next installment.
     *
     * @return list<array<string, mixed>>
     */
    public static function payableFinancings(int $clientId, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $st = $db->prepare("
            SELECT f.id, f.financing_number, f.product_type, f.total_due, f.total_paid, f.outstanding_balance, f.status,
                   (SELECT i.due_date FROM installments i
                    WHERE i.financing_id = f.id AND i.status <> 'paid' AND i.remaining_balance > 0
                    ORDER BY i.installment_number LIMIT 1) AS next_due_date,
                   (SELECT i.remaining_balance FROM installments i
                    WHERE i.financing_id = f.id AND i.status <> 'paid' AND i.remaining_balance > 0
                    ORDER BY i.installment_number LIMIT 1) AS next_due_amount
            FROM financings f
            WHERE f.client_id = :cid AND f.status IN ('active', 'overdue')
            ORDER BY f.id DESC
        ");
        $st->execute([':cid' => $clientId]);
        $rows = [];
        foreach ($st->fetchAll() as $r) {
            $owed = self::owedCents((int) $r['id'], $db);
            if ($owed <= 0) {
                continue;
            }
            $r['owed'] = ScheduleEngine::fromCents($owed);
            $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Records a payment the signed-in client sends from the portal.
     *
     * @param array $user the signed-in client (role_slug 'client', id, client_id)
     * @return array RepaymentEngine result plus 'provider', 'phone' and 'transaction_reference'
     * @throws RuntimeException when the financing is not theirs, not payable, the provider or phone is
     *                          invalid, or the amount is zero or more than what they owe
     */
    public static function pay(array $user, int $financingId, string $amount, string $provider, string $phone, bool $isSo = true, ?PDO $pdo = null): array
    {
        $tr = static fn (string $so, string $en): string => $isSo ? $so : $en;
        if (($user['role_slug'] ?? '') !== 'client' || empty($user['client_id'])) {
            throw new RuntimeException($tr('Lacag-bixintan waxaa diri kara macmiil keliya.', 'Only a client can send this payment.'));
        }
        if (!in_array($provider, self::PROVIDERS, true)) {
            throw new RuntimeException($tr('Dooro EVC Plus, Zaad ama eDahab.', 'Choose EVC Plus, Zaad or eDahab.'));
        }
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            throw new RuntimeException($tr('Geli lambar telefoon sax ah.', 'Enter a valid phone number.'));
        }
        $clean = trim($amount);
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $clean)) {
            throw new RuntimeException($tr('Geli lacag sax ah (tusaale 50 ama 50.25).', 'Enter a valid amount (for example 50 or 50.25).'));
        }
        $cents = ScheduleEngine::toCents($clean);
        if ($cents <= 0) {
            throw new RuntimeException($tr('Lacagtu waa inay ka badan tahay $0.', 'The amount must be more than $0.'));
        }

        $db = $pdo ?? Database::connection();
        $db->beginTransaction();
        try {
            // Lock the financing so two payments at once can't both pass the limit.
            $st = $db->prepare('SELECT id, client_id, status, financing_number FROM financings WHERE id = :id FOR UPDATE');
            $st->execute([':id' => $financingId]);
            $fin = $st->fetch();
            if (!$fin || (int) $fin['client_id'] !== (int) $user['client_id']) {
                throw new RuntimeException($tr('Maalgelintan lama helin.', 'Financing not found.'));
            }
            if (!in_array($fin['status'], ['active', 'overdue'], true)) {
                throw new RuntimeException($tr('Maalgelintan horay ayaa loo wada bixiyay.', 'This financing is already fully paid.'));
            }

            $owed = self::owedCents($financingId, $db);
            if ($cents > $owed) {
                $owedText = '$' . ScheduleEngine::fromCents($owed);
                $sentText = '$' . ScheduleEngine::fromCents($cents);
                throw new RuntimeException($tr(
                    "Lacagtaas lama oggola. Waxaa lagugu leeyahay {$owedText} oo keliya, adiguna waxaad dirtay {$sentText}. Kaliya dir {$owedText} ama ka yar.",
                    "That amount is not allowed. You owe only {$owedText} and you sent {$sentText}. Send {$owedText} or less."
                ));
            }

            $ref = 'CLP-' . strtoupper(bin2hex(random_bytes(4)));
            $res = RepaymentEngine::recordRepayment(
                $financingId,
                (int) $user['id'],
                ScheduleEngine::fromCents($cents),
                $provider,
                $ref,
                date('Y-m-d'),
                'Sent by the client from the portal (' . $provider . ' +' . $digits . ')',
                false,
                '0.00',
                $db
            );

            AuditLogger::log(
                'client.payment_sent',
                'repayments',
                (int) $res['repayment_id'],
                "Client paid \${$res['cash_amount']} on {$fin['financing_number']} via {$provider} (+{$digits}), ref {$ref}.",
                [],
                (int) $user['id']
            );
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }

        $res['provider'] = $provider;
        $res['phone'] = $digits;
        $res['transaction_reference'] = $ref;
        return $res;
    }
}
