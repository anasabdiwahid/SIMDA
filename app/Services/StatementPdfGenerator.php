<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\Lang;
use PDO;
use RuntimeException;

/**
 * Module 13 — Client Statements Generator (Pure PHP Native PDF 1.4 + Structured Data)
 *
 * Generates downloadable valid PDF documents directly from live database records
 * without requiring third-party binaries or Composer packages.
 *
 * Includes:
 * - Client information & Branch details
 * - Financing contract details
 * - All installments (Due dates, Installment amounts, Amount paid, Remaining balance, Status)
 * - Complete Payment History
 * - Charity Late Charges & Waivers
 * - Total Remaining Balance (Installment Remaining + Unpaid Charity Charges)
 * - Closed financings retain downloadable final statements clearly marked "FULLY REPAID / SI BUUXDA LOO BIXIYAY"
 */
final class StatementPdfGenerator
{
    public static function buildStatementData(int $financingId, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();

        $finStmt = $db->prepare('
            SELECT f.*,
                   c.client_code, c.full_name AS client_name, c.phone AS client_phone,
                   c.id_type, c.id_number, c.city, c.district, c.address, c.credit_balance,
                   p.name_so AS product_name_so, p.name_en AS product_name_en, p.product_code,
                   b.name AS branch_name, b.code AS branch_code, b.phone AS branch_phone
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            INNER JOIN products p ON p.id = f.product_id
            INNER JOIN branches b ON b.id = f.branch_id
            WHERE f.id = :id
            LIMIT 1
        ');
        $finStmt->execute([':id' => $financingId]);
        $financing = $finStmt->fetch();

        if (!$financing) {
            throw new RuntimeException('Financing record not found for statement generation.');
        }

        $instStmt = $db->prepare('
            SELECT * FROM installments
            WHERE financing_id = :fid
            ORDER BY installment_number ASC
        ');
        $instStmt->execute([':fid' => $financingId]);
        $installments = $instStmt->fetchAll();

        $repStmt = $db->prepare('
            SELECT r.*, u.full_name AS recorded_by_name
            FROM repayments r
            INNER JOIN users u ON u.id = r.recorded_by
            WHERE r.financing_id = :fid
            ORDER BY r.id ASC
        ');
        $repStmt->execute([':fid' => $financingId]);
        $repayments = $repStmt->fetchAll();

        $ccStmt = $db->prepare('
            SELECT cc.*, i.installment_number
            FROM charity_charges cc
            INNER JOIN installments i ON i.id = cc.installment_id
            WHERE cc.financing_id = :fid
            ORDER BY cc.id ASC
        ');
        $ccStmt->execute([':fid' => $financingId]);
        $charityCharges = $ccStmt->fetchAll();

        $unpaidCharityCents = 0;
        foreach ($charityCharges as $cc) {
            if (in_array($cc['status'], ['unpaid', 'partial'], true)) {
                $unpaidCharityCents += ScheduleEngine::toCents($cc['remaining_amount']);
            }
        }

        $outBalCents = ScheduleEngine::toCents($financing['outstanding_balance']);
        $totalRemainingCents = $outBalCents + $unpaidCharityCents;
        $isFullyRepaid = ($financing['status'] === 'completed' && $totalRemainingCents === 0);

        return [
            'financing' => $financing,
            'installments' => $installments,
            'repayments' => $repayments,
            'charity_charges' => $charityCharges,
            'unpaid_charity_total' => ScheduleEngine::fromCents($unpaidCharityCents),
            'total_remaining_balance' => ScheduleEngine::fromCents($totalRemainingCents),
            'is_fully_repaid' => $isFullyRepaid,
            'watermark_label' => $isFullyRepaid ? 'FULLY REPAID / SI BUUXDA LOO BIXIYAY' : strtoupper((string) $financing['status']),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Render a valid, multi-line PDF 1.4 binary string containing the complete client statement.
     */
    public static function generatePdfBinary(int $financingId, string $locale = 'so', ?PDO $pdo = null): string
    {
        $data = self::buildStatementData($financingId, $pdo);
        $f = $data['financing'];

        $lines = [];
        $lines[] = ['bold' => true, 'size' => 15, 'text' => 'SIMDA - SOMALI ISLAMIC MICROFINANCE DEVELOPMENT ASSOCIATION'];
        $lines[] = ['bold' => false, 'size' => 10, 'text' => ($locale === 'so' ? 'BAYAANKA XISAABTA MACMIILKA (OFFICIAL CLIENT STATEMENT)' : 'OFFICIAL SHARIA-COMPLIANT FINANCING CLIENT STATEMENT')];
        $lines[] = ['bold' => true, 'size' => 11, 'text' => 'STATUS: ' . ($data['is_fully_repaid'] ? 'FULLY REPAID / SI BUUXDA LOO BIXIYAY' : strtoupper((string) $f['status']))];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => str_repeat('-', 95)];

        $lines[] = ['bold' => true, 'size' => 10, 'text' => ($locale === 'so' ? '1. MACLUUMAADKA MACMIILKA & HESHIISKA' : '1. CLIENT & FINANCING CONTRACT DETAILS')];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => sprintf('Client: %s (%s) | ID: %s | Phone: %s', $f['client_name'], $f['client_code'], $f['id_number'], $f['client_phone'])];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => sprintf('Branch: %s (%s) | Address: %s, %s', $f['branch_name'], $f['branch_code'], $f['district'], $f['city'])];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => sprintf('Financing No: %s | Product: %s (%s)', $f['financing_number'], $f['product_name_en'], strtoupper($f['product_type']))];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => sprintf('Disbursed Date: %s via %s (Ref: %s)', substr((string) $f['disbursed_at'], 0, 10), $f['disbursement_provider'], $f['disbursement_reference'])];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => sprintf('Approved Principal: $%s | Admin Fee: $%s | Net Disbursed: $%s', $f['approved_amount'], $f['admin_fee'], $f['net_disbursed_amount'])];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => sprintf('Profit/Markup: $%s | Ibra Discount: $%s | Total Contract Due: $%s', $f['total_profit'], $f['ibra_discount'], $f['total_due'])];
        $lines[] = ['bold' => false, 'size' => 9, 'text' => str_repeat('-', 95)];

