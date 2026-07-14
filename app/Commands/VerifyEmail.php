<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class VerifyEmail extends BaseCommand
{
    protected $group       = 'FORMMIX';
    protected $name        = 'formmix:verify-email';
    protected $description = 'Yerel e-posta gönderimini doğrular.';

    public function run(array $params): void
    {
        $recipient = $params[0] ?? 'admin@formmix.local';
        $email     = service('email');

        $fromEmail = (string) setting('Email.fromEmail');
        $fromName  = (string) setting('Email.fromName');

        $email->setTo($recipient);
        $email->setFrom($fromEmail, $fromName);
        $email->setSubject('FORMMIX e-posta doğrulama');
        $email->setMessage('Yerel e-posta servisi başarıyla çalışıyor.');

        if (! $email->send(false)) {
            CLI::error('E-posta gönderilemedi.');
            CLI::write($email->printDebugger(['headers']));
            exit(1);
        }

        CLI::write("E-posta başarıyla gönderildi: {$recipient} ({$fromEmail})", 'green');
    }
}
