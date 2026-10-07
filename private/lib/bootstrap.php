<?php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

/* ---------- .env ---------- */
function load_env(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    foreach ([__DIR__ . '/../.env', __DIR__ . '/../../.env'] as $f) {   // private/.env  or  <domain root>/.env
        if (!is_file($f)) continue;
        foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
            $l = trim($l);
            if ($l === '' || $l[0] === '#' || !str_contains($l, '=')) continue;
            [$k, $v] = explode('=', $l, 2);
            $k = trim($k); $v = trim($v);
            if (strlen($v) > 1 && ($v[0] === '"' || $v[0] === "'") && substr($v, -1) === $v[0]) $v = substr($v, 1, -1);
            $_ENV[$k] = $v;
        }
        break;
    }
}
function env(string $k, ?string $d = null): ?string {
    load_env();
    if (isset($_ENV[$k]) && $_ENV[$k] !== '') return $_ENV[$k];
    $g = getenv($k);
    return ($g !== false && $g !== '') ? $g : $d;
}
function site_url(): string { return rtrim((string)env('SITE_URL', 'https://example.com'), '/'); }

/* ---------- helpers ---------- */
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function log_err(string $m): void {
    @file_put_contents(__DIR__ . '/../logs/app.log', '[' . date('c') . '] ' . $m . "\n", FILE_APPEND | LOCK_EX);
}
function json_out(array $d, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}
function client_ip(): string {
    if (env('TRUST_PROXY') === '1' && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}
function input(): array {
    $raw = file_get_contents('php://input');
    if ($raw !== '' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json')) {
        $j = json_decode($raw, true);
        return is_array($j) ? $j : [];
    }
    return $_POST;
}
function clean($v, int $max = 255): string {
    $v = trim((string)$v);
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '';
    return mb_substr($v, 0, $max);
}
function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_out(['ok' => false, 'error' => 'POST only'], 405);
    $o = $_SERVER['HTTP_ORIGIN'] ?? '';
    $norm = fn($u) => preg_replace('/^www\./', '', (string)parse_url((string)$u, PHP_URL_HOST));   // www and non-www both allowed
    if ($o !== '' && $norm($o) !== $norm(site_url())) {
        json_out(['ok' => false, 'error' => 'Origin not allowed'], 403);
    }
}
function new_ref(string $prefix): string { return $prefix . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))); }

/* ---------- database (Hostinger MySQL, or AWS RDS / any MySQL via DB_HOST) ---------- */
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = 'mysql:host=' . env('DB_HOST', 'localhost') . ';port=' . env('DB_PORT', '3306') . ';dbname=' . env('DB_NAME') . ';charset=utf8mb4';
    $o = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => 8];
    if (env('DB_SSL_CA')) $o[PDO::MYSQL_ATTR_SSL_CA] = env('DB_SSL_CA');   // path to RDS CA bundle
    return $pdo = new PDO($dsn, (string)env('DB_USER'), (string)env('DB_PASS'), $o);
}

/* ---------- mail: SMTP (PHPMailer) and/or EmailJS ---------- */
function assets_dir(): string {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : '';
    $candidates = [
        env('ASSETS_DIR'),
        __DIR__ . '/../../assets',
        __DIR__ . '/../../public_html/assets',
        __DIR__ . '/../../../public_html/assets',
        $docRoot ? $docRoot . '/assets' : null,
    ];
    foreach ($candidates as $d) {
        if (!$d) continue;
        $d = rtrim($d, '/\\');
        if (is_dir($d) && (is_file($d . '/email-banner.png') || is_file($d . '/logo.png') || is_file($d . '/logo.jpg') || is_file($d . '/logo-dark.png'))) {
            return $d . '/';
        }
    }
    if ($docRoot && is_dir($docRoot . '/assets')) return $docRoot . '/assets/';
    return __DIR__ . '/../../assets/';
}

function smtp_send(array $to, string $subject, string $html, string $alt, ?string $replyTo, ?string $replyName): bool {
    $m = new PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host = (string)env('SMTP_HOST');
        $m->Port = (int)env('SMTP_PORT', '465');
        $m->SMTPAuth = true;
        $m->Username = (string)env('SMTP_USER');
        $m->Password = (string)env('SMTP_PASS');
        $m->SMTPSecure = strtolower((string)env('SMTP_SECURE', 'ssl')) === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $m->CharSet = 'UTF-8';
        $m->Timeout = 15;
        $m->setFrom((string)env('MAIL_FROM'), (string)env('MAIL_FROM_NAME', 'VOTESS'));
        foreach ($to as $a) $m->addAddress($a);
        if ($replyTo) $m->addReplyTo($replyTo, (string)$replyName);
        $m->isHTML(true);
        $m->Subject = $subject;

        $d = assets_dir();
        $attachedLogo = false;
        $attachedBanner = false;

        // Attach Logo (prefer PNG, then JPG)
        foreach (['logo.png' => 'image/png', 'logo-dark.png' => 'image/png', 'logo.jpg' => 'image/jpeg', 'logo-dark.jpg' => 'image/jpeg'] as $f => $mime) {
            if (is_file($d . $f)) {
                $m->addEmbeddedImage($d . $f, 'logo', 'logo.png', 'base64', $mime);
                $attachedLogo = true;
                break;
            }
        }

        // Attach Banner (prefer PNG, then JPG)
        foreach (['email-banner.png' => 'image/png', 'email-banner.jpg' => 'image/jpeg', 'banner.png' => 'image/png'] as $f => $mime) {
            if (is_file($d . $f)) {
                $m->addEmbeddedImage($d . $f, 'banner', 'email-banner.png', 'base64', $mime);
                $attachedBanner = true;
                break;
            }
        }

        // Safety fallback: if embedded images could not be loaded from disk, fall back to direct web URL
        if (!$attachedLogo) {
            $html = str_replace('cid:logo', site_url() . '/assets/logo.png', $html);
        }
        if (!$attachedBanner) {
            $html = str_replace('cid:banner', site_url() . '/assets/email-banner.png', $html);
        }

        $m->Body = $html;
        $m->AltBody = $alt;
        $m->send();
        return true;
    } catch (\Throwable $e) {
        log_err('SMTP failed: ' . $e->getMessage());
        return false;
    }
}

