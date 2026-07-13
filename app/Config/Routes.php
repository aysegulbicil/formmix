<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * FORMMIX site rotalari (temiz, Turkce URL'ler).
 */
$routes->get('/', 'Home::index');
$routes->get('hakkimizda', 'About::index');
$routes->get('urunler', 'Products::index');
$routes->get('katalog', 'Catalog::index');
$routes->get('iletisim', 'Contact::index');
$routes->post('iletisim', 'Contact::submit');
