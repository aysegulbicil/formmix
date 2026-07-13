<?php
// Geliştirme (development) ortamı için detaylı hata görünümü.
$statusCode = $statusCode ?? 500;
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(get_class($exception)) ?></title>
    <style>
        body { margin:0; font-family:Consolas,Menlo,monospace; background:#0E2A47; color:#e8edf3; padding:32px; }
        .card { background:#10243f; border:1px solid #1d3b5e; border-radius:12px; padding:24px 28px; max-width:1000px; margin:0 auto; }
        h1 { color:#F26A21; font-size:20px; margin:0 0 6px; }
        .msg { font-size:16px; color:#fff; margin:0 0 16px; }
        .loc { color:#9fb3c8; margin-bottom:18px; }
        pre { background:#0b1c33; border-radius:8px; padding:16px; overflow:auto; font-size:13px; line-height:1.5; color:#cdd9e5; }
        .tag { display:inline-block; background:#F26A21; color:#fff; border-radius:6px; padding:2px 8px; font-size:12px; margin-bottom:12px; }
    </style>
</head>
<body>
    <div class="card">
        <span class="tag">HTTP <?= esc((string) $statusCode) ?></span>
        <h1><?= esc(get_class($exception)) ?></h1>
        <p class="msg"><?= esc($exception->getMessage()) ?></p>
        <div class="loc"><?= esc($exception->getFile()) ?> : <strong><?= (int) $exception->getLine() ?></strong></div>
        <pre><?= esc($exception->getTraceAsString()) ?></pre>
    </div>
</body>
</html>
