<?php
declare(strict_types=1);
require_once __DIR__ . '/_boot.php';
require_once VOTESS_PRIVATE . '/lib/payments.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') json_out(['ok' => true, 'gateways' => enabled_gateways(), 'live' => pay_live()]);
require_post();
$in = input();
$gw = clean($in['gateway'] ?? '', 20);
if (!in_array($gw, enabled_gateways(), true)) json_out(['ok' => false, 'error' => 'This payment method is not available.'], 400);
$name = clean($in['name'] ?? '', 100); $email = strtolower(clean($in['email'] ?? '', 150));
$phone = preg_replace('/\D/', '', clean($in['phone'] ?? '', 30)) ?? ''; $phone = substr($phone, -10);
$amt = round((float)($in['amount'] ?? 0), 2);
$purpose = preg_replace('/[^A-Za-z0-9 .,#\/-]/', '', clean($in['purpose'] ?? '', 100)) ?? '';
$invoice = clean($in['invoice_no'] ?? '', 40);
$max = (float)env('PAY_MAX', '500000');
$err = [];
if (mb_strlen($name) < 2) $err['name'] = 'Enter your name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err['email'] = 'Enter a valid email.';
if (strlen($phone) !== 10) $err['phone'] = 'Enter a 10-digit mobile number.';
if ($amt < 1 || $amt > $max) $err['amount'] = 'Enter an amount between 1 and ' . $max . '.';
if ($purpose === '') $err['purpose'] = 'Tell us what this payment is for.';
if ($err) json_out(['ok' => false, 'errors' => $err], 422);

$ref = new_ref('VP-');
$first = explode(' ', $name)[0];
$amtStr = number_format($amt, 2, '.', '');
$site = site_url();
try {
    db()->prepare('INSERT INTO payments (order_ref, gateway, amount, name, email, phone, purpose, invoice_no, ip) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([$ref, $gw, $amtStr, $name, $email, $phone, $purpose, $invoice, client_ip()]);
} catch (\Throwable $e) { log_err('pay insert: ' . $e->getMessage()); json_out(['ok' => false, 'error' => 'Could not start the payment.'], 500); }

$setOrder = fn(string $id) => db()->prepare('UPDATE payments SET gateway_order_id = ? WHERE order_ref = ?')->execute([$id, $ref]);
$fail = function (string $why, $res) use ($ref) { log_err("$why: " . json_encode($res)); mark_payment($ref, 'failed', null, $res); json_out(['ok' => false, 'error' => 'The payment provider did not accept the request.'], 502); };

switch ($gw) {
    case 'razorpay':
        $k = (string)env('RAZORPAY_KEY_ID');
        [$c, $r] = http_request('POST', 'https://api.razorpay.com/v1/orders', json_encode(['amount' => (int)round($amt * 100), 'currency' => 'INR', 'receipt' => $ref, 'notes' => ['purpose' => $purpose, 'invoice' => $invoice]]),
            ['Content-Type: application/json', 'Authorization: Basic ' . base64_encode($k . ':' . env('RAZORPAY_KEY_SECRET'))]);
        if ($c !== 200 || empty($r['id'])) $fail('razorpay', $r);
        $setOrder($r['id']);
        json_out(['ok' => true, 'gateway' => 'razorpay', 'ref' => $ref, 'key' => $k, 'order_id' => $r['id'], 'amount' => (int)round($amt * 100), 'name' => 'VOTESS', 'description' => $purpose, 'prefill' => ['name' => $name, 'email' => $email, 'contact' => $phone]]);

    case 'cashfree':
        $base = pay_live() ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';
        [$c, $r] = http_request('POST', $base . '/orders', json_encode([
            'order_id' => $ref, 'order_amount' => (float)$amtStr, 'order_currency' => 'INR',
            'customer_details' => ['customer_id' => substr(md5($email), 0, 20), 'customer_name' => $name, 'customer_email' => $email, 'customer_phone' => $phone],
            'order_meta' => ['return_url' => "$site/api/pay_return.php?gateway=cashfree&ref=$ref", 'notify_url' => "$site/api/pay_webhook.php?gateway=cashfree"],
            'order_note' => $purpose]),
            ['Content-Type: application/json', 'x-api-version: 2023-08-01', 'x-client-id: ' . env('CASHFREE_APP_ID'), 'x-client-secret: ' . env('CASHFREE_SECRET')]);
        if ($c !== 200 || empty($r['payment_session_id'])) $fail('cashfree', $r);
        $setOrder($r['cf_order_id'] ?? $ref);
        json_out(['ok' => true, 'gateway' => 'cashfree', 'ref' => $ref, 'session_id' => $r['payment_session_id'], 'mode' => pay_live() ? 'production' : 'sandbox']);

    case 'payu':
        $key = (string)env('PAYU_KEY');
        $info = $purpose ?: 'VOTESS payment';
        $url = pay_live() ? 'https://secure.payu.in/_payment' : 'https://test.payu.in/_payment';
        $fields = ['key' => $key, 'txnid' => $ref, 'amount' => $amtStr, 'productinfo' => $info, 'firstname' => $first, 'email' => $email, 'phone' => $phone,
            'surl' => "$site/api/pay_return.php?gateway=payu", 'furl' => "$site/api/pay_return.php?gateway=payu",
            'hash' => req_hash($key, $ref, $amtStr, $info, $first, $email, (string)env('PAYU_SALT'))];
        $setOrder($ref);
        json_out(['ok' => true, 'gateway' => 'payu', 'ref' => $ref, 'action' => $url, 'fields' => $fields]);

    case 'easebuzz':
        $key = (string)env('EASEBUZZ_KEY');
        $info = $purpose ?: 'VOTESS payment';
        $base = pay_live() ? 'https://pay.easebuzz.in' : 'https://testpay.easebuzz.in';
        $f = ['key' => $key, 'txnid' => $ref, 'amount' => $amtStr, 'productinfo' => $info, 'firstname' => $first, 'phone' => $phone, 'email' => $email,
            'surl' => "$site/api/pay_return.php?gateway=easebuzz", 'furl' => "$site/api/pay_return.php?gateway=easebuzz",
            'hash' => req_hash($key, $ref, $amtStr, $info, $first, $email, (string)env('EASEBUZZ_SALT'))];
        [$c, $r] = http_request('POST', $base . '/payment/initiateLink', http_build_query($f), ['Content-Type: application/x-www-form-urlencoded']);
        if (!is_array($r) || (int)($r['status'] ?? 0) !== 1 || empty($r['data'])) $fail('easebuzz', $r);
        $setOrder($ref);
        json_out(['ok' => true, 'gateway' => 'easebuzz', 'ref' => $ref, 'redirect' => $base . '/pay/' . $r['data']]);

    case 'upi':
        $vpa = (string)env('UPI_VPA');
        $link = 'upi://pay?' . http_build_query(['pa' => $vpa, 'pn' => env('UPI_PAYEE_NAME', 'VOTESS'), 'am' => $amtStr, 'cu' => 'INR', 'tn' => 'VOTESS ' . $ref, 'tr' => $ref], '', '&', PHP_QUERY_RFC3986);
        json_out(['ok' => true, 'gateway' => 'upi', 'ref' => $ref, 'link' => $link, 'vpa' => $vpa, 'amount' => $amtStr, 'qr_image' => env('UPI_QR_IMAGE')]);
}
