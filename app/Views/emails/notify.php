<?php
/** @var string $name @var string $company @var string $phone @var string $email @var string $product @var string $message @var string $date */
?>
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0; background:#f2f4f7; font-family:Arial,Helvetica,sans-serif; color:#23272E;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7; padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(14,42,71,.08);">
        <tr><td style="background:#0E2A47; padding:22px 28px;">
          <span style="font-size:20px; font-weight:800; color:#ffffff;">FORM<span style="color:#F26A21;">MIX</span></span>
          <span style="color:#c8d4e2; font-size:13px; float:right; padding-top:6px;"><?= esc($date) ?></span>
        </td></tr>
        <tr><td style="padding:28px;">
          <h1 style="margin:0 0 6px; font-size:20px; color:#0E2A47;">Yeni teklif talebi</h1>
          <p style="margin:0 0 22px; color:#5b6573; font-size:14px;">İletişim formundan yeni bir talep geldi. Detaylar aşağıdadır.</p>

          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;">
            <?php
            $rows = [
                'Ad Soyad'     => $name,
                'Firma'        => $company !== '' ? $company : '—',
                'Telefon'      => $phone,
                'E-posta'      => $email,
                'İstenen Ürün' => $product !== '' ? $product : '—',
            ];
            foreach ($rows as $label => $value): ?>
              <tr>
                <td style="padding:10px 0; border-bottom:1px solid #eef1f5; color:#5b6573; width:140px; vertical-align:top;"><?= esc($label) ?></td>
                <td style="padding:10px 0; border-bottom:1px solid #eef1f5; color:#23272E; font-weight:bold;"><?= esc($value) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>

          <?php if ($message !== ''): ?>
            <p style="margin:20px 0 6px; color:#5b6573; font-size:14px;">Mesaj:</p>
            <div style="background:#f7f9fb; border-left:4px solid #F26A21; padding:14px 16px; border-radius:0 8px 8px 0; color:#23272E; font-size:15px; line-height:1.6;"><?= nl2br(esc($message)) ?></div>
          <?php endif; ?>

          <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:24px;">
            <tr>
              <td style="border-radius:8px; background:#25D366;">
                <a href="https://wa.me/<?= esc(preg_replace('/\D+/', '', $phone)) ?>" style="display:inline-block; padding:12px 22px; color:#ffffff; font-weight:bold; text-decoration:none; font-size:14px;">Müşteriye WhatsApp'tan yaz</a>
              </td>
              <td width="12"></td>
              <td style="border-radius:8px; background:#0E2A47;">
                <a href="tel:<?= esc(preg_replace('/[^\d+]/', '', $phone)) ?>" style="display:inline-block; padding:12px 22px; color:#ffffff; font-weight:bold; text-decoration:none; font-size:14px;">Ara</a>
              </td>
            </tr>
          </table>
        </td></tr>
        <tr><td style="background:#0A2038; padding:16px 28px; color:#8ea0b4; font-size:12px;">Bu e-posta FORMMIX web sitesi iletişim formundan otomatik gönderildi.</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
