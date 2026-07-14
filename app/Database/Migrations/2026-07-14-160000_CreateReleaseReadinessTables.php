<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReleaseReadinessTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 60],
            'category' => ['type' => 'VARCHAR', 'constraint' => 60],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'VARCHAR', 'constraint' => 500],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'checked_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'checked_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey(['category', 'sort_order']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('checked_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('release_readiness_items', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT'],
            'severity' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'medium'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'resolution_note' => ['type' => 'TEXT', 'null' => true],
            'reported_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'resolved_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'severity']);
        $this->forge->addForeignKey('reported_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('resolved_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('release_issues', true);

        $this->seedChecklist();
    }

    public function down(): void
    {
        $this->forge->dropTable('release_issues', true);
        $this->forge->dropTable('release_readiness_items', true);
    }

    private function seedChecklist(): void
    {
        $now = date('Y-m-d H:i:s');
        $items = [
            ['test-data', 'Deneme hazırlığı', 'Gerçeğe yakın deneme verileri hazırlandı', 'Deneme kayıtları gerçek kayıtlardan ayırt edilebilir ve sonradan güvenle temizlenebilir.', 10],
            ['role-owner', 'Görev bazlı testler', 'İşletme sahibi senaryosu geçti', 'Kullanıcı, personel, onay, maliyet, rapor ve kritik işlemler kontrol edildi.', 20],
            ['role-sales-manager', 'Görev bazlı testler', 'Satış yöneticisi senaryosu geçti', 'Müşteri dağılımı, sipariş onayı ve maliyet gizleme kontrol edildi.', 30],
            ['role-field-sales', 'Görev bazlı testler', 'Saha personeli senaryosu geçti', 'Kendi müşterisi, teklif, sipariş, mobil taslak ve başka müşteri erişim reddi kontrol edildi.', 40],
            ['role-accounting', 'Görev bazlı testler', 'Muhasebe senaryosu geçti', 'Satış, alış, maliyet ve rapor erişimi; yetkisiz yönetim işlemleri kontrol edildi.', 50],
            ['role-warehouse', 'Görev bazlı testler', 'Depo senaryosu geçti', 'Mal kabul, ayırma, sevkiyat, iade, sayım ve müşteri bilgisi sınırı kontrol edildi.', 60],
            ['device-desktop', 'Cihaz ve kullanım', 'Masaüstü görünümü doğrulandı', 'Temel ekranlar, tablolar, filtreler ve dışa aktarım masaüstünde kontrol edildi.', 70],
            ['device-tablet', 'Cihaz ve kullanım', 'Tablet görünümü doğrulandı', 'Menü, formlar ve tablolar tablet genişliğinde kontrol edildi.', 80],
            ['device-phone', 'Cihaz ve kullanım', 'Telefon görünümü doğrulandı', 'Yatay taşma, dokunma alanları ve bağlantısız sipariş taslağı kontrol edildi.', 90],
            ['security-access', 'Güvenlik', 'Görev ve veri erişimi doğrulandı', 'Yetkisiz sayfa erişimi, maliyet gizleme, CSRF, çıkış ve oturum davranışı kontrol edildi.', 100],
            ['email-recovery', 'Güvenlik', 'Gerçek e-posta ile şifre yenileme denendi', 'Canlıda kullanılacak SMTP hesabıyla parola yenileme iletisi doğrulandı.', 110],
            ['backup-final', 'Operasyon', 'Son yedek alındı', 'Yedek dosyasının tarihi, boyutu ve güvenli saklama konumu not edildi.', 120],
            ['restore-tested', 'Operasyon', 'Yedekten geri dönüş denendi', 'Yedek ayrı bir veritabanına geri yüklenip temel tablo ve kayıt sayıları karşılaştırıldı.', 130],
            ['production-config', 'Operasyon', 'Canlı ortam ayarları doğrulandı', 'Production modu, HTTPS, alan adı, MySQL, SMTP, dosya izinleri ve gizli bilgiler kontrol edildi.', 140],
            ['training', 'Operasyon', 'Kısa kullanıcı anlatımı tamamlandı', 'Görev bazlı kullanım rehberi ilgili kullanıcılara iletildi.', 150],
            ['support-owner', 'Operasyon', 'Sorun sorumlusu ve iletişim yolu belirlendi', 'Kritik sorun halinde kimin, hangi kanaldan ve hangi bilgiyle aranacağı yazıldı.', 160],
            ['no-critical-issues', 'Onay', 'Açık kritik sorun kalmadı', 'Kritik/yüksek sorunlar çözüldü ve düzeltmeler yeniden test edildi.', 170],
            ['written-approval', 'Onay', 'Yazılı canlı kullanım onayı alındı', 'Bu madde yalnızca manuel testler ve geri dönüş denemesi bittikten sonra işaretlenir.', 180],
        ];
        $rows = array_map(static fn ($item) => [
            'code' => $item[0], 'category' => $item[1], 'title' => $item[2],
            'description' => $item[3], 'sort_order' => $item[4], 'status' => 'pending',
            'created_at' => $now, 'updated_at' => $now,
        ], $items);
        $this->db->table('release_readiness_items')->insertBatch($rows);
    }
}
