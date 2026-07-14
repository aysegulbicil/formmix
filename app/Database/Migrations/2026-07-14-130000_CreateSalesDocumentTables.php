<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesDocumentTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'document_number' => ['type' => 'VARCHAR', 'constraint' => 40],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 10],
            'source_quote_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'customer_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'customer_owner_employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'sales_employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'approved_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'draft'],
            'client_reference' => ['type' => 'VARCHAR', 'constraint' => 80],
            'currency' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'TRY'],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'discount_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'tax_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'grand_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'delivery_address' => ['type' => 'TEXT', 'null' => true],
            'requested_delivery_date' => ['type' => 'DATE', 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'cancelled_at' => ['type' => 'DATETIME', 'null' => true],
            'cancellation_reason' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('document_number');
        $this->forge->addUniqueKey('client_reference');
        $this->forge->addKey(['customer_id', 'created_at']);
        $this->forge->addKey(['sales_employee_id', 'status']);
        $this->forge->addKey(['document_type', 'status']);
        $this->forge->addForeignKey('source_quote_id', 'sales_documents', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('customer_owner_employee_id', 'employees', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('sales_employee_id', 'employees', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('approved_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('sales_documents', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'sales_document_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'product_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'product_variant_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'product_code_snapshot' => ['type' => 'VARCHAR', 'constraint' => 80],
            'product_name_snapshot' => ['type' => 'VARCHAR', 'constraint' => 180],
            'variant_snapshot' => ['type' => 'VARCHAR', 'constraint' => 500],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '15,4'],
            'discount_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'net_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'tax_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'line_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sales_document_id');
        $this->forge->addKey(['product_id', 'product_variant_id']);
        $this->forge->addForeignKey('sales_document_id', 'sales_documents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('sales_document_items', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'sales_document_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'old_status' => ['type' => 'VARCHAR', 'constraint' => 24, 'null' => true],
            'new_status' => ['type' => 'VARCHAR', 'constraint' => 24],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'changed_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sales_document_id', 'created_at']);
        $this->forge->addForeignKey('sales_document_id', 'sales_documents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('sales_document_status_history', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'sales_document_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'approval_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'requested_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'requested_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'decided_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'decision_note' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'decided_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sales_document_id', 'status']);
        $this->forge->addForeignKey('sales_document_id', 'sales_documents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('requested_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('decided_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('sales_document_approvals', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('sales_document_approvals', true);
        $this->forge->dropTable('sales_document_status_history', true);
        $this->forge->dropTable('sales_document_items', true);
        $this->forge->dropTable('sales_documents', true);
    }
}
