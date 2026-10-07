<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates.php';

function pay_live(): bool { return strtolower((string)env('PAY_ENV', 'test')) === 'live'; }

function enabled_gateways(): array {
    $g = [];
    if (env('RAZORPAY_KEY_ID') && env('RAZORPAY_KEY_SECRET')) $g[] = 'razorpay';
    if (env('CASHFREE_APP_ID') && env('CASHFREE_SECRET')) $g[] = 'cashfree';
    if (env('PAYU_KEY') && env('PAYU_SALT')) $g[] = 'payu';
    if (env('EASEBUZZ_KEY') && env('EASEBUZZ_SALT')) $g[] = 'easebuzz';
    if (env('UPI_VPA')) $g[] = 'upi';
    return $g;
}

/** PayU and Easebuzz request hash: key|txnid|amount|productinfo|firstname|email|udf1..udf10|salt */
function req_hash(string $key, string $txn, string $amt, string $info, string $first, string $email, string $salt): string {
    return hash('sha512', implode('|', array_merge([$key, $txn, $amt, $info, $first, $email], array_fill(0, 10, ''), [$salt])));
}
/** PayU and Easebuzz response hash: salt|status|udf10..udf1|email|firstname|productinfo|amount|txnid|key */
function resp_hash(string $salt, string $status, string $email, string $first, string $info, string $amt, string $txn, string $key): string {
    return hash('sha512', implode('|', array_merge([$salt, $status], array_fill(0, 10, ''), [$email, $first, $info, $amt, $txn, $key])));
}

function payment_by_ref(string $ref): ?array {
    $s = db()->prepare('SELECT * FROM payments WHERE order_ref = ?'); $s->execute([$ref]);
    return $s->fetch() ?: null;
}
function payment_by_gateway_order(string $gw, string $oid): ?array {
    $s = db()->prepare('SELECT * FROM payments WHERE gateway = ? AND gateway_order_id = ?'); $s->execute([$gw, $oid]);
    return $s->fetch() ?: null;
}

/** Idempotent status change. Receipt and admin emails go out only on the first transition to "paid". */
function mark_payment(string $ref, string $status, ?string $gwPaymentId, $raw): void {
    $pdo = db();
    $u = $pdo->prepare("UPDATE payments SET status = ?, gateway_payment_id = COALESCE(?, gateway_payment_id), raw_response = ? WHERE order_ref = ? AND status <> 'paid'");
    $u->execute([$status, $gwPaymentId, is_string($raw) ? $raw : json_encode($raw), $ref]);
    if ($u->rowCount() < 1) return;
    $p = payment_by_ref($ref);
    if (!$p) return;
    if ($status === 'paid') {
        [$s, $b] = payment_receipt_email($p);
        send_mail([$p['email']], $s, $b, (string)env('REPLY_TO', env('MAIL_FROM')), 'VOTESS');
        [$s, $b] = payment_admin_email($p, 'Payment received');
        send_mail(admin_emails(), $s, $b, $p['email'], $p['name']);
        forward_webhook('payment.paid', $p);
    } elseif ($status === 'pending_verification') {
        [$s, $b] = payment_admin_email($p, 'UPI payment awaiting your verification');
        send_mail(admin_emails(), $s, $b, $p['email'], $p['name']);
    }
}
