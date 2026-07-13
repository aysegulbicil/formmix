<?php

/**
 * Ürün listesi. Veritabanı yerine içerik buradan yönetilir.
 *
 * Alanlar:
 *  - name          : Ürün adı
 *  - short         : Kısa açıklama (kartlarda görünür)
 *  - image         : public/assets içindeki görsel yolu
 *  - highlight     : Öne çıkan ürün mü? (polo yaka vurgulanır)
 *  - badge         : Kart üzerinde rozet metni (boşsa gösterilmez)
 *  - home_featured : Anasayfadaki "Öne Çıkan Ürünler" bölümünde yer alsın mı?
 */

return [
    [
        'name'          => 'Polo Yaka İş Kıyafeti',
        'short'         => 'Kurumsal ekipler için en çok tercih edilen, logoya özel baskılı/nakışlı polo yaka tişört.',
        'image'         => 'images/product-polo.jpg',
        'highlight'     => true,
        'badge'         => 'En Çok Tercih Edilen',
        'home_featured' => true,
    ],
    [
        'name'          => 'Önlük',
        'short'         => 'Kafe, restoran, kuaför ve mutfak ekipleri için çapraz askılı, logolu kurumsal önlük.',
        'image'         => 'images/product-onluk.jpg',
        'highlight'     => false,
        'badge'         => '',
        'home_featured' => true,
    ],
    [
        'name'          => 'Sweatshirt',
        'short'         => 'Soğuk ortamlar ve dış mekan ekipleri için sıcak tutan, logolu kurumsal sweatshirt.',
        'image'         => 'images/product-sweatshirt.jpg',
        'highlight'     => false,
        'badge'         => '',
        'home_featured' => true,
    ],
    [
        'name'          => 'İş Pantolonu',
        'short'         => 'Dayanıklı kumaş ve rahat kalıp; cep detaylı, fabrika ve teknik ekipler için iş pantolonu.',
        'image'         => 'images/product-pants.jpg',
        'highlight'     => false,
        'badge'         => '',
        'home_featured' => true,
    ],
    [
        'name'          => 'Yelek',
        'short'         => 'Saha, depo ve servis ekipleri için pratik, logolu kurumsal yelek seçenekleri.',
        'image'         => 'images/product-waistcoat.png',
        'highlight'     => false,
        'badge'         => '',
        'home_featured' => false,
    ],
    [
        'name'          => 'Baskılı Tişört',
        'short'         => 'Pamuklu, dayanıklı ve uygun maliyetli. Etkinlik ve günlük ekip kullanımı için ideal.',
        'image'         => 'images/product-tshirt.jpg',
        'highlight'     => false,
        'badge'         => '',
        'home_featured' => false,
    ],
];