function emailjs_send(array $to, string $subject, string $html, ?string $replyTo): bool {
    $html = str_replace(['cid:logo', 'cid:banner'], [site_url() . '/assets/logo.png', site_url() . '/assets/email-banner.png'], $html);
    $payload = [
        'service_id' => env('EMAILJS_SERVICE_ID'), 'template_id' => env('EMAILJS_TEMPLATE_ID'),
        'user_id' => env('EMAILJS_PUBLIC_KEY'), 'accessToken' => env('EMAILJS_PRIVATE_KEY'),
        'template_params' => ['to_email' => implode(',', $to), 'subject' => $subject, 'html_body' => $html,
            'reply_to' => $replyTo ?? '', 'from_name' => env('MAIL_FROM_NAME', 'VOTESS')],
    ];
    [$code, $res] = http_request('POST', 'https://api.emailjs.com/api/v1.0/email/send', json_encode($payload), ['Content-Type: application/json']);
    if ($code !== 200) log_err("EmailJS failed ($code): " . json_encode($res));
    return $code === 200;
}

/** MAIL_DRIVER = smtp | emailjs | smtp_then_emailjs (SMTP first, EmailJS as fallback) */
function send_mail(array $to, string $subject, string $html, ?string $replyTo = null, ?string $replyName = null): bool {
    $to = array_values(array_filter(array_map('trim', $to), fn($a) => filter_var($a, FILTER_VALIDATE_EMAIL)));
    if (!$to) return false;
    $alt = trim(html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/tr|\/h\d)>/i', "\n", $html) ?? $html)));
    $drv = strtolower((string)env('MAIL_DRIVER', 'smtp'));
    $ok = false;
    if ($drv === 'smtp' || $drv === 'smtp_then_emailjs') $ok = smtp_send($to, $subject, $html, $alt, $replyTo, $replyName);
    if (!$ok && ($drv === 'emailjs' || $drv === 'smtp_then_emailjs')) $ok = emailjs_send($to, $subject, $html, $replyTo);
    return $ok;
}
function admin_emails(): array { return array_filter(array_map('trim', explode(',', (string)env('ADMIN_EMAILS', '')))); }

/* ---------- tiny HTTP client ---------- */
function http_request(string $method, string $url, $body = null, array $headers = [], int $timeout = 20): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $r = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($r === false) log_err('HTTP error ' . $url . ': ' . curl_error($ch));
    curl_close($ch);
    $j = json_decode((string)$r, true);
    return [$code, is_array($j) ? $j : (string)$r];
}

/* ---------- optional cloud forwarding (AWS API Gateway/Lambda, Zapier, n8n, any webhook) ---------- */
function forward_webhook(string $event, array $data): void {
    $url = env('LEAD_WEBHOOK_URL');
    if (!$url) return;
    $body = json_encode(['event' => $event, 'data' => $data, 'sent_at' => date('c')]);
    $sig = hash_hmac('sha256', (string)$body, (string)env('LEAD_WEBHOOK_SECRET', ''));
    http_request('POST', $url, $body, ['Content-Type: application/json', 'X-Votess-Signature: ' . $sig], 6);
}

/* ---------- shared lists ---------- */
function purposes(): array {
    return [
        'erp_demo' => 'Nexus ERP: book a demo',
        'travel' => 'GoVacayTrip: travel enquiry or partnership',
        'chat' => 'Ciao Chat: communication / engagement tools',
        'digital' => 'Votess Digital: website, app or digital project',
        'cloud' => 'Votess Cloud / Connect: hosting and integrations',
        'labs' => 'Votess Labs: product or AI idea',
        'partnership' => 'Business partnership or investment',
        'nayibeej' => 'Nayibeej: volunteer, partner or support',
        'careers' => 'Careers at VOTESS',
        'media' => 'Media or general enquiry',
        'support' => 'Support for an existing product',
        'rfp' => 'Submit an RFP',
    ];
}
