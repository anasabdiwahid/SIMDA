<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Database;
use App\Helpers\Env;
use App\Helpers\Security;
use PDO;
use RuntimeException;

/**
 * Core Domain Service for Modules 1, 2, 3, 4, and 10:
 * - Module 1: Client Profile & Guarantor Management (+ Admin Branch Transfer)
 * - Module 2: Financing Product Setup (deletion protection when in use, rate changes apply only to new apps)
 * - Module 3: Financing Applications (guarantor check, product limits, 30-day overdue block, draft/submit)
 * - Module 4: Approval Workflow ($5,000 threshold, creator self-approval block, single resubmission of rejected apps)
 * - Module 10: Group Financing (3-10 members, Branch Manager approval, 30-day overdue group lock, active financing leave block)
 */
final class FinancingDomainService
{
    /**
     * Module 1: Register a new Client along with their primary Guarantor (atomic transaction).
     */
    public static function registerClientWithGuarantor(
        array $clientData,
        array $guarantorData,
        int $registeredByUserId,
        ?PDO $pdo = null
    ): array {
        $db = $pdo ?? Database::connection();

        $fullName = trim((string) ($clientData['full_name'] ?? ''));
        $idType = trim((string) ($clientData['id_type'] ?? 'national_id'));
        $idNumber = trim((string) ($clientData['id_number'] ?? ''));
        $phone = trim((string) ($clientData['phone'] ?? ''));
        $email = trim((string) ($clientData['email'] ?? ''));
        $city = trim((string) ($clientData['city'] ?? ''));
        $district = trim((string) ($clientData['district'] ?? ''));
        $address = trim((string) ($clientData['address'] ?? ''));
        $businessType = trim((string) ($clientData['business_type'] ?? ''));
        $monthlyIncome = number_format((float) ($clientData['monthly_income'] ?? 0), 2, '.', '');
        $branchId = (int) ($clientData['branch_id'] ?? 0);
        $photoPath = $clientData['photo_path'] ?? null;

        if ($fullName === '' || $idNumber === '' || $phone === '' || $branchId <= 0) {
            throw new RuntimeException('Client full name, ID number, phone number, and branch assignment are required.');
        }

        // Guarantor validation (Mandatory: every client must have at least one guarantor)
        $gName = trim((string) ($guarantorData['full_name'] ?? ''));
        $gPhone = trim((string) ($guarantorData['phone'] ?? ''));
        $gId = trim((string) ($guarantorData['id_number'] ?? ''));
        $gRel = trim((string) ($guarantorData['relationship'] ?? ''));

        if ($gName === '' || $gPhone === '' || $gId === '' || $gRel === '') {
            throw new RuntimeException('Every client must have at least one complete guarantor (name, phone, ID, and relationship).');
        }

        // Check uniqueness of ID number, Phone number, and Email
        $sql = 'SELECT id, id_number, phone, email FROM clients WHERE id_number = :id_num OR phone = :phone';
        $params = [':id_num' => $idNumber, ':phone' => $phone];
        if ($email !== '') {
            $sql .= ' OR email = :email';
            $params[':email'] = $email;
        }
        $sql .= ' LIMIT 1';
        $dupStmt = $db->prepare($sql);
        $dupStmt->execute($params);
        $dup = $dupStmt->fetch();
        if ($dup) {
            if (strcasecmp((string) $dup['id_number'], $idNumber) === 0) {
                throw new RuntimeException("Client ID number '{$idNumber}' is already registered.");
            }
            if (strcasecmp((string) $dup['phone'], $phone) === 0) {
                throw new RuntimeException("Client phone number '{$phone}' is already registered.");
            }
            if ($email !== '' && strcasecmp((string) ($dup['email'] ?? ''), $email) === 0) {
                throw new RuntimeException("Client email '{$email}' is already registered.");
            }
        }

        $ownTx = !$db->inTransaction();
        if ($ownTx) {
            $db->beginTransaction();
        }

        try {
            $count = (int) $db->query('SELECT COUNT(*) FROM clients')->fetchColumn() + 1;
            $clientCode = 'CL-' . date('Y') . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT) . '-' . random_int(10, 99);

            // Optional linked portal account for Client role if username/password supplied
            $clientUserId = null;
            if (!empty($clientData['create_portal_user'])) {
                $roleId = (int) $db->query("SELECT id FROM roles WHERE slug = 'client' LIMIT 1")->fetchColumn();
                if ($roleId > 0) {
                    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $clientCode));
                    $email = $clientData['email'] ?? ($username . '@client.simda.so');
                    $password = $clientData['portal_password'] ?? 'Client@123';
                    $uStmt = $db->prepare('
                        INSERT INTO users (role_id, branch_id, full_name, username, email, phone, password_hash, is_synthetic_demo)
                        VALUES (:rid, :bid, :name, :uname, :email, :phone, :phash, :demo)
                    ');
                    $uStmt->execute([
                        ':rid' => $roleId,
                        ':bid' => $branchId,
                        ':name' => $fullName,
                        ':uname' => $username,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':phash' => Security::hashPassword((string) $password),
                        ':demo' => (int) ($clientData['is_synthetic_demo'] ?? 0),
                    ]);
                    $clientUserId = (int) $db->lastInsertId();
                }
            }

