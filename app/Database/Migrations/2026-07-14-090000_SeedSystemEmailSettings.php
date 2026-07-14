<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSystemEmailSettings extends Migration
{
    public function up(): void
    {
        setting('Email.fromEmail', (string) env('email.fromEmail', 'no-reply@formmix.local'));
        setting('Email.fromName', (string) env('email.fromName', 'FORMMIX'));
    }

    public function down(): void
    {
        setting()->forget('Email.fromEmail');
        setting()->forget('Email.fromName');
    }
}
