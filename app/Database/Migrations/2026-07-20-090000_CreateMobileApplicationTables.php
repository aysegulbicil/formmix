<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMobileApplicationTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'installation_id' => ['type' => 'VARCHAR', 'constraint' => 80],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'android'],
            'device_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'app_version' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'push_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'notifications_enabled' => ['type' => 'BOOLEAN', 'default' => false],
            'last_seen_at' => ['type' => 'DATETIME', 'null' => true],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'installation_id']);
        $this->forge->addKey('push_token');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('mobile_devices', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'notification_type' => ['type' => 'VARCHAR', 'constraint' => 60],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'body' => ['type' => 'TEXT'],
            'target_route' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'entity_id' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'delivery_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'attempt_count' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'last_error' => ['type' => 'TEXT', 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'read_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'read_at']);
        $this->forge->addKey(['delivery_status', 'attempt_count']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('mobile_notifications', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'idempotency_key' => ['type' => 'VARCHAR', 'constraint' => 100],
            'operation' => ['type' => 'VARCHAR', 'constraint' => 100],
            'resource_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'resource_id' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'response_status' => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 200],
            'response_body' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'idempotency_key']);
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('api_idempotency_keys', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'android'],
            'version_name' => ['type' => 'VARCHAR', 'constraint' => 30],
            'version_code' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'minimum_version_code' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'download_url' => ['type' => 'VARCHAR', 'constraint' => 500],
            'sha256' => ['type' => 'CHAR', 'constraint' => 64],
            'release_notes' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['platform', 'version_code']);
        $this->forge->addKey(['platform', 'is_active', 'published_at']);
        $this->forge->createTable('mobile_app_releases', true);

        $this->forge->addColumn('audit_logs', [
            'source' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'web', 'after' => 'user_agent'],
            'device_id' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'source'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('audit_logs', ['device_id', 'source']);
        $this->forge->dropTable('mobile_app_releases', true);
        $this->forge->dropTable('api_idempotency_keys', true);
        $this->forge->dropTable('mobile_notifications', true);
        $this->forge->dropTable('mobile_devices', true);
    }
}
