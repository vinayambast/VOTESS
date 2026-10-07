<?php
declare(strict_types=1);
require_once __DIR__ . '/_boot.php';
require_once VOTESS_PRIVATE . '/lib/bootstrap.php';
require_once VOTESS_PRIVATE . '/lib/templates.php';

require_post();
$in = input();
if (!empty($in['website_url'])) json_out(['ok' => true, 'ref' => 'VT-OK']);          // honeypot: bots get a fake success

$P = purposes();
$l = [
    'name' => clean($in['name'] ?? '', 100), 'email' => strtolower(clean($in['email'] ?? '', 150)), 'phone' => clean($in['phone'] ?? '', 30),
    'preferred_contact' => clean($in['preferred_contact'] ?? 'email', 20), 'preferred_time' => clean($in['preferred_time'] ?? '', 40),
    'company' => clean($in['company'] ?? '', 150), 'designation' => clean($in['designation'] ?? '', 100), 'website' => clean($in['website'] ?? '', 200),
    'org_size' => clean($in['org_size'] ?? '', 40), 'country' => clean($in['country'] ?? '', 80), 'state' => clean($in['state'] ?? '', 80), 'city' => clean($in['city'] ?? '', 80),
    'purpose' => clean($in['purpose'] ?? '', 30), 'sub_interest' => clean($in['sub_interest'] ?? '', 120), 'budget' => clean($in['budget'] ?? '', 40),
    'timeline' => clean($in['timeline'] ?? '', 40), 'heard_from' => clean($in['heard_from'] ?? '', 60), 'message' => clean($in['message'] ?? '', 3000),
    'consent_privacy' => !empty($in['consent_privacy']) ? 1 : 0, 'consent_marketing' => !empty($in['consent_marketing']) ? 1 : 0,
    'source_url' => clean($in['source_url'] ?? '', 255), 'utm_source' => clean($in['utm_source'] ?? '', 80), 'utm_medium' => clean($in['utm_medium'] ?? '', 80),
    'utm_campaign' => clean($in['utm_campaign'] ?? '', 80), 'ip' => client_ip(), 'user_agent' => clean($_SERVER['HTTP_USER_AGENT'] ?? '', 255),
];
$err = [];
if (mb_strlen($l['name']) < 2) $err['name'] = 'Enter your full name.';
if (!filter_var($l['email'], FILTER_VALIDATE_EMAIL)) $err['email'] = 'Enter a valid email address.';
if (!isset($P[$l['purpose']])) $err['purpose'] = 'Choose why you are contacting us.';
if (mb_strlen($l['message']) < 10) $err['message'] = 'Tell us a little more (at least 10 characters).';
if (!$l['consent_privacy']) $err['consent_privacy'] = 'Please agree to be contacted about this enquiry.';
if (in_array($l['preferred_contact'], ['phone', 'whatsapp'], true) && strlen(preg_replace('/\D/', '', $l['phone']) ?? '') < 8) $err['phone'] = 'Enter a phone number for phone or WhatsApp contact.';
if ($l['phone'] !== '' && !preg_match('/^[+0-9()\-\s]{7,30}$/', $l['phone'])) $err['phone'] = 'Enter a valid phone number.';
if ($err) json_out(['ok' => false, 'errors' => $err], 422);

$l['ref'] = new_ref('VT-');
$stored = false;
try {
    $pdo = db();
    $limit = (int)env('RATE_LIMIT_PER_HOUR', '5');
    $q = $pdo->prepare('SELECT COUNT(*) FROM leads WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
    $q->execute([$l['ip']]);
    if ((int)$q->fetchColumn() >= $limit) json_out(['ok' => false, 'error' => 'Too many submissions. Please try again later.'], 429);
    $cols = ['ref','name','email','phone','preferred_contact','preferred_time','company','designation','website','org_size','country','state','city','purpose','sub_interest','budget','timeline','heard_from','message','consent_privacy','consent_marketing','source_url','utm_source','utm_medium','utm_campaign','ip','user_agent'];
    $st = $pdo->prepare('INSERT INTO leads (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
    $st->execute(array_map(fn($c) => $l[$c], $cols));
    $stored = true;
} catch (\Throwable $e) {
    log_err('Lead DB error: ' . $e->getMessage());   // keep going: the admin email still carries the lead
}

[$sa, $ba] = lead_admin_email($l);
$adminOk = send_mail(admin_emails(), $sa, $ba, $l['email'], $l['name']);
[$su, $bu] = lead_user_email($l);
$userOk = send_mail([$l['email']], $su, $bu, (string)env('REPLY_TO', env('MAIL_FROM')), 'VOTESS');

if ($stored) {
    try { db()->prepare('UPDATE leads SET admin_mail_sent = ?, user_mail_sent = ? WHERE ref = ?')->execute([(int)$adminOk, (int)$userOk, $l['ref']]); } catch (\Throwable $e) { log_err($e->getMessage()); }
}
unset($l['user_agent']);
forward_webhook('lead.created', $l);

if (!$stored && !$adminOk) json_out(['ok' => false, 'error' => 'We could not save your message. Please email us directly.'], 500);
json_out(['ok' => true, 'ref' => $l['ref']]);
