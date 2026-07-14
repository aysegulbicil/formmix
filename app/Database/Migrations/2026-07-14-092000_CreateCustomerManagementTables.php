<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerManagementTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'customer_code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'company_name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'official_title' => ['type' => 'VARCHAR', 'constraint' => 220, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'city' => ['type' => 'VARCHAR', 'constraint' => 100],
            'district' => ['type' => 'VARCHAR', 'constraint' => 100],
            'address' => ['type' => 'TEXT', 'null' => true],
            'delivery_address' => ['type' => 'TEXT', 'null' => true],
            'billing_address' => ['type' => 'TEXT', 'null' => true],
            'tax_office' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'tax_number' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'tax_number_normalized' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'candidate'],
            'payment_term_days' => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 30],
            'credit_limit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'current_owner_employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'last_activity_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('customer_code');
        $this->forge->addKey('company_name');
        $this->forge->addKey('tax_number_normalized');
        $this->forge->addKey('current_owner_employee_id');
        $this->forge->addKey('last_activity_at');
        $this->forge->addForeignKey('current_owner_employee_id', 'employees', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('customers', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'job_title' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 30],
            'phone_normalized' => ['type' => 'VARCHAR', 'constraint' => 20],
            'email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'is_primary' => ['type' => 'BOOLEAN', 'default' => false],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addKey('phone_normalized');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customer_contacts', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'started_at' => ['type' => 'DATETIME'],
            'ended_at' => ['type' => 'DATETIME', 'null' => true],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'assigned_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['customer_id', 'started_at']);
        $this->forge->addKey(['employee_id', 'ended_at']);
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('assigned_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('customer_assignments', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'activity_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 180],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'happened_at' => ['type' => 'DATETIME'],
            'next_action_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['customer_id', 'happened_at']);
        $this->forge->addKey(['employee_id', 'happened_at']);
        $this->forge->addKey('next_action_at');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('customer_activities', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('customer_activities', true);
        $this->forge->dropTable('customer_assignments', true);
        $this->forge->dropTable('customer_contacts', true);
        $this->forge->dropTable('customers', true);
    }
}
