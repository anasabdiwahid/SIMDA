<?php
declare(strict_types=1);

/**
 * Root entry proxy to public/index.php so SIMDA runs seamlessly whether
 * Apache DocumentRoot points to c:/xampp/htdocs or c:/xampp/htdocs/SIMDA/public.
 */
require_once __DIR__ . '/public/index.php';

