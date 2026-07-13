<?php
/** @var string $name @var string $company @var string $phone @var string $email @var string $product @var string $message @var string $date */
$wa = whatsapp_link();
?>
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0; background:#f2f4f7; font-family:Arial,Helvetica,sans-serif; color:#23272E;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7; padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(14,42,71,.08);">
        <tr><td style="background:#0E2A47; padding:24px 28px; text-align:center;">
          <span style="font-size:24px; font-weight:800; color:#ffffff;">FORM<span style="color:#F26A21;">MIX</span></span>
        </td></tr>
        <tr><td style="padding:30px 28px;">
          <h1 style="margin:0 0 10px; font-size:22px; color:#0E2A47;">Merhaba <?= esc($name) ?>,</h1>
          <p style="margin:0 0 18px; color:#5b6573; font-size:15px; line-height:1.7;">
            Teklif talebinizi aldık, teşekkür ederiz. Ekibimiz en kısa sürede sizinle iletişime geçecek.
            Daha hızlı dönüş için WhatsApp'tan da yazabilirsiniz.
          </p>

          <div style="background:#f7f9fb; border-radius:10px; padding:18px 20px; margin-bottom:22px;">
            <p style="margin:0 0 10px; color:#0E2A47; font-weight:bold; font-size:14px;">Talebinizin özeti</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#23272E;">
              <tr><td style="padding:4px 0; color:#5b6573; width:130px;">İstenen ürün</td><td style="padding:4px 0;"><?= esc($product !== '' ? $product : 'Belirtilmedi') ?></td></tr>
              <tr><td style="padding:4px 0; color:#5b6573;">Telefon</td><td style="padding:4px 0;"><?= esc($phone) ?></td></tr>
              <?php if ($message !== ''): ?>
              <tr><td style="padding:4px 0; color:#5b6573; vertical-align:top;">Mesajınız</td><td style="padding:4px 0;"><?= nl2br(esc($message)) ?></td></tr>
              <?php endif; ?>
            </table>
          </div>

          <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
            <tr>
              <td style="border-radius:8px; background:#F26A21;">
                <a href="<?= esc($wa) ?>" style="display:inline-block; padding:14px 26px; color:#ffffff; font-weight:bold; text-decoration:none; font-size:15px;">WhatsApp'tan Yaz</a>
              </td>
            </tr>
          </table>

          <p style="margin:24px 0 0; color:#8ea0b4; font-size:13px; text-align:center;">
            Telefon: <?= esc(site('phoneDisplay')) ?>
          </p>
        </td></tr>
        <tr><td style="background:#0A2038; padding:18px 28px; text-align:center; color:#8ea0b4; font-size:12px;">
          FORMMIX — Ekibiniz markanızı temsil eder.
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
