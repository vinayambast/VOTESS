<?php
declare(strict_types=1);

function email_layout(string $title, string $inner, string $preheader = ''): string {
    $site = h(site_url());
    return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#F2F2F2;font-family:Arial,Helvetica,sans-serif;color:#000">
<span style="display:none;max-height:0;overflow:hidden">' . h($preheader) . '</span>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2F2F2"><tr><td align="center" style="padding:24px 12px">
<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;width:100%;background:#fff">
<tr><td style="padding:20px 28px;border-bottom:4px solid #86BC25;background:#ffffff"><a href="' . $site . '"><img src="cid:logo" alt="VOTESS" width="168" style="display:block;width:168px;max-width:168px;height:auto;border:0;outline:none;text-decoration:none"></a></td></tr>
<tr><td style="background:#0b0d0c;line-height:0"><img src="cid:banner" alt="VOTESS: one technology backbone for many brands" width="640" style="display:block;width:100%;max-width:640px;height:auto;border:0;outline:none;text-decoration:none"></td></tr>
<tr><td style="padding:28px"><h1 style="margin:0 0 16px;font-size:22px;line-height:1.3">' . h($title) . '</h1>' . $inner . '</td></tr>
<tr><td style="background:#000;color:#BBBCBC;padding:20px 28px;font-size:12px;line-height:1.6">
<b style="color:#fff">VOTESS</b> &middot; Virtual Operating Technology &amp; Enterprise Systems<br>
<a href="' . $site . '" style="color:#86BC25">' . $site . '</a><br>
Nayibeej is an independent non-profit supported by VOTESS. It is not a VOTESS business.</td></tr>
</table></td></tr></table></body></html>';
}

function kv_table(string $heading, array $rows): string {
    $o = '<h3 style="margin:22px 0 8px;font-size:13px;letter-spacing:.04em;color:#53565A;border-bottom:1px solid #D0D0CE;padding-bottom:6px">' . h($heading) . '</h3><table role="presentation" width="100%" cellpadding="0" cellspacing="0">';
    foreach ($rows as $k => $v) {
        if ($v === '' || $v === null) continue;
        $o .= '<tr><td style="padding:6px 12px 6px 0;width:34%;font-size:13px;color:#53565A;vertical-align:top">' . h($k) . '</td><td style="padding:6px 0;font-size:14px;vertical-align:top">' . $v . '</td></tr>';
    }
    return $o . '</table>';
}
function btn(string $label, string $href, bool $dark = false): string {
    return '<a href="' . h($href) . '" style="display:inline-block;margin:6px 8px 0 0;padding:11px 20px;font-weight:bold;font-size:14px;text-decoration:none;background:' . ($dark ? '#000' : '#86BC25') . ';color:' . ($dark ? '#fff' : '#000') . '">' . h($label) . '</a>';
}

/** Email to VOTESS admin for a new lead. */
function lead_admin_email(array $l): array {
    $P = purposes();
    $purpose = $P[$l['purpose']] ?? $l['purpose'];
    $digits = preg_replace('/\D+/', '', (string)$l['phone']) ?? '';
    $contact = [
        'Name' => h($l['name']),
        'Email' => '<a href="mailto:' . h($l['email']) . '">' . h($l['email']) . '</a>',
        'Phone' => $l['phone'] ? '<a href="tel:' . h($l['phone']) . '">' . h($l['phone']) . '</a>' : '',
        'Preferred contact' => h($l['preferred_contact']) . ($l['preferred_time'] ? ' (' . h($l['preferred_time']) . ')' : ''),
        'Location' => h(implode(', ', array_filter([$l['city'], $l['state'], $l['country']]))),
    ];
    $org = ['Organisation' => h($l['company']), 'Role' => h($l['designation']), 'Organisation size' => h($l['org_size']), 'Website' => h($l['website'])];
    $enq = ['Purpose' => '<b>' . h($purpose) . '</b>', 'Detail' => h($l['sub_interest']), 'Budget' => h($l['budget']), 'Timeline' => h($l['timeline']), 'Heard about us via' => h($l['heard_from'])];
    $trk = ['Reference' => h($l['ref']), 'Submitted' => h(date('d M Y, H:i T')), 'Page' => h($l['source_url']), 'UTM' => h(trim($l['utm_source'] . ' / ' . $l['utm_medium'] . ' / ' . $l['utm_campaign'], ' /')), 'IP' => h($l['ip']), 'Marketing consent' => $l['consent_marketing'] ? 'Yes' : 'No'];
    $inner = '<p style="margin:0 0 6px;font-size:14px;color:#53565A">A new enquiry arrived through the website.</p>'
        . '<div style="background:#F2F2F2;border-left:4px solid #86BC25;padding:12px 16px;font-size:15px"><b>' . h($purpose) . '</b><br><span style="color:#53565A;font-size:13px">Ref ' . h($l['ref']) . '</span></div>'
        . kv_table('CONTACT', $contact) . kv_table('ORGANISATION', $org) . kv_table('ENQUIRY', $enq)
        . '<h3 style="margin:22px 0 8px;font-size:13px;letter-spacing:.04em;color:#53565A;border-bottom:1px solid #D0D0CE;padding-bottom:6px">MESSAGE</h3><div style="font-size:14px;line-height:1.65">' . nl2br(h($l['message'])) . '</div>'
        . '<div style="margin-top:22px">' . btn('Reply to ' . explode(' ', $l['name'])[0], 'mailto:' . $l['email'] . '?subject=' . rawurlencode('Re: your VOTESS enquiry (' . $l['ref'] . ')'))
        . ($digits ? btn('WhatsApp', 'https://wa.me/' . $digits, true) : '') . '</div>'
        . kv_table('TRACKING', $trk);
    return ['[VOTESS Lead] ' . $purpose . ' | ' . $l['name'] . ' (' . $l['ref'] . ')', email_layout('New lead: ' . $l['name'], $inner, $purpose . ' from ' . $l['name'])];
}

