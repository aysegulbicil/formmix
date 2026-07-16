<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderProductionAssignees extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('sales_documents', [
            'preparation_employee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'sales_employee_id',
            ],
            'design_employee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'preparation_employee_id',
            ],
            'print_employee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'design_employee_id',
            ],
        ]);

        $this->db->query('ALTER TABLE sales_documents
            ADD INDEX sales_documents_preparation_status (preparation_employee_id, status),
            ADD INDEX sales_documents_design_status (design_employee_id, status),
            ADD INDEX sales_documents_print_status (print_employee_id, status),
            ADD CONSTRAINT sales_documents_preparation_employee_fk FOREIGN KEY (preparation_employee_id) REFERENCES employees(id) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD CONSTRAINT sales_documents_design_employee_fk FOREIGN KEY (design_employee_id) REFERENCES employees(id) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD CONSTRAINT sales_documents_print_employee_fk FOREIGN KEY (print_employee_id) REFERENCES employees(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE sales_documents
            DROP FOREIGN KEY sales_documents_preparation_employee_fk,
            DROP FOREIGN KEY sales_documents_design_employee_fk,
            DROP FOREIGN KEY sales_documents_print_employee_fk,
            DROP INDEX sales_documents_preparation_status,
            DROP INDEX sales_documents_design_status,
            DROP INDEX sales_documents_print_status');

        $this->forge->dropColumn('sales_documents', [
            'preparation_employee_id',
            'design_employee_id',
            'print_employee_id',
        ]);
    }
}
