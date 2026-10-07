<?php
declare(strict_types=1);
// Setup check. Open  /api/health.php  in the browser. DELETE THIS FILE once everything works.
//   ?smtp=1                  test the SMTP login
//   ?test_mail=you@mail.com  send a real test email (needs HEALTH_KEY in .env, then add &key=...)
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
$r = ['php' => PHP_VERSION, 'php_8_1_or_newer' => PHP_VERSION_ID >= 80100];
foreach (['pdo_mysql', 'curl', 'mbstring', 'openssl'] as $x) $r['ext_' . $x] = extension_loaded($x);
$p = null;
foreach ([__DIR__ . '/../../private', __DIR__ . '/../private', __DIR__ . '/../../../private'] as $d) if (is_file($d . '/lib/bootstrap.php')) { $p = realpath($d); break; }
$r['private_folder_found'] = (bool)$p;
if ($p && PHP_VERSION_ID >= 80100) {
    try {
        require_once $p . '/lib/bootstrap.php';
        $r['env_file_found'] = is_file($p . '/.env') || is_file($p . '/../.env');
        $site = parse_url(site_url(), PHP_URL_HOST) ?: '';
        $now = $_SERVER['HTTP_HOST'] ?? '';
        $r['site_url'] = site_url();
        $r['site_url_matches_this_domain'] = preg_replace('/^www\./', '', $site) === preg_replace('/^www\./', '', $now);
        $r['logs_writable'] = is_writable($p . '/logs');
        $r['mail_driver'] = env('MAIL_DRIVER', 'smtp');
        $r['admin_emails_set'] = count(admin_emails()) > 0;
        $r['smtp_configured'] = (bool)(env('SMTP_HOST') && env('SMTP_USER') && env('SMTP_PASS') && env('MAIL_FROM'));
        try { db()->query('SELECT 1'); $r['db_connected'] = true;
              $r['table_leads'] = (bool)db()->query("SHOW TABLES LIKE 'leads'")->fetch();
              $r['table_payments'] = (bool)db()->query("SHOW TABLES LIKE 'payments'")->fetch();
        } catch (Throwable $e) { $r['db_connected'] = false; $r['db_error_code'] = (string)$e->getCode(); }
        $dAssets = assets_dir();
        $r['assets_dir'] = $dAssets;
        $r['logo_found'] = is_file($dAssets . 'logo.png') || is_file($dAssets . 'logo-dark.png') || is_file($dAssets . 'logo.jpg');
        $r['banner_found'] = is_file($dAssets . 'email-banner.png') || is_file($dAssets . 'email-banner.jpg');
        $key = env('HEALTH_KEY');
        $authed = !$key || hash_equals((string)$key, (string)($_GET['key'] ?? ''));
        if (isset($_GET['smtp']) && $authed) {
            $m = new PHPMailer\PHPMailer\PHPMailer(true);
            $m->isSMTP(); $m->Host = (string)env('SMTP_HOST'); $m->Port = (int)env('SMTP_PORT', '465'); $m->SMTPAuth = true;
            $m->Username = (string)env('SMTP_USER'); $m->Password = (string)env('SMTP_PASS'); $m->Timeout = 10;
            $m->SMTPSecure = strtolower((string)env('SMTP_SECURE', 'ssl')) === 'tls' ? 'tls' : 'ssl';
            try { $r['smtp_login_ok'] = $m->smtpConnect(); $m->smtpClose(); } catch (Throwable $e) { $r['smtp_login_ok'] = false; $r['smtp_error'] = substr($e->getMessage(), 0, 160); }
        }
        if (!empty($_GET['test_mail']) && $authed) {
            require_once $p . '/lib/templates.php';
            $sampleInner = '<p style="font-size:15px;line-height:1.65;margin:0 0 14px">Hello,</p><p style="font-size:15px;line-height:1.65;margin:0 0 14px">This is a test email confirming that your SMTP connection, logo, and brand banner are rendering properly.</p>';
            $testHtml = email_layout('VOTESS Template Verification', $sampleInner, 'Testing logo and banner delivery');
            $r['test_mail_sent'] = send_mail([(string)$_GET['test_mail']], 'VOTESS Test: Logo & Banner Verification', $testHtml);
        }
        $r['payment_gateways'] = function_exists('enabled_gateways') ? enabled_gateways() : 'open pay page to check';
    } catch (Throwable $e) { $r['error'] = substr($e->getMessage(), 0, 200); }
}
echo json_encode($r, JSON_PRETTY_PRINT);
