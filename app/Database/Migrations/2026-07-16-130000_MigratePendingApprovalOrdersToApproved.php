<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigratePendingApprovalOrdersToApproved extends Migration
{
    public function up(): void
    {
        $rows = $this->db->table('sales_documents')
            ->select('id, created_by_user_id')
            ->where('document_type', 'order')
            ->where('status', 'pending_approval')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $now = date('Y-m-d H:i:s');

            $this->db->table('sales_documents')
                ->where('id', $row['id'])
                ->update([
                    'status' => 'approved',
                    'approved_by_user_id' => $row['created_by_user_id'] ?: null,
                    'approved_at' => $now,
                    'updated_at' => $now,
                ]);

            $this->db->table('sales_document_status_history')->insert([
                'sales_document_id' => $row['id'],
                'old_status' => 'pending_approval',
                'new_status' => 'approved',
                'reason' => 'Siparis sureci sadelelestirme gecisi',
                'changed_by_user_id' => $row['created_by_user_id'] ?: null,
                'created_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $rows = $this->db->table('sales_document_status_history')
            ->select('sales_document_id, changed_by_user_id, created_at')
            ->where('reason', 'Siparis sureci sadelelestirme gecisi')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $this->db->table('sales_documents')
                ->where('id', $row['sales_document_id'])
                ->where('status', 'approved')
                ->update([
                    'status' => 'pending_approval',
                    'approved_by_user_id' => null,
                    'approved_at' => null,
                    'updated_at' => $row['created_at'],
                ]);
        }

        $this->db->table('sales_document_status_history')
            ->where('reason', 'Siparis sureci sadelelestirme gecisi')
            ->delete();
    }
}
