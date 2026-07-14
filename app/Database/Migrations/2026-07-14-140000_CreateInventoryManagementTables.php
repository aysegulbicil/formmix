<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryManagementTables extends Migration
{
    public function up(): void
    {
        $this->createSuppliers();
        $this->createWarehouses();
        $this->createPurchaseOrders();
        $this->createPurchaseOrderItems();
        $this->createStockBalances();
        $this->createStockMovements();
        $this->createReservations();
        $this->createStockCounts();
        $this->createStockCountItems();

        $this->forge->addColumn('sales_document_items', [
            'reserved_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0, 'after' => 'quantity'],
            'fulfilled_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0, 'after' => 'reserved_quantity'],
        ]);

        $now = date('Y-m-d H:i:s');
        $this->db->table('warehouses')->insert([
            'code' => 'ANA', 'name' => 'Ana Depo', 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales_document_items', ['fulfilled_quantity', 'reserved_quantity']);
        $this->forge->dropTable('stock_count_items', true);
        $this->forge->dropTable('stock_counts', true);
        $this->forge->dropTable('stock_reservations', true);
        $this->forge->dropTable('stock_movements', true);
        $this->forge->dropTable('stock_balances', true);
        $this->forge->dropTable('purchase_order_items', true);
        $this->forge->dropTable('purchase_orders', true);
        $this->forge->dropTable('warehouses', true);
        $this->forge->dropTable('suppliers', true);
    }

    private function createSuppliers(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'supplier_code' => ['type' => 'VARCHAR', 'constraint' => 40],
            'company_name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'contact_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'tax_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('supplier_code');
        $this->forge->addKey(['company_name', 'is_active']);
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('suppliers', true);
    }

    private function createWarehouses(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'address' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('warehouses', true);
    }

    private function createPurchaseOrders(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'order_number' => ['type' => 'VARCHAR', 'constraint' => 40],
            'supplier_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ordered'],
            'order_date' => ['type' => 'DATE'],
            'expected_date' => ['type' => 'DATE', 'null' => true],
            'currency' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'TRY'],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('order_number');
        $this->forge->addKey(['supplier_id', 'status']);
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('purchase_orders', true);
    }

    private function createPurchaseOrderItems(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'purchase_order_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'product_variant_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'sku_snapshot' => ['type' => 'VARCHAR', 'constraint' => 80],
            'product_name_snapshot' => ['type' => 'VARCHAR', 'constraint' => 180],
            'ordered_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'received_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0],
            'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '15,4'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('purchase_order_id');
        $this->forge->addForeignKey('purchase_order_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('purchase_order_items', true);
    }

    private function createStockBalances(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_variant_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'on_hand_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0],
            'reserved_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['warehouse_id', 'product_variant_id']);
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_balances', true);
    }

    private function createStockMovements(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'movement_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'movement_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_variant_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'balance_after' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500],
            'reference_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'reference_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'paired_movement_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('movement_number');
        $this->forge->addKey(['warehouse_id', 'product_variant_id', 'created_at']);
        $this->forge->addKey(['reference_type', 'reference_id']);
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_movements', true);
    }

    private function createReservations(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'sales_document_item_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'reserved_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'fulfilled_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sales_document_item_id', 'status']);
        $this->forge->addForeignKey('sales_document_item_id', 'sales_document_items', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_reservations', true);
    }

    private function createStockCounts(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'count_number' => ['type' => 'VARCHAR', 'constraint' => 40],
            'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'completed'],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500],
            'counted_at' => ['type' => 'DATETIME'],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('count_number');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_counts', true);
    }

    private function createStockCountItems(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'stock_count_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'product_variant_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'system_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'counted_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'difference_quantity' => ['type' => 'DECIMAL', 'constraint' => '15,3'],
            'stock_movement_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('stock_count_id');
        $this->forge->addForeignKey('stock_count_id', 'stock_counts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_count_items', true);
    }
}
