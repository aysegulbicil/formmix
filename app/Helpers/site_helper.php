<?php

/**
 * FORMMIX — Site yardimcisi (helper)
 *
 * Marka/iletisim bilgilerine ve dosya tabanli icerige erisim,
 * WhatsApp/telefon linkleri ve menu aktiflik durumu icin yardimci fonksiyonlar.
 * Bu helper Autoload.php ile her istekte otomatik yuklenir.
 */

if (! function_exists('site')) {
    /**
     * Site yapilandirmasina (Config\Site) erisim.
     * Anahtar verilirse ilgili degeri, verilmezse config nesnesini dondurur.
     */
    function site(?string $key = null)
    {
        $config = config('Site');

        if ($key === null) {
            return $config;
        }

        return $config->{$key} ?? '';
    }
}

if (! function_exists('whatsapp_link')) {
    /**
     * wa.me WhatsApp linki uretir. Istege bagli ozel mesaj eklenebilir.
     */
    function whatsapp_link(?string $text = null): string
    {
        $config = config('Site');
        $number = preg_replace('/\D+/', '', (string) $config->whatsapp);
        $message = $text !== null && $text !== '' ? $text : $config->whatsappText;

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }
}

if (! function_exists('phone_link')) {
    /**
     * Aranabilir tel: linki uretir.
     */
    function phone_link(): string
    {
        $config = config('Site');

        return 'tel:' . preg_replace('/[^\d+]/', '', (string) $config->phoneDial);
    }
}

if (! function_exists('site_data')) {
    /**
     * app/Data/<name>.php dosyasindaki diziyi yukler.
     * Veritabani yerine icerik bu dosyalardan yonetilir.
     */
    function site_data(string $name): array
    {
        // Guvenlik: yalniz harf, rakam, tire ve alt cizgi
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
        $path = APPPATH . 'Data/' . $name . '.php';

        if (is_file($path)) {
            $data = require $path;

            return is_array($data) ? $data : [];
        }

        return [];
    }
}

if (! function_exists('nav_active')) {
    /**
     * Verilen yol gecerli sayfayla eslesiyorsa 'is-active' sinifini dondurur.
     */
    function nav_active(string $path): string
    {
        $current = trim(uri_string(), '/');
        $path    = trim($path, '/');

        if ($path === '') {
            return $current === '' ? 'is-active' : '';
        }

        return $current === $path ? 'is-active' : '';
    }
}

