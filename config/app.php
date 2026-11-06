<?php
declare(strict_types=1);

use App\Helpers\Env;

return [
    'name' => Env::get('APP_NAME', 'SIMDA'),
    'full_name' => Env::get('APP_FULL_NAME', 'Somali Islamic Microfinance Development Association'),
    'tagline' => Env::get('APP_TAGLINE', 'Building Trust. Empowering Growth.'),
    'env' => Env::get('APP_ENV', 'development'),
    'debug' => filter_var(Env::get('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) Env::get('APP_URL', 'http://localhost/SIMDA'), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'Africa/Mogadishu'),
    'default_locale' => Env::get('DEFAULT_LOCALE', 'en'),
    'supported_locales' => ['so', 'en'],
    'session_lifetime' => (int) Env::get('SESSION_LIFETIME', 3600),
    'login_max_attempts' => (int) Env::get('LOGIN_MAX_ATTEMPTS', 5),
    'login_lockout_minutes' => (int) Env::get('LOGIN_LOCKOUT_MINUTES', 15),
    'approval_officer_limit' => (string) Env::get('APPROVAL_OFFICER_LIMIT', '5000.00'),
    'approval_branch_limit' => (string) Env::get('APPROVAL_BRANCH_LIMIT', '50000.00'),
    'late_charge_grace_days' => (int) Env::get('LATE_CHARGE_GRACE_DAYS', 7),
    'default_charity_late_fee' => (string) Env::get('DEFAULT_CHARITY_LATE_FEE', '15.00'),
    'credit_score_initial' => (int) Env::get('CREDIT_SCORE_INITIAL', 600),
    'credit_score_min' => (int) Env::get('CREDIT_SCORE_MIN', 300),
    'credit_score_max' => (int) Env::get('CREDIT_SCORE_MAX', 850),
    'disbursement_expiry_days' => (int) Env::get('DISBURSEMENT_EXPIRY_DAYS', 30),
];

