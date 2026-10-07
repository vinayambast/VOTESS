<?php
declare(strict_types=1);
// Shared start-up for every API file: always answers in JSON, finds the private folder, logs fatal errors.
ini_set('display_errors', '0');
function votess_fail(int $code, string $msg): void {
    if (!headers_sent()) { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); }
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}
if (PHP_VERSION_ID < 80100) votess_fail(500, 'Server setup: PHP 8.1 or newer is required (this server runs ' . PHP_VERSION . '). Change it in hPanel > Advanced > PHP Configuration.');
$votess_private = null;
foreach ([__DIR__ . '/../../private', __DIR__ . '/../private', __DIR__ . '/../../../private'] as $d) {
    if (is_file($d . '/lib/bootstrap.php')) { $votess_private = realpath($d); break; }
}
if (!$votess_private) votess_fail(500, 'Server setup: the "private" folder was not found next to public_html. See README step 2.');
define('VOTESS_PRIVATE', $votess_private);
if (!extension_loaded('mbstring')) votess_fail(500, 'Server setup: the PHP extension mbstring is not enabled (hPanel > Advanced > PHP Configuration > Extensions).');
function votess_log(string $m): void { @file_put_contents(VOTESS_PRIVATE . '/logs/app.log', '[' . date('c') . '] ' . $m . "\n", FILE_APPEND | LOCK_EX); }
set_exception_handler(function (Throwable $e) {
    votess_log('Uncaught: ' . $e->getMessage() . ' @' . basename($e->getFile()) . ':' . $e->getLine());
    votess_fail(500, 'Server error. Please email us directly.');
});
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        votess_log('Fatal: ' . $e['message'] . ' @' . basename($e['file']) . ':' . $e['line']);
        votess_fail(500, 'Server error. Please email us directly.');
    }
});
