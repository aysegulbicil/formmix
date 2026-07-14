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

service('auth')->routes($routes, ['except' => ['register', 'logout']]);
$routes->post('logout', '\\CodeIgniter\\Shield\\Controllers\\LoginController::logoutAction');

$routes->group('panel', ['filter' => ['session', 'permission:panel.access']], static function ($routes): void {
    $routes->get('/', 'Panel\\Dashboard::index');

    $routes->group('personel', ['filter' => 'permission:employees.view'], static function ($routes): void {
        $routes->get('/', 'Panel\\Employees::index');
        $routes->get('yeni', 'Panel\\Employees::create', ['filter' => 'permission:employees.manage']);
        $routes->post('yeni', 'Panel\\Employees::store', ['filter' => 'permission:employees.manage']);
        $routes->get('(:num)/duzenle', 'Panel\\Employees::edit/$1', ['filter' => 'permission:employees.manage']);
        $routes->post('(:num)/duzenle', 'Panel\\Employees::update/$1', ['filter' => 'permission:employees.manage']);
        $routes->post('(:num)/durum', 'Panel\\Employees::toggleStatus/$1', ['filter' => 'permission:employees.manage']);
    });
});
