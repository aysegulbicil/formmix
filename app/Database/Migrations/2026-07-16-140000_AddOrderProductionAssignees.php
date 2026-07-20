<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderProductionAssignees extends Migration
{
    public function up(): void
    {
        $columns = [
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
        ];
        foreach ($columns as $name => $definition) {
            if (! $this->db->fieldExists($name, 'sales_documents')) {
                $this->forge->addColumn('sales_documents', [$name => $definition]);
            }
        }

        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query('CREATE INDEX IF NOT EXISTS sales_documents_preparation_status ON sales_documents (preparation_employee_id, status)');
            $this->db->query('CREATE INDEX IF NOT EXISTS sales_documents_design_status ON sales_documents (design_employee_id, status)');
            $this->db->query('CREATE INDEX IF NOT EXISTS sales_documents_print_status ON sales_documents (print_employee_id, status)');
            return;
        }

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
        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query('DROP INDEX IF EXISTS sales_documents_preparation_status');
            $this->db->query('DROP INDEX IF EXISTS sales_documents_design_status');
            $this->db->query('DROP INDEX IF EXISTS sales_documents_print_status');
        } else {
            $this->db->query('ALTER TABLE sales_documents
            DROP FOREIGN KEY sales_documents_preparation_employee_fk,
            DROP FOREIGN KEY sales_documents_design_employee_fk,
            DROP FOREIGN KEY sales_documents_print_employee_fk,
            DROP INDEX sales_documents_preparation_status,
            DROP INDEX sales_documents_design_status,
            DROP INDEX sales_documents_print_status');
        }

        $this->forge->dropColumn('sales_documents', [
            'preparation_employee_id',
            'design_employee_id',
            'print_employee_id',
        ]);
    }
}
