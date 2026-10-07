# VOTESS website: Hostinger package

PHP + MySQL website with lead capture, SMTP / EmailJS email, and Razorpay, Cashfree, PayU, Easebuzz and UPI (QR) payments.
Works on Hostinger shared hosting (PHP 8.1+, with PDO MySQL, cURL and mbstring, all on by default).

## 1. Folder layout

    <your domain folder>/            e.g. domains/yourdomain.com/
      public_html/                   <- the website (public)
        index.html, contact.html, submit-rfp.html, pay.html, who-we-are.html ...
        assets/  (site.css, site.js, config.js, pay.js, logo.png, email-banner.png)
        api/     (lead.php, pay_create.php, pay_verify.php, pay_return.php, pay_webhook.php)
      private/                       <- NOT public. Code, secrets, logs
        .env.example  ->  copy to  .env
        lib/ (bootstrap.php, templates.php, payments.php, phpmailer/)
        logs/app.log
      database/schema.sql

## 2. Install on Hostinger (File Manager)

1. hPanel > Websites > Manage > **File Manager**. Open the folder that contains `public_html` (one level above it).
2. Upload `votess-hostinger.zip` there and **Extract** it. Choose to merge/overwrite into `public_html`.
   Make sure `private/` and `database/` sit **next to** `public_html`, not inside it.
3. hPanel > **Databases > MySQL Databases**: create a database, user and password.
   Then **phpMyAdmin > Import** `database/schema.sql` into that database.
4. In `private/`, copy `.env.example` to `.env` and fill it in (File Manager > Edit). See sections 3 to 6.
5. hPanel > **SSL**: make sure HTTPS is active. Set `SITE_URL=https://votess.in` in `.env`.
6. Open `https://yourdomain.com/contact.html`, submit a test enquiry, then check phpMyAdmin (`leads` table), the admin inbox and the auto-reply.

If the `.env` cannot sit in `private/`, it can also be placed in the domain folder next to `public_html`. Never put it inside `public_html`.

## 3. Database (MySQL)

`.env`: `DB_HOST=localhost`, `DB_NAME`, `DB_USER`, `DB_PASS` (shown in hPanel).
Tables: `leads` (every field from the contact and RFP forms, UTM, IP, status, mail-sent flags) and `payments`.
Change a lead's `status` (new, contacted, qualified, won, lost, spam) in phpMyAdmin to track follow-up.

## 4. Email: SMTP and EmailJS

- `MAIL_DRIVER=smtp` (default), `emailjs`, or `smtp_then_emailjs` (SMTP first, EmailJS as backup).
- **Hostinger email:** create a mailbox in hPanel > Emails. `SMTP_HOST=smtp.hostinger.com`, `SMTP_PORT=465`, `SMTP_SECURE=ssl`, user/password of that mailbox, and `MAIL_FROM` the same address.
- **Admin mail:** `ADMIN_EMAILS=a@x.com,b@x.com`. Each lead produces an organised email (contact, organisation, enquiry, message, tracking) with logo and banner. Reply-To is the lead's address, plus Reply and WhatsApp buttons.
- **User mail:** an automatic thank-you with their reference number, summary and next steps.
- Logo and banner are embedded in SMTP mail. To change them, replace `public_html/assets/logo.png` and `email-banner.png`.
- **EmailJS:** create a service and a template. Template settings: To = `{{to_email}}`, Subject = `{{subject}}`, Reply-To = `{{reply_to}}`, body = `{{{html_body}}}` (three braces, so HTML renders). Turn on **Allow EmailJS API for non-browser applications** (Account > Security). Fill the four `EMAILJS_*` values. With EmailJS the images load from `SITE_URL/assets/`.
- **Amazon SES:** use SMTP with `email-smtp.<region>.amazonaws.com`, port 465, and your SES SMTP credentials.

## 5. Payments

Set `PAY_ENV=test` first. Only gateways with keys filled in appear on `pay.html`. Switch to `live` after testing with each gateway's sandbox.

| Gateway | .env keys | Dashboard setting |
|---|---|---|
| Razorpay | `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, `RAZORPAY_WEBHOOK_SECRET` | Webhook URL `https://yourdomain.com/api/pay_webhook.php?gateway=razorpay`, events `payment.captured`, `order.paid` |
| Cashfree | `CASHFREE_APP_ID`, `CASHFREE_SECRET` | Webhook URL `https://yourdomain.com/api/pay_webhook.php?gateway=cashfree` |
| PayU | `PAYU_KEY`, `PAYU_SALT` | Success and failure URLs are set automatically (`api/pay_return.php?gateway=payu`) |
| Easebuzz | `EASEBUZZ_KEY`, `EASEBUZZ_SALT` | Same, `api/pay_return.php?gateway=easebuzz` |
| UPI / QR | `UPI_VPA` (e.g. `votess@bank`), `UPI_PAYEE_NAME`, optional `UPI_QR_IMAGE` | None |

