<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedEmailTransportSettings extends Migration
{
    private array $keys = [
        'Email.protocol',
        'Email.SMTPHost',
        'Email.SMTPPort',
        'Email.SMTPTimeout',
        'Email.SMTPCrypto',
    ];

    public function up(): void
    {
        setting('Email.protocol', (string) env('email.protocol', 'smtp'));
        setting('Email.SMTPHost', (string) env('email.SMTPHost', 'mailpit'));
        setting('Email.SMTPPort', (int) env('email.SMTPPort', 1025));
        setting('Email.SMTPTimeout', (int) env('email.SMTPTimeout', 5));
        setting('Email.SMTPCrypto', (string) env('email.SMTPCrypto', 'tls'));
    }

    public function down(): void
    {
        foreach ($this->keys as $key) {
            setting()->forget($key);
        }
    }
}
