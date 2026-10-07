<?php
declare(strict_types=1);
require_once __DIR__ . '/_boot.php';
// Server-to-server confirmations. Configure these URLs in each gateway dashboard (see README).
require_once VOTESS_PRIVATE . '/lib/payments.php';
$gw = $_GET['gateway'] ?? '';
$raw = file_get_contents('php://input') ?: '';
$j = json_decode($raw, true) ?: [];
try {
    if ($gw === 'razorpay') {
        $sig = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
        if (!hash_equals(hash_hmac('sha256', $raw, (string)env('RAZORPAY_WEBHOOK_SECRET')), $sig)) { http_response_code(401); exit('bad signature'); }
        $pay = $j['payload']['payment']['entity'] ?? [];
        if (in_array($j['event'] ?? '', ['payment.captured', 'order.paid'], true) && !empty($pay['order_id'])) {
            $p = payment_by_gateway_order('razorpay', $pay['order_id']);
            if ($p) mark_payment($p['order_ref'], 'paid', $pay['id'] ?? null, $j);
        }
    } elseif ($gw === 'cashfree') {
        $ts = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? ''; $sig = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
        if (!hash_equals(base64_encode(hash_hmac('sha256', $ts . $raw, (string)env('CASHFREE_SECRET'), true)), $sig)) { http_response_code(401); exit('bad signature'); }
        $ref = $j['data']['order']['order_id'] ?? '';
        if (($j['type'] ?? '') === 'PAYMENT_SUCCESS_WEBHOOK' && $ref) mark_payment($ref, 'paid', (string)($j['data']['payment']['cf_payment_id'] ?? ''), $j);
        if (($j['type'] ?? '') === 'PAYMENT_FAILED_WEBHOOK' && $ref) mark_payment($ref, 'failed', null, $j);
    }
} catch (\Throwable $e) { log_err('webhook: ' . $e->getMessage()); http_response_code(500); exit('error'); }
http_response_code(200);
echo 'ok';