if (! function_exists('asset')) {
    /**
     * public/assets altindaki bir dosya icin tam URL uretir.
     */
    function asset(string $path): string
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (! function_exists('logo_url')) {
    /**
     * Logo URL'i. Once gercek logo dosyasini (png/jpg/webp) arar, yoksa SVG'ye duser.
     * Kendi logonuzu public/assets/images/logo.png olarak kaydetmeniz yeterli;
     * site otomatik olarak onu kullanir (kod degisikligi gerekmez).
     */
    function logo_url(): string
    {
        foreach (['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp', 'logo.svg'] as $file) {
            if (is_file(FCPATH . 'assets/images/' . $file)) {
                return base_url('assets/images/' . $file);
            }
        }

        return base_url('assets/images/logo.svg');
    }
}

if (! function_exists('icon')) {
    /**
     * Ad ile satir ici (inline) SVG ikon dondurur. currentColor kullanir.
     */
    function icon(string $name, int $size = 24): string
    {
        $paths = [
            'shield'     => '<path fill="currentColor" d="M12 2 4 5v6c0 5 3.4 8.5 8 9 4.6-.5 8-4 8-9V5l-8-3zm-1.2 13L7 11.2l1.4-1.4 2.4 2.4 4.4-4.4L16.6 9 10.8 15z"/>',
            'building'   => '<path fill="currentColor" d="M4 21V5a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v4h5a1 1 0 0 1 1 1v11H4zm3-3h2v-2H7v2zm0-4h2v-2H7v2zm0-4h2V8H7v2zm0-4h2V4H7v2zm9 12h2v-2h-2v2zm0-4h2v-2h-2v2z"/>',
            'star'       => '<path fill="currentColor" d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 17.8 5.9 20.4l1.5-6.8L2.2 9l6.9-.7z"/>',
            'users'      => '<path fill="currentColor" d="M8 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zm8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM2 20c0-3.3 2.7-6 6-6s6 2.7 6 6v.5H2V20zm14.5-5.4c2.5.3 4.5 2.4 4.5 5v.4h-4V20c0-2-.7-3.9-1.9-5.3z"/>',
            'factory'    => '<path fill="currentColor" d="M2 21V10l6 3v-3l6 3V8l8-3v16H2zm4-2h2v-3H6v3zm5 0h2v-3h-2v3zm5 0h2v-3h-2v3z"/>',
            'car'        => '<path fill="currentColor" d="M3 13 5 8a3 3 0 0 1 2.8-2h8.4A3 3 0 0 1 19 8l2 5v6h-3v-2H6v2H3v-6zm3.5 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm11 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM7.5 8 6.4 11h11.2L16.5 8a1 1 0 0 0-1-.7H8.5a1 1 0 0 0-1 .7z"/>',
            'restaurant' => '<path fill="currentColor" d="M3 18h18v2H3v-2zm9-12a8 8 0 0 0-8 8h16a8 8 0 0 0-8-8zm0-4a1.5 1.5 0 0 0-1 2.6V7h2v-.4A1.5 1.5 0 0 0 12 2z"/>',
            'cafe'       => '<path fill="currentColor" d="M4 8h14v4a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8zm14 1v3h1.3a1.5 1.5 0 0 0 0-3H18zM6 20h12v2H6v-2z"/>',
            'scissors'   => '<path fill="currentColor" d="M14.5 12 21 5.5 19.5 4 12 11.5 9.9 9.4A3 3 0 1 0 8.5 11l1.6 1-1.6 1a3 3 0 1 0 1.4 1.6L12 12.5 19.5 20 21 18.5 14.5 12zM6 9a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm0 8a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>',
            'check'      => '<path fill="currentColor" d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>',
            'arrow'      => '<path fill="currentColor" d="M13 5l7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6z"/>',
            'download'   => '<path fill="currentColor" d="M11 3h2v8h3l-4 4-4-4h3V3zM5 19h14v2H5v-2z"/>',
            'file'       => '<path fill="currentColor" d="M6 2h8l4 4v16H6V2zm7 1.5V7h3.5L13 3.5zM8 12h8v1.6H8V12zm0 3h8v1.6H8V15zm0-6h4v1.6H8V9z"/>',
            'mail'       => '<path fill="currentColor" d="M3 5h18v14H3V5zm2 2v.4l7 4.6 7-4.6V7H5zm14 2.8-6.4 4.2a1 1 0 0 1-1.2 0L5 9.8V17h14V9.8z"/>',
            'map'        => '<path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/>',
            'phone'      => '<path fill="currentColor" d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.5.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.5.1.4 0 .8-.3 1l-2.2 2.3z"/>',
            'clock'      => '<path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10.6 3.5 2-.9 1.5L11 13.5V7h2v5.6z"/>',
            'whatsapp'   => '<path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 0 1 12 4zm4.6 9.9c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.2.2-.3.2-.6.1a6.5 6.5 0 0 1-3.2-2.8c-.2-.4.2-.4.6-1.2.1-.2 0-.4 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.9.9-.9 2.1-.9 2.3 0 .2.6 2.6 2.9 4.6 2.9 2.6 2.9 1.7 3.5 1.7.5 0 1.6-.7 1.8-1.3.2-.6.2-1.2.2-1.3 0-.1-.2-.2-.5-.3z"/>',
            'instagram'  => '<path fill="currentColor" d="M12 2.2c3.2 0 3.6 0 4.9.07 1.2.06 1.8.25 2.2.42.6.2 1 .47 1.4.9.43.4.7.8.9 1.4.17.4.36 1 .42 2.2.07 1.3.07 1.7.07 4.9s0 3.6-.07 4.9c-.06 1.2-.25 1.8-.42 2.2-.2.6-.47 1-.9 1.4-.4.43-.8.7-1.4.9-.4.17-1 .36-2.2.42-1.3.07-1.7.07-4.9.07s-3.6 0-4.9-.07c-1.2-.06-1.8-.25-2.2-.42-.6-.2-1-.47-1.4-.9-.43-.4-.7-.8-.9-1.4-.17-.4-.36-1-.42-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.07-4.9c.06-1.2.25-1.8.42-2.2.2-.6.47-1 .9-1.4.4-.43.8-.7 1.4-.9.4-.17 1-.36 2.2-.42C8.4 2.2 8.8 2.2 12 2.2zm0 3.05A6.75 6.75 0 1 0 18.75 12 6.75 6.75 0 0 0 12 5.25zm0 11.13A4.38 4.38 0 1 1 16.38 12 4.38 4.38 0 0 1 12 16.38zm6.97-11.4a1.58 1.58 0 1 1-1.58-1.57 1.58 1.58 0 0 1 1.58 1.57z"/>',
        ];

        $svg = $paths[$name] ?? '';

        if ($svg === '') {
            return '';
        }

        return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" aria-hidden="true" focusable="false">' . $svg . '</svg>';
    }
}