How it stays safe: amounts are created on the server, Razorpay signatures and PayU/Easebuzz response hashes are verified, Cashfree status is re-checked with the Cashfree API, and webhooks are signature-checked. A receipt goes to the payer and an alert to admin only once per payment.
**UPI is peer-to-peer:** the payer enters their 12-digit UTR, the payment shows as `pending_verification`, and admin gets an email to confirm it in the bank app, then sets `status='paid'` in phpMyAdmin.
The payments page is for VOTESS invoices. Nayibeej should collect donations in its own account, never through VOTESS keys.

## 6. AWS and other cloud

- **Database on AWS RDS (or any cloud MySQL):** set `DB_HOST` to the endpoint, `DB_PORT=3306`, and download the RDS CA bundle into `private/` and set `DB_SSL_CA=/home/<user>/domains/yourdomain.com/private/global-bundle.pem`. Allow Hostinger's server IP in the RDS security group.
- **Email via Amazon SES:** see section 4.
- **Forward leads and paid payments to your cloud:** set `LEAD_WEBHOOK_URL` (API Gateway + Lambda, Zapier, n8n, Make). Each event is a JSON POST with an `X-Votess-Signature` header (HMAC-SHA256 of the body using `LEAD_WEBHOOK_SECRET`). A Lambda can then write to DynamoDB/S3, notify Slack, or sync a CRM.
- The site itself can also be served from S3/CloudFront or an EC2 box: the `api/` PHP folder needs PHP hosting, everything else is static.

## 7. Editing the site

Everything you will change often is in `public_html/assets/config.js`:
- **Product website URLs** (`products[].url`): the CTA button on every product opens these in a new tab. Replace the `example.com` placeholders.
- **Social links:** footer and mobile menu icons. Leave a value empty to hide an icon.
- **Menu:** every menu item is a page and opens in a new tab.
- **News, events, press releases and perspectives:** replace the sample items.
Anything on the same page (buttons, tabs, sliders, anchors) stays in the same tab.

## 8. Security notes

- `.env` and `private/` are outside the public folder. Keep it that way.
- Forms use a hidden honeypot, server-side validation, same-origin checks, prepared SQL statements and a per-IP rate limit (`RATE_LIMIT_PER_HOUR`).
- Errors are written to `private/logs/app.log`.
- `privacy.html` is a template. Have it reviewed before launch.

## 9. Before going live (checklist)

1. Replace placeholder product URLs, social links, `info@votess.in` and sample news.
2. Test one lead end to end, and one payment per gateway in sandbox.
3. Set `PAY_ENV=live` and live keys, and add the webhook URLs.
4. Check spam-folder delivery of the emails. Add SPF, DKIM and DMARC records in hPanel > DNS.

## 10. Troubleshooting: "Network error" or a form message

Open **https://yourdomain.com/api/health.php** in the browser. It lists what is wrong without showing any secrets.
Add `?smtp=1` to test the email login. Set `HEALTH_KEY` in `.env` and open `?test_mail=you@mail.com&key=YOURKEY` to send a real test email.
**Delete `api/health.php` when everything works.**

| What you see | Cause and fix |
|---|---|
| Form says "Could not reach the server" | The page is not on the live site (for example opened as a file, or in a preview). Open it from your real domain. |
| "The form service did not respond correctly (HTTP 404)" | `api/` is not at `public_html/api/`. Re-extract so the folder is in the right place. |
| "(HTTP 403)" with no JSON | A host firewall blocked the request. Contact Hostinger support or check hPanel security settings. |
| "private folder was not found" | `private/` must sit next to `public_html`, not inside it (README step 2). |
| "PHP 8.1 or newer is required" | hPanel > Advanced > PHP Configuration, choose 8.1 or newer. |
| "Origin not allowed" | `SITE_URL` in `.env` does not match your domain. www and non-www are both accepted. |
| "We could not save your message" | Both the database and the email failed. Check `private/logs/app.log` and health.php. |
| Lead saved but no email | Check `SMTP_*`, `MAIL_FROM` and `ADMIN_EMAILS` in `.env`, then run `?smtp=1`. |

Whenever the server fails, the form now shows a "Send this by email instead" link so the visitor's message is not lost. Server errors are written to `private/logs/app.log`.
