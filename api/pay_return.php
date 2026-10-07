<?php
declare(strict_types=1);
require_once __DIR__ . '/_boot.php';
// Customer is redirected here by Cashfree, PayU and Easebuzz. We verify before trusting anything.
require_once VOTESS_PRIVATE . '/lib/payments.php';
$gw = $_GET['gateway'] ?? '';
$ref = '';
$status = 'failed';
try {
    if ($gw === 'cashfree') {
        $ref = clean($_GET['ref'] ?? '', 24);
        $base = pay_live() ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';
        [, $r] = http_request('GET', "$base/orders/" . rawurlencode($ref), null, ['x-api-version: 2023-08-01', 'x-client-id: ' . env('CASHFREE_APP_ID'), 'x-client-secret: ' . env('CASHFREE_SECRET')]);
        if (is_array($r) && ($r['order_status'] ?? '') === 'PAID') { $status = 'paid'; mark_payment($ref, 'paid', (string)($r['cf_order_id'] ?? ''), $r); }
    } elseif ($gw === 'payu' || $gw === 'easebuzz') {
        $p = $_POST;
        $ref = clean($p['txnid'] ?? '', 24);
        $key = (string)env($gw === 'payu' ? 'PAYU_KEY' : 'EASEBUZZ_KEY'); $salt = (string)env($gw === 'payu' ? 'PAYU_SALT' : 'EASEBUZZ_SALT');
        $calc = resp_hash($salt, (string)($p['status'] ?? ''), (string)($p['email'] ?? ''), (string)($p['firstname'] ?? ''), (string)($p['productinfo'] ?? ''), (string)($p['amount'] ?? ''), $ref, $key);
        $row = payment_by_ref($ref);
        $valid = hash_equals($calc, strtolower((string)($p['hash'] ?? ''))) && $row && abs((float)$row['amount'] - (float)($p['amount'] ?? 0)) < 0.01;
        if ($valid && ($p['status'] ?? '') === 'success') {
            $status = 'paid';
            mark_payment($ref, 'paid', (string)($p['mihpayid'] ?? $p['easepayid'] ?? ''), $p);
        } elseif ($valid) {
            mark_payment($ref, 'failed', null, $p);
        } else { log_err("pay_return hash mismatch ($gw) for $ref"); }
    }
} catch (\Throwable $e) { log_err('pay_return: ' . $e->getMessage()); }
header('Location: ' . site_url() . '/pay.html?status=' . $status . '&ref=' . rawurlencode($ref));
exit;
