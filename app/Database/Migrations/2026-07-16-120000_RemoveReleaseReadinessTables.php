<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveReleaseReadinessTables extends Migration
{
    public function up(): void
    {
        $this->forge->dropTable('release_issues', true);
        $this->forge->dropTable('release_readiness_items', true);
    }

    public function down(): void
    {
        // Kaldırılan operasyonel özellik ve verileri geri oluşturmuyoruz.
    }
}
