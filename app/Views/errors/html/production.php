<?php
$waLink = function_exists('whatsapp_link') ? whatsapp_link() : '#';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Bir Sorun Oluştu | FORMMIX</title>
    <style>
        :root { --navy:#0E2A47; --orange:#F26A21; --gray:#F2F4F7; --ink:#23272E; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:"Segoe UI",system-ui,-apple-system,Arial,sans-serif;
               background:var(--gray); color:var(--ink); display:flex; min-height:100vh;
               align-items:center; justify-content:center; padding:24px; }
        .box { background:#fff; max-width:540px; width:100%; border-radius:18px; padding:48px 36px;
               text-align:center; box-shadow:0 20px 60px rgba(14,42,71,.12); }
        h1 { font-size:24px; margin:0 0 10px; color:var(--navy); }
        p { color:#5b6573; line-height:1.6; margin:0 0 26px; }
        .btns { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }
        a.btn { text-decoration:none; font-weight:700; padding:13px 22px; border-radius:10px; font-size:15px; }
        .primary { background:var(--navy); color:#fff; }
        .wa { background:var(--orange); color:#fff; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Geçici bir sorun oluştu</h1>
        <p>Şu anda isteğinizi karşılayamıyoruz. Lütfen birazdan tekrar deneyin ya da WhatsApp'tan bize ulaşın.</p>
        <div class="btns">
            <a class="btn primary" href="<?= site_url('/') ?>">Ana Sayfa</a>
            <a class="btn wa" href="<?= esc($waLink) ?>" target="_blank" rel="noopener">WhatsApp'tan Yazın</a>
        </div>
    </div>
</body>
</html>
