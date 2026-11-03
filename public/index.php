<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Controllers\PortalController;
use App\Helpers\Lang;
use App\Helpers\Security;

Security::startSecureSession();
Lang::init($_GET['lang'] ?? null);

$route = (string) ($_GET['route'] ?? 'home');
PortalController::dispatch($route);

