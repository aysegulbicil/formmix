<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * FORMMIX marka ve iletisim bilgileri.
 *
 * TEK KAYNAK: Telefon, WhatsApp, e-posta vb. bilgiler buradan yonetilir.
 * Tum site (header, footer, butonlar, iletisim sayfasi) bu degerleri kullanir.
 *
 * >>> GERCEK WHATSAPP / TELEFON NUMARANIZI asagidaki alanlara yazin. <<<
 */
class Site extends BaseConfig
{
    /**
     * Marka adi.
     */
    public string $brand = 'FORMMIX';

    /**
     * Ana slogan / marka mesaji.
     */
    public string $slogan = 'Ekibiniz Markanızı Temsil Eder.';

    // ------------------------------------------------------------------
    // ILETISIM (lutfen gercek bilgilerle guncelleyin)
    // ------------------------------------------------------------------

    /**
     * Ekranda gosterilecek telefon (insan okur).
     * Ornek: '+90 532 000 00 00'
     */
    public string $phoneDisplay = '0539 487 31 90';

    /**
     * Aranabilir telefon (tel: linki icin, sadece rakam ve +).
     */
    public string $phoneDial = '+905394873190';

    /**
     * WhatsApp numarasi (wa.me linki icin; basinda + OLMADAN, ulke koduyla).
     */
    public string $whatsapp = '905394873190';

    /**
     * WhatsApp butonlarinda otomatik dolacak hazir mesaj.
     */
    public string $whatsappText = 'Merhaba, FORMMIX kurumsal iş kıyafetleri hakkında teklif almak istiyorum.';

    /**
     * E-posta adresi (genel). Su an sitede gosterilmiyor.
     */
    public string $email = 'aysegullbicill@gmail.com';

    /**
     * Iletisim formu bildirimlerinin (lead) gonderilecegi adres.
     */
    public string $notifyEmail = 'aysegullbicill@gmail.com';

    /**
     * Instagram profil adresi.
     */
    public string $instagram = 'https://www.instagram.com/formmix_/';

    /**
     * Adres (istege bagli; bos birakilirsa gosterilmez).
     */
    public string $address = '';

    /**
     * Calisma saatleri (istege bagli).
     */
    public string $workingHours = '7/24';

    // ------------------------------------------------------------------
    // SEO VARSAYILANLARI
    // ------------------------------------------------------------------

    /**
     * Sayfa basligi eki (her <title> sonuna eklenir).
     */
    public string $titleSuffix = 'FORMMIX';

    /**
     * Varsayilan meta aciklama.
     */
    public string $defaultDescription = 'FORMMIX, işletmenize özel baskılı kurumsal iş kıyafetleri üretir: polo yaka, baskılı tişört, sweatshirt, yelek, önlük ve iş pantolonu. Fabrika, servis, restoran ve tüm ekipler için profesyonel görünüm.';
}