            $insClient = $db->prepare('
                INSERT INTO clients (
                    client_code, user_id, branch_id, registered_by, full_name,
                    id_type, id_number, phone, email, city, district, address,
                    business_type, monthly_income, photo_path, credit_balance, status, is_synthetic_demo
                ) VALUES (
                    :code, :uid, :bid, :reg_by, :name,
                    :id_type, :id_num, :phone, :email, :city, :district, :address,
                    :biz, :income, :photo, 0.00, \'active\', :demo
                )
            ');
            $insClient->execute([
                ':code' => $clientCode,
                ':uid' => $clientUserId,
                ':bid' => $branchId,
                ':reg_by' => $registeredByUserId,
                ':name' => $fullName,
                ':id_type' => $idType,
                ':id_num' => $idNumber,
                ':phone' => $phone,
                ':email' => $email !== '' ? $email : null,
                ':city' => $city,
                ':district' => $district,
                ':address' => $address,
                ':biz' => $businessType,
                ':income' => $monthlyIncome,
                ':photo' => $photoPath,
                ':demo' => (int) ($clientData['is_synthetic_demo'] ?? 0),
            ]);

            $clientId = (int) $db->lastInsertId();

            $insGuar = $db->prepare('
                INSERT INTO guarantors (client_id, full_name, phone, id_number, relationship, occupation, estimated_income)
                VALUES (:cid, :name, :phone, :id_num, :rel, :occ, :inc)
            ');
            $insGuar->execute([
                ':cid' => $clientId,
                ':name' => $gName,
                ':phone' => $gPhone,
                ':id_num' => $gId,
                ':rel' => $gRel,
                ':occ' => $guarantorData['occupation'] ?? null,
                ':inc' => number_format((float) ($guarantorData['estimated_income'] ?? 0), 2, '.', ''),
            ]);

            CreditScoreEngine::initializeForClient($clientId, $db);

            if ($ownTx) {
                $db->commit();
            }

            AuditLogger::log(
                'client.registered',
                'clients',
                $clientId,
                "Registered client {$fullName} ({$clientCode}) with guarantor {$gName}.",
                ['client_code' => $clientCode, 'branch_id' => $branchId],
                $registeredByUserId,
                $branchId
            );

            return [
                'client_id' => $clientId,
                'client_code' => $clientCode,
                'user_id' => $clientUserId,
            ];
        } catch (\Throwable $e) {
            if ($ownTx && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Module 1: Only Admin can transfer a client between branches.
     */
    public static function transferClientBranch(int $clientId, int $newBranchId, array $actingUser, ?PDO $pdo = null): void
    {
        if (($actingUser['role_slug'] ?? '') !== 'admin') {
            throw new RuntimeException('Only an Administrator can transfer a client between branches.');
        }

        $db = $pdo ?? Database::connection();
        $stmt = $db->prepare('SELECT id, full_name, branch_id, user_id FROM clients WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $clientId]);
        $client = $stmt->fetch();
        if (!$client) {
            throw new RuntimeException('Client not found.');
        }

        $oldBranchId = (int) $client['branch_id'];
        $db->prepare('UPDATE clients SET branch_id = :nb WHERE id = :id')
            ->execute([':nb' => $newBranchId, ':id' => $clientId]);

        if (!empty($client['user_id'])) {
            $db->prepare('UPDATE users SET branch_id = :nb WHERE id = :uid')
                ->execute([':nb' => $newBranchId, ':uid' => $client['user_id']]);
        }

        AuditLogger::log(
            'client.branch_transferred',
            'clients',
            $clientId,
            "Transferred client {$client['full_name']} from branch #{$oldBranchId} to branch #{$newBranchId}.",
            ['old_branch_id' => $oldBranchId, 'new_branch_id' => $newBranchId],
            (int) $actingUser['id'],
            $newBranchId
        );
    }

    /**
     * Module 2: Delete product only if not in use by any application or financing.
     */
    public static function deleteProduct(int $productId, int $adminUserId, ?PDO $pdo = null): void
    {
        $db = $pdo ?? Database::connection();
        $appCountStmt = $db->prepare('SELECT COUNT(*) FROM financing_applications WHERE product_id = :pid');
        $appCountStmt->execute([':pid' => $productId]);
        $appCount = (int) $appCountStmt->fetchColumn();

        $finCountStmt = $db->prepare('SELECT COUNT(*) FROM financings WHERE product_id = :pid');
        $finCountStmt->execute([':pid' => $productId]);
        $finCount = (int) $finCountStmt->fetchColumn();

        if ($appCount > 0 || $finCount > 0) {
            throw new RuntimeException('Products currently in use cannot be deleted. Deactivate the product instead.');
        }

        $db->prepare('DELETE FROM products WHERE id = :pid')->execute([':pid' => $productId]);
        AuditLogger::log('product.deleted', 'products', $productId, "Deleted unused product #{$productId}.", [], $adminUserId);
    }

    /**
     * Who decides an application: Header Office up to the officer limit, Branch Manager up to the
     * branch limit, General Manager above it. Group financing starts at Branch Manager level.
     */
    public static function requiredApproverRole(float $amount, bool $isGroup): string
    {
        if ($amount > (float) Env::get('APPROVAL_BRANCH_LIMIT', 50000.00)) {
            return 'manager';
        }
        if ($isGroup || $amount > (float) Env::get('APPROVAL_OFFICER_LIMIT', 5000.00)) {
            return 'branch_manager';
        }
        return 'loan_officer';
    }

    public static function canDecideLevel(string $reviewerRole, string $requiredRole): bool
    {
        if ($reviewerRole === 'admin') {
            return true;
        }
        $rank = ['loan_officer' => 1, 'branch_manager' => 2, 'manager' => 3];
        return isset($rank[$reviewerRole], $rank[$requiredRole]) && $rank[$reviewerRole] >= $rank[$requiredRole];
    }

    public static function approverLabel(string $role): string
    {
        return match ($role) {
            'manager' => 'General Manager',
            'branch_manager' => 'Branch Manager',
            default => 'Header Office',
        };
    }

    /**
     * Emails the people an application was forwarded to: the branch's managers, or the general managers.
     * Applications that stay at Header Office level send nothing. Mail problems are reported, never thrown,
     * so they cannot undo a submission that is already saved.
     *
     * @return array{required_role: string, recipients: int, failed: int}
     */
    public static function notifyForwardedApplication(int $applicationId, string $locale, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $stmt = $db->prepare('
            SELECT fa.*, c.full_name AS client_name, c.client_code, c.phone AS client_phone, c.id_number AS client_id_number,
                   c.city AS client_city, c.district AS client_district, c.business_type, c.monthly_income,
                   b.name AS branch_name, p.product_type, u.full_name AS creator_name
            FROM financing_applications fa
            INNER JOIN clients c ON c.id = fa.client_id
            INNER JOIN branches b ON b.id = fa.branch_id
            INNER JOIN products p ON p.id = fa.product_id
            INNER JOIN users u ON u.id = fa.created_by
            WHERE fa.id = :id
        ');
        $stmt->execute([':id' => $applicationId]);
        $app = $stmt->fetch();
        if (!$app) {
            throw new RuntimeException('Application not found.');
        }

        $requiredRole = (string) $app['required_approver_role'];
        if ($requiredRole === 'loan_officer' || $app['status'] !== 'submitted') {
            return ['required_role' => $requiredRole, 'recipients' => 0, 'failed' => 0];
        }

        $sql = '
            SELECT u.full_name, u.email
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE r.slug = :role AND u.is_active = 1 AND u.deleted_at IS NULL AND u.id != :creator
        ';
        $params = [':role' => $requiredRole, ':creator' => (int) $app['created_by']];
        if ($requiredRole === 'branch_manager') {
            $sql .= ' AND u.branch_id = :branch';
            $params[':branch'] = (int) $app['branch_id'];
        }
        $recipients = $db->prepare($sql);
        $recipients->execute($params);
        $people = $recipients->fetchAll();

        $failed = 0;
        foreach ($people as $person) {
            $mail = MailService::sendApplicationForwardedEmail((string) $person['email'], (string) $person['full_name'], $app, $locale);
            if (!$mail['success']) {
                $failed++;
            }
        }

        AuditLogger::log(
            'application.forwarded',
            'financing_applications',
            $applicationId,
            "Application {$app['application_number']} forwarded to " . self::approverLabel($requiredRole) . '.',
            ['required_role' => $requiredRole, 'recipients' => count($people), 'mail_failed' => $failed],
            (int) $app['created_by'],
            (int) $app['branch_id']
        );

        return ['required_role' => $requiredRole, 'recipients' => count($people), 'failed' => $failed];
    }

    /**
     * Module 3: Create or submit a Financing Application.
     *
     * Enforces:
     * - Client must have at least one guarantor.
     * - Product must be active.
     * - Amount must be within product [min_amount, max_amount].
     * - Term must be within product [min_term, max_term].
     * - Client > 30 days late on active financing cannot apply.
     * - If part of a Group (`group_id`), if ANY member of the group is > 30 days late, no member can receive new financing.
     * - Approval level: see requiredApproverRole().
     */
    public static function createApplication(
        array $data,
        int $createdByUserId,
        bool $submitImmediately = true,
        ?string $referenceDate = null,
        ?PDO $pdo = null
    ): array {
        $db = $pdo ?? Database::connection();
        $refDate = $referenceDate ?? date('Y-m-d');

        $clientId = (int) ($data['client_id'] ?? 0);
        $productId = (int) ($data['product_id'] ?? 0);
        $groupId = !empty($data['group_id']) ? (int) $data['group_id'] : null;
        $amount = (float) ($data['requested_amount'] ?? 0);
        $term = (int) ($data['term_installments'] ?? 0);
        $frequency = strtolower(trim((string) ($data['repayment_frequency'] ?? 'monthly')));
        $purpose = trim((string) ($data['purpose'] ?? ''));
        $murabahaGoods = trim((string) ($data['murabaha_goods_description'] ?? ''));
        $notes = trim((string) ($data['notes'] ?? ''));

        // 1. Verify client exists
        $cliStmt = $db->prepare('SELECT * FROM clients WHERE id = :id LIMIT 1');
        $cliStmt->execute([':id' => $clientId]);
        $client = $cliStmt->fetch();
        if (!$client) {
            throw new RuntimeException('Client not found.');
        }

        // 2. Verify client has at least one guarantor
        $gStmt = $db->prepare('SELECT COUNT(*) FROM guarantors WHERE client_id = :cid');
        $gStmt->execute([':cid' => $clientId]);
        if ((int) $gStmt->fetchColumn() < 1) {
            throw new RuntimeException('A client cannot apply for financing without at least one registered guarantor.');
        }

        // 3. Check if client is > 30 days late on any active financing
        $lateStmt = $db->prepare("
            SELECT COUNT(*) FROM installments i
            INNER JOIN financings f ON f.id = i.financing_id
            WHERE f.client_id = :cid
              AND f.status IN ('active', 'overdue')
              AND i.remaining_balance > 0
              AND i.status != 'paid'
              AND DATEDIFF(:ref_date, i.due_date) > 30
        ");
        $lateStmt->execute([':cid' => $clientId, ':ref_date' => $refDate]);
        if ((int) $lateStmt->fetchColumn() > 0) {
            throw new RuntimeException('Client has an active financing more than 30 days overdue and is ineligible to apply.');
        }

        // 4. If applying under a Group (or if client belongs to an active group), enforce Module 10 group lock rule
        if ($groupId !== null) {
            self::assertGroupEligibleForFinancing($groupId, $refDate, $db);
        }

        // 5. Verify product limits
        $prodStmt = $db->prepare('SELECT * FROM products WHERE id = :pid LIMIT 1');
        $prodStmt->execute([':pid' => $productId]);
        $product = $prodStmt->fetch();
        if (!$product || !(bool) $product['is_active']) {
            throw new RuntimeException('Selected financing product is not available or inactive.');
        }

        if ($amount < (float) $product['min_amount'] || $amount > (float) $product['max_amount']) {
            throw new RuntimeException(sprintf(
                'Financing amount ($%.2f) must be between product limits ($%.2f and $%.2f).',
                $amount,
                (float) $product['min_amount'],
                (float) $product['max_amount']
            ));
        }

        if ($term < (int) $product['min_term'] || $term > (int) $product['max_term']) {
            throw new RuntimeException(sprintf(
                'Financing term (%d) must be between product limits (%d and %d installments).',
                $term,
                (int) $product['min_term'],
                (int) $product['max_term']
            ));
        }

        if ($product['allowed_frequency'] !== 'both' && $product['allowed_frequency'] !== $frequency) {
            throw new RuntimeException("This product only supports {$product['allowed_frequency']} installments.");
        }

        if ($product['product_type'] === 'murabaha' && $murabahaGoods === '') {
            throw new RuntimeException('Murabaha financing requires a description of the underlying goods/assets.');
        }

        $agreedRate = ($product['product_type'] === 'murabaha')
            ? (string) $product['murabaha_markup_pct']
            : (string) $product['annual_profit_rate'];

        $schedule = ScheduleEngine::generate(
            (string) $product['product_type'],
            number_format($amount, 2, '.', ''),
            $agreedRate,
            $term,
            $frequency,
            $refDate
        );

        $requiredRole = self::requiredApproverRole($amount, $groupId !== null);
        $status = $submitImmediately ? 'submitted' : 'draft';
        $submittedAt = $submitImmediately ? date('Y-m-d H:i:s') : null;

        $appNo = 'APP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $ins = $db->prepare('
            INSERT INTO financing_applications (
                application_number, client_id, branch_id, product_id, group_id, created_by,
                requested_amount, term_installments, repayment_frequency, agreed_rate, agreed_admin_fee,
                projected_profit, projected_total_due, purpose, murabaha_goods_description, notes,
                calculation_snapshot, status, required_approver_role, submitted_at
            ) VALUES (
                :app_no, :cid, :bid, :pid, :gid, :cby,
                :amt, :term, :freq, :rate, :fee,
                :prof, :tot, :purpose, :goods, :notes,
                :snap, :status, :req_role, :sub_at
            )
        ');
        $ins->execute([
            ':app_no' => $appNo,
            ':cid' => $clientId,
            ':bid' => $client['branch_id'],
            ':pid' => $productId,
            ':gid' => $groupId,
            ':cby' => $createdByUserId,
            ':amt' => $schedule['principal'],
            ':term' => $term,
            ':freq' => $frequency,
            ':rate' => $agreedRate,
            ':fee' => $product['admin_fee'],
            ':prof' => $schedule['total_profit'],
            ':tot' => $schedule['total_due'],
            ':purpose' => $purpose,
            ':goods' => $murabahaGoods ?: null,
            ':notes' => $notes ?: null,
            ':snap' => json_encode($schedule, JSON_UNESCAPED_UNICODE),
            ':status' => $status,
            ':req_role' => $requiredRole,
            ':sub_at' => $submittedAt,
        ]);

        $appId = (int) $db->lastInsertId();

        AuditLogger::log(
            'application.' . $status,
            'financing_applications',
            $appId,
            "Created application {$appNo} ({$status}) for \${$schedule['principal']}.",
            ['application_number' => $appNo, 'amount' => $schedule['principal'], 'required_approver_role' => $requiredRole],
            $createdByUserId,
            (int) $client['branch_id']
        );

        return [
            'application_id' => $appId,
            'application_number' => $appNo,
            'status' => $status,
            'required_approver_role' => $requiredRole,
            'schedule' => $schedule,
        ];
    }

    /**
     * Module 4: Approve or Reject an Application.
     *
     * Rules:
     * - Application creators CANNOT approve their own applications.
     * - The amount sets the approval level (see requiredApproverRole()); higher levels and Admin may also decide lower ones.
     * - Header Office and Branch Managers decide only for their own branch.
     * - Every decision is logged in `audit_logs`.
     */
    public static function reviewApplication(
        int $applicationId,
        array $reviewerUser,
        string $decision,
        string $comments = '',
        ?PDO $pdo = null
    ): array {
        $decision = strtolower(trim($decision));
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            throw new RuntimeException('Decision must be either approved or rejected.');
        }

        $db = $pdo ?? Database::connection();
        $stmt = $db->prepare('SELECT * FROM financing_applications WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $applicationId]);
        $app = $stmt->fetch();

        if (!$app) {
            throw new RuntimeException('Application not found.');
        }
        if ($app['status'] !== 'submitted') {
            throw new RuntimeException('Only submitted applications can be reviewed.');
        }

        $reviewerId = (int) $reviewerUser['id'];
        $reviewerRole = (string) $reviewerUser['role_slug'];

        // Rule: Application creators cannot approve their own applications (Admin has full override authority)
        if ($decision === 'approved' && (int) $app['created_by'] === $reviewerId && $reviewerRole !== 'admin') {
            throw new RuntimeException('Conflict of interest protection: Application creators cannot approve their own applications.');
        }

        $requiredRole = self::requiredApproverRole((float) $app['requested_amount'], !empty($app['group_id']));
        if (!self::canDecideLevel($reviewerRole, $requiredRole)) {
            throw new RuntimeException('This application requires ' . self::approverLabel($requiredRole) . ' approval.');
        }
        if (in_array($reviewerRole, ['loan_officer', 'branch_manager'], true)
            && (int) ($reviewerUser['branch_id'] ?? 0) !== (int) $app['branch_id']) {
            throw new RuntimeException('This application belongs to another branch.');
        }

        $expiresAt = ($decision === 'approved')
            ? date('Y-m-d H:i:s', strtotime('+' . (int) Env::get('DISBURSEMENT_EXPIRY_DAYS', 30) . ' days'))
            : null;

        $upd = $db->prepare('
            UPDATE financing_applications
            SET status = :status,
                reviewed_by = :rev_by,
                reviewed_at = NOW(),
                decision_comments = :comments,
                expires_at = :exp
            WHERE id = :id
        ');
        $upd->execute([
            ':status' => $decision,
            ':rev_by' => $reviewerId,
            ':comments' => $comments,
            ':exp' => $expiresAt,
            ':id' => $applicationId,
        ]);

        AuditLogger::log(
            'application.' . $decision,
            'financing_applications',
            $applicationId,
            "Application {$app['application_number']} {$decision} by {$reviewerUser['full_name']}. Comments: {$comments}",
            [
                'application_number' => $app['application_number'],
                'decision' => $decision,
                'amount' => $app['requested_amount'],
                'comments' => $comments,
            ],
            $reviewerId,
            (int) $app['branch_id']
        );

        return [
            'application_id' => $applicationId,
            'status' => $decision,
            'reviewed_by' => $reviewerId,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Module 4: Rejected applications can be edited and resubmitted ONCE.
     */
    public static function resubmitRejectedApplication(
        int $applicationId,
        array $updatedData,
        int $actingUserId,
        ?PDO $pdo = null
    ): array {
        $db = $pdo ?? Database::connection();
        $stmt = $db->prepare('SELECT * FROM financing_applications WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $applicationId]);
        $app = $stmt->fetch();

        if (!$app) {
            throw new RuntimeException('Application not found.');
        }
        if ($app['status'] !== 'rejected') {
            throw new RuntimeException('Only rejected applications can be resubmitted.');
        }
        if ((int) $app['resubmission_count'] >= 1) {
            throw new RuntimeException('Rejected applications can only be edited and resubmitted once.');
        }

        $prodStmt = $db->prepare('SELECT * FROM products WHERE id = :pid LIMIT 1');
        $prodStmt->execute([':pid' => $app['product_id']]);
        $product = $prodStmt->fetch();

        $newAmount = (float) ($updatedData['requested_amount'] ?? $app['requested_amount']);
        $newTerm = (int) ($updatedData['term_installments'] ?? $app['term_installments']);
        $newPurpose = trim((string) ($updatedData['purpose'] ?? $app['purpose']));

        if ($newAmount < (float) $product['min_amount'] || $newAmount > (float) $product['max_amount']) {
            throw new RuntimeException('Updated amount is outside product limits.');
        }

        $schedule = ScheduleEngine::generate(
            (string) $product['product_type'],
            number_format($newAmount, 2, '.', ''),
            (string) $app['agreed_rate'],
            $newTerm,
            (string) $app['repayment_frequency'],
            date('Y-m-d')
        );

        $requiredRole = self::requiredApproverRole($newAmount, !empty($app['group_id']));

        $upd = $db->prepare("
            UPDATE financing_applications
            SET requested_amount = :amt,
                term_installments = :term,
                projected_profit = :prof,
                projected_total_due = :tot,
                purpose = :purpose,
                calculation_snapshot = :snap,
                required_approver_role = :req_role,
                status = 'submitted',
                resubmission_count = resubmission_count + 1,
                submitted_at = NOW()
            WHERE id = :id
        ");
        $upd->execute([
            ':amt' => $schedule['principal'],
            ':term' => $newTerm,
            ':prof' => $schedule['total_profit'],
            ':tot' => $schedule['total_due'],
            ':purpose' => $newPurpose,
            ':snap' => json_encode($schedule, JSON_UNESCAPED_UNICODE),
            ':req_role' => $requiredRole,
            ':id' => $applicationId,
        ]);

        AuditLogger::log(
            'application.resubmitted',
            'financing_applications',
            $applicationId,
            "Resubmitted application {$app['application_number']} (Resubmission #1).",
            ['new_amount' => $schedule['principal'], 'new_term' => $newTerm],
            $actingUserId,
            (int) $app['branch_id']
        );

        return [
            'application_id' => $applicationId,
            'status' => 'submitted',
            'resubmission_count' => (int) $app['resubmission_count'] + 1,
        ];
    }

    /**
     * Module 10: Create a Client Group (3–10 members).
     */
    public static function createGroup(
        string $groupName,
        int $branchId,
        int $leaderClientId,
        array $memberClientIds,
        int $createdByUserId,
        string $notes = '',
        ?PDO $pdo = null
    ): array {
        $uniqueMembers = array_values(array_unique(array_map('intval', array_merge([$leaderClientId], $memberClientIds))));
        $count = count($uniqueMembers);
        if ($count < 3 || $count > 10) {
            throw new RuntimeException('A financing group must contain between 3 and 10 clients.');
        }

        $db = $pdo ?? Database::connection();
        $ownTx = !$db->inTransaction();
        if ($ownTx) {
            $db->beginTransaction();
        }

        try {
            $groupCode = 'GRP-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $ins = $db->prepare("
                INSERT INTO groups (group_code, branch_id, group_name, leader_client_id, status, created_by, notes)
                VALUES (:code, :bid, :name, :leader, 'pending_approval', :cby, :notes)
            ");
            $ins->execute([
                ':code' => $groupCode,
                ':bid' => $branchId,
                ':name' => trim($groupName),
                ':leader' => $leaderClientId,
                ':cby' => $createdByUserId,
                ':notes' => $notes ?: null,
            ]);
            $groupId = (int) $db->lastInsertId();

            $insMem = $db->prepare('
                INSERT INTO group_members (group_id, client_id, role_in_group, is_active)
                VALUES (:gid, :cid, :role, 1)
            ');
            foreach ($uniqueMembers as $cid) {
                $role = ($cid === $leaderClientId) ? 'leader' : 'member';
                $insMem->execute([
                    ':gid' => $groupId,
                    ':cid' => $cid,
                    ':role' => $role,
                ]);
            }

            if ($ownTx) {
                $db->commit();
            }

            AuditLogger::log(
                'group.created',
                'groups',
                $groupId,
                "Created client group {$groupName} ({$groupCode}) with {$count} members.",
                ['group_code' => $groupCode, 'members_count' => $count],
                $createdByUserId,
                $branchId
            );

            return [
                'group_id' => $groupId,
                'group_code' => $groupCode,
                'members_count' => $count,
                'status' => 'pending_approval',
            ];
        } catch (\Throwable $e) {
            if ($ownTx && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Module 10: Branch Manager approves a group.
     */
    public static function approveGroup(int $groupId, array $approverUser, ?PDO $pdo = null): void
    {
        if (!in_array($approverUser['role_slug'] ?? '', ['branch_manager', 'admin'], true)) {
            throw new RuntimeException('Only a Branch Manager or Admin can approve group financing.');
        }
        $db = $pdo ?? Database::connection();
        $db->prepare("
            UPDATE groups
            SET status = 'active', approved_by = :aby, approved_at = NOW()
            WHERE id = :gid
        ")->execute([':aby' => $approverUser['id'], ':gid' => $groupId]);

        AuditLogger::log('group.approved', 'groups', $groupId, "Approved group #{$groupId}.", [], (int) $approverUser['id']);
    }

    /**
     * Module 10: Verify no member of the group is > 30 days overdue.
     */
    public static function assertGroupEligibleForFinancing(int $groupId, ?string $referenceDate = null, ?PDO $pdo = null): void
    {
        $db = $pdo ?? Database::connection();
        $refDate = $referenceDate ?? date('Y-m-d');

        $gStmt = $db->prepare('SELECT * FROM groups WHERE id = :gid LIMIT 1');
        $gStmt->execute([':gid' => $groupId]);
        $group = $gStmt->fetch();
        if (!$group) {
            throw new RuntimeException('Client group not found.');
        }
        if ($group['status'] !== 'active') {
            throw new RuntimeException('Group must be approved by a Branch Manager and active before members receive group financing.');
        }

        $lateStmt = $db->prepare("
            SELECT c.full_name, MAX(DATEDIFF(:ref_date, i.due_date)) AS max_days_late
            FROM group_members gm
            INNER JOIN clients c ON c.id = gm.client_id
            INNER JOIN financings f ON f.client_id = gm.client_id
            INNER JOIN installments i ON i.financing_id = f.id
            WHERE gm.group_id = :gid
              AND gm.is_active = 1
              AND f.status IN ('active', 'overdue')
              AND i.remaining_balance > 0
              AND i.status != 'paid'
              AND DATEDIFF(:ref_date_2, i.due_date) > 30
            GROUP BY c.id
        ");
        $lateStmt->execute([
            ':ref_date' => $refDate,
            ':ref_date_2' => $refDate,
            ':gid' => $groupId,
        ]);
        $lateMember = $lateStmt->fetch();
        if ($lateMember) {
            throw new RuntimeException("Group financing locked: Member {$lateMember['full_name']} is more than 30 days late ({$lateMember['max_days_late']} days). No group member can receive new financing.");
        }
    }

    /**
     * Module 10: Remove a member from a group (blocked if member has active financing).
     */
    public static function removeMemberFromGroup(int $groupId, int $clientId, int $actingUserId, ?PDO $pdo = null): void
    {
        $db = $pdo ?? Database::connection();

        $activeStmt = $db->prepare("
            SELECT COUNT(*) FROM financings
            WHERE client_id = :cid AND status IN ('active', 'overdue') AND outstanding_balance > 0
        ");
        $activeStmt->execute([':cid' => $clientId]);
        if ((int) $activeStmt->fetchColumn() > 0) {
            throw new RuntimeException('Members cannot leave a group while they have active financing.');
        }

        $db->prepare('
            UPDATE group_members
            SET is_active = 0, left_at = NOW()
            WHERE group_id = :gid AND client_id = :cid
        ')->execute([':gid' => $groupId, ':cid' => $clientId]);

        AuditLogger::log('group.member_removed', 'groups', $groupId, "Removed client #{$clientId} from group #{$groupId}.", [], $actingUserId);
    }
}

