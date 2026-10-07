<?php
declare(strict_types=1);
require_once __DIR__ . '/_boot.php';
require_once VOTESS_PRIVATE . '/lib/payments.php';
require_post();
$in = input();
$action = clean($in['action'] ?? '', 20);

if ($action === 'razorpay') {                       // browser success callback: verify signature server-side
    $oid = clean($in['razorpay_order_id'] ?? '', 60); $pid = clean($in['razorpay_payment_id'] ?? '', 60); $sig = clean($in['razorpay_signature'] ?? '', 128);
    $ok = hash_equals(hash_hmac('sha256', $oid . '|' . $pid, (string)env('RAZORPAY_KEY_SECRET')), $sig);
    $p = $ok ? payment_by_gateway_order('razorpay', $oid) : null;
    if (!$p) json_out(['ok' => false, 'error' => 'Payment could not be verified.'], 400);
    mark_payment($p['order_ref'], 'paid', $pid, $in);
    json_out(['ok' => true, 'ref' => $p['order_ref'], 'status' => 'paid']);
}
if ($action === 'upi_utr') {                        // UPI is peer-to-peer: payer submits UTR, admin confirms in the bank app
    $ref = clean($in['ref'] ?? '', 24); $utr = preg_replace('/\D/', '', clean($in['utr'] ?? '', 30)) ?? '';
    $p = payment_by_ref($ref);
    if (!$p || $p['gateway'] !== 'upi' || strlen($utr) !== 12) json_out(['ok' => false, 'error' => 'Enter the 12-digit UTR / reference from your UPI app.'], 422);
    db()->prepare("UPDATE payments SET utr = ? WHERE order_ref = ? AND status <> 'paid'")->execute([$utr, $ref]);
    mark_payment($ref, 'pending_verification', null, ['utr' => $utr]);
    json_out(['ok' => true, 'ref' => $ref, 'status' => 'pending_verification']);
}
if ($action === 'status') {
    $p = payment_by_ref(clean($in['ref'] ?? '', 24));
    json_out(['ok' => (bool)$p, 'status' => $p['status'] ?? null]);
}
json_out(['ok' => false, 'error' => 'Unknown action'], 400);
