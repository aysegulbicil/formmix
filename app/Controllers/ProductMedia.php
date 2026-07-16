<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class ProductMedia extends BaseController
{
    public function show(string $fileName): ResponseInterface
    {
        if (! preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $fileName)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $path = WRITEPATH . 'uploads/products/' . $fileName;
        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mimeTypes = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return $this->response
            ->setHeader('Content-Type', $mimeTypes[$extension])
            ->setHeader('Content-Length', (string) filesize($path))
            ->setHeader('Cache-Control', 'public, max-age=31536000, immutable')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody((string) file_get_contents($path));
    }
}
