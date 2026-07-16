<?php

namespace App\Controllers;

use App\Services\WebsiteProductCatalogService;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

/**
 * İletişim sayfası ve form gönderimi.
 */
class Contact extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'       => 'İletişim | FORMMIX',
            'description' => 'FORMMIX ile iletişime geçin: WhatsApp’tan teklif alın, telefonla ulaşın veya iletişim formunu doldurun. Kurumsal baskılı iş kıyafetleri için buradayız.',
            'bodyClass'   => 'page-contact',
            'products'    => (new WebsiteProductCatalogService())->all(),
        ];

        return view('pages/contact', $data);
    }

    /**
     * Form gönderimi (POST). Doğrulama + çift yönlü e-posta (işletme + müşteri).
     */
    public function submit(): RedirectResponse
    {
        // Bal küpü (honeypot) — bot doldurursa sessizce başarılıya yönlendir.
        if (trim((string) $this->request->getPost('website')) !== '') {
            return redirect()->to(site_url('iletisim'))->with('sent', true);
        }

        $rules = [
            'name'    => 'required|min_length[2]|max_length[80]',
            'company' => 'permit_empty|max_length[120]',
            'phone'   => 'required|min_length[7]|max_length[25]',
            'email'   => 'required|valid_email|max_length[120]',
            'product' => 'permit_empty|max_length[80]',
            'message' => 'permit_empty|max_length[1500]',
        ];

        $messages = [
            'name' => [
                'required'   => 'Lütfen adınızı ve soyadınızı girin.',
                'min_length' => 'Ad soyad çok kısa görünüyor.',
                'max_length' => 'Ad soyad en fazla 80 karakter olabilir.',
            ],
            'phone' => [
                'required'   => 'Size ulaşabilmemiz için telefon numaranız gerekli.',
                'min_length' => 'Geçerli bir telefon numarası girin.',
                'max_length' => 'Telefon numarası en fazla 25 karakter olabilir.',
            ],
            'email' => [
                'required'    => 'Onay e-postası gönderebilmemiz için e-posta adresiniz gerekli.',
                'valid_email' => 'Lütfen geçerli bir e-posta adresi girin.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to(site_url('iletisim'))
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'    => trim((string) $this->request->getPost('name')),
            'company' => trim((string) $this->request->getPost('company')),
            'phone'   => trim((string) $this->request->getPost('phone')),
            'email'   => trim((string) $this->request->getPost('email')),
            'product' => trim((string) $this->request->getPost('product')),
            'message' => trim((string) $this->request->getPost('message')),
            'date'    => date('d.m.Y H:i'),
        ];

        $this->sendEmails($data);

        return redirect()->to(site_url('iletisim'))->with('sent', true);
    }

    /**
     * İşletmeye bildirim + müşteriye onay e-postası gönderir.
     * Hata olursa kullanıcı akışını bozmadan loglar.
     */
    private function sendEmails(array $data): void
    {
        try {
            $email = \Config\Services::email();

            // 1) İşletmeye bildirim (lead)
            $email->setTo(site('notifyEmail'));
            $email->setReplyTo($data['email'], $data['name']);
            $email->setSubject('Yeni Teklif Talebi — ' . $data['name']);
            $email->setMessage(view('emails/notify', $data));

            if (! $email->send()) {
                log_message('error', 'FORMMIX bildirim e-postası gönderilemedi: ' . $email->printDebugger(['headers']));
            }

            // 2) Formu dolduran kişiye onay
            $email->clear(true);
            $email->setTo($data['email']);
            $email->setSubject('FORMMIX — Talebinizi aldık');
            $email->setMessage(view('emails/confirm', $data));

            if (! $email->send()) {
                log_message('error', 'FORMMIX onay e-postası gönderilemedi: ' . $email->printDebugger(['headers']));
            }
        } catch (Throwable $e) {
            log_message('error', 'İletişim formu e-posta hatası: ' . $e->getMessage());
        }
    }
}