        $lines[] = ['bold' => true, 'size' => 10, 'text' => ($locale === 'so' ? '2. JADWALKA BIXINTA (INSTALLMENT SCHEDULE)' : '2. INSTALLMENT REPAYMENT SCHEDULE')];
        $lines[] = ['bold' => true, 'size' => 8, 'text' => sprintf('%-4s %-16s %-12s %-12s %-12s %-12s %-12s %-10s', '#', 'Due Date', 'Principal', 'Profit', 'Total Due', 'Paid', 'Remaining', 'Status')];

        foreach ($data['installments'] as $inst) {
            $lines[] = [
                'bold' => false,
                'size' => 8,
                'text' => sprintf(
                    '%-4s %-16s $%-11s $%-11s $%-11s $%-11s $%-11s %-10s',
                    $inst['installment_number'],
                    ScheduleEngine::formatDue($inst),
                    $inst['principal_due'],
                    $inst['profit_due'],
                    $inst['total_due'],
                    $inst['total_paid'],
                    $inst['remaining_balance'],
                    strtoupper((string) $inst['status'])
                ),
            ];
        }

        $lines[] = ['bold' => false, 'size' => 9, 'text' => str_repeat('-', 95)];
        $lines[] = ['bold' => true, 'size' => 10, 'text' => ($locale === 'so' ? '3. TAARIIKHDA LACAG BIXINTA (PAYMENT HISTORY)' : '3. PAYMENT HISTORY')];
        if (empty($data['repayments'])) {
            $lines[] = ['bold' => false, 'size' => 8, 'text' => 'No repayments recorded yet.'];
        } else {
            foreach ($data['repayments'] as $rep) {
                $lines[] = [
                    'bold' => false,
                    'size' => 8,
                    'text' => sprintf(
                        '%s | Date: %s | Method: %s | Paid: $%s | Prin: $%s | Prof: $%s | Charity: $%s',
                        $rep['receipt_number'],
                        $rep['payment_date'],
                        $rep['payment_method'],
                        $rep['amount'],
                        $rep['allocated_to_principal'],
                        $rep['allocated_to_profit'],
                        $rep['allocated_to_charity']
                    ),
                ];
            }
        }

        $lines[] = ['bold' => false, 'size' => 9, 'text' => str_repeat('-', 95)];
        $lines[] = ['bold' => true, 'size' => 10, 'text' => ($locale === 'so' ? '4. KHIDMADAHA SADAQADA EE DIB-U-DHACA (CHARITY LATE CHARGES)' : '4. CHARITY LATE CHARGES (NON-INCOME CHARITY ACCOUNT)')];
        if (empty($data['charity_charges'])) {
            $lines[] = ['bold' => false, 'size' => 8, 'text' => 'No charity late charges assessed ($0.00).'];
        } else {
            foreach ($data['charity_charges'] as $cc) {
                $lines[] = [
                    'bold' => false,
                    'size' => 8,
                    'text' => sprintf(
                        'Installment #%s | Assessed: $%s | Paid: $%s | Remaining: $%s | Status: %s %s',
                        $cc['installment_number'],
                        $cc['amount'],
                        $cc['amount_paid'],
                        $cc['remaining_amount'],
                        strtoupper((string) $cc['status']),
                        !empty($cc['waiver_reason']) ? '(Waived: ' . $cc['waiver_reason'] . ')' : ''
                    ),
                ];
            }
        }

        $lines[] = ['bold' => false, 'size' => 9, 'text' => str_repeat('=', 95)];
        $lines[] = ['bold' => true, 'size' => 10, 'text' => sprintf('SUMMARY: Total Paid: $%s | Installment Balance: $%s | Unpaid Charity Charges: $%s', $f['total_paid'], $f['outstanding_balance'], $data['unpaid_charity_total'])];
        $lines[] = ['bold' => true, 'size' => 11, 'text' => sprintf('TOTAL REMAINING BALANCE DUE: $%s   [%s]', $data['total_remaining_balance'], $data['watermark_label'])];
        $lines[] = ['bold' => false, 'size' => 8, 'text' => 'Generated by SIMDA Core Financial System on ' . $data['generated_at']];

        return self::compilePdfDocument($lines);
    }

    private static function compilePdfDocument(array $lines): string
    {
        $stream = "BT\n";
        $y = 800;
        foreach ($lines as $line) {
            if ($y < 50) {
                break;
            }
            $font = !empty($line['bold']) ? '/F2' : '/F1';
            $size = (int) ($line['size'] ?? 9);
            $safeText = self::escapePdfText((string) $line['text']);
            $stream .= "{$font} {$size} Tf\n";
            $stream .= "1 0 0 1 36 {$y} Tm\n";
            $stream .= "({$safeText}) Tj\n";
            $y -= ($size + 5);
        }
        $stream .= "ET\n";

        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>";
        $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier-Bold >>";
        $objects[6] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }

    private static function escapePdfText(string $text): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii === false) {
            $ascii = $text;
        }
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
    }
}