/** Auto-reply to the person who submitted the form. */
function lead_user_email(array $l): array {
    $P = purposes();
    $purpose = $P[$l['purpose']] ?? $l['purpose'];
    $first = h(explode(' ', trim($l['name']))[0]);
    $sla = h(env('REPLY_SLA', 'one business day'));
    $note = $l['purpose'] === 'nayibeej'
        ? '<p style="font-size:14px;line-height:1.6;background:#E6F3F9;border-left:4px solid #0076A8;padding:12px 16px">Nayibeej is an independent non-profit that VOTESS supports. We will pass your message to the right person and put you in touch.</p>' : '';
    $inner = '<p style="font-size:15px;line-height:1.65;margin:0 0 14px">Hello ' . $first . ',</p>'
        . '<p style="font-size:15px;line-height:1.65;margin:0 0 14px">Thank you for contacting VOTESS. We have received your message and a member of our team will reply within ' . $sla . '.</p>'
        . $note
        . kv_table('YOUR ENQUIRY', ['Reference' => '<b>' . h($l['ref']) . '</b>', 'Topic' => h($purpose), 'Organisation' => h($l['company']), 'Your message' => nl2br(h(mb_substr($l['message'], 0, 400)) . (mb_strlen($l['message']) > 400 ? '...' : ''))])
        . '<h3 style="margin:24px 0 8px;font-size:15px">What happens next</h3><ol style="margin:0;padding-left:20px;font-size:14px;line-height:1.8"><li>We review your enquiry and route it to the right team.</li><li>We contact you by your preferred method: ' . h($l['preferred_contact']) . '.</li><li>We agree next steps together.</li></ol>'
        . '<p style="font-size:14px;line-height:1.6;margin:22px 0 0">Need to add something? Simply reply to this email and quote your reference.</p>'
        . '<div style="margin-top:14px">' . btn('Visit our website', site_url()) . '</div>';
    return ['We received your message (' . $l['ref'] . ')', email_layout('Thank you, ' . explode(' ', trim($l['name']))[0], $inner, 'We will reply within ' . env('REPLY_SLA', 'one business day'))];
}

function payment_receipt_email(array $p): array {
    $rows = ['Reference' => '<b>' . h($p['order_ref']) . '</b>', 'Amount' => '<b>INR ' . number_format((float)$p['amount'], 2) . '</b>', 'Paid for' => h($p['purpose']), 'Invoice no.' => h($p['invoice_no']),
        'Gateway' => h(ucfirst($p['gateway'])), 'Gateway payment ID' => h($p['gateway_payment_id']), 'Status' => 'Received'];
    $inner = '<p style="font-size:15px;line-height:1.65;margin:0 0 6px">Hello ' . h(explode(' ', trim($p['name']))[0]) . ', thank you. We have received your payment.</p>' . kv_table('PAYMENT RECEIPT', $rows)
        . '<p style="font-size:13px;color:#53565A;margin-top:20px">Keep this email as your acknowledgement. A tax invoice, if applicable, is issued separately.</p>';
    return ['Payment received: ' . $p['order_ref'], email_layout('Payment received', $inner, 'INR ' . number_format((float)$p['amount'], 2) . ' received')];
}
function payment_admin_email(array $p, string $headline): array {
    $rows = ['Reference' => h($p['order_ref']), 'Amount' => 'INR ' . number_format((float)$p['amount'], 2), 'Gateway' => h($p['gateway']), 'Status' => '<b>' . h($p['status']) . '</b>', 'Payer' => h($p['name']), 'Email' => h($p['email']), 'Phone' => h($p['phone']),
        'Paid for' => h($p['purpose']), 'Invoice no.' => h($p['invoice_no']), 'Gateway payment ID' => h($p['gateway_payment_id']), 'UPI UTR' => h($p['utr'])];
    return ['[VOTESS Payment] ' . $headline . ' | ' . $p['order_ref'] . ' | INR ' . number_format((float)$p['amount'], 2), email_layout($headline, kv_table('PAYMENT', $rows), $headline)];
}
