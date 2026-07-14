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

    $routes->group('musteriler', static function ($routes): void {
        $routes->get('/', 'Panel\\Customers::index');
        $routes->get('yeni', 'Panel\\Customers::create', ['filter' => 'permission:customers.create']);
        $routes->get('tekrar-kontrol', 'Panel\\Customers::duplicateCheck', ['filter' => 'permission:customers.create']);
        $routes->post('yeni', 'Panel\\Customers::store', ['filter' => 'permission:customers.create']);
        $routes->get('(:num)', 'Panel\\Customers::show/$1');
        $routes->get('(:num)/duzenle', 'Panel\\Customers::edit/$1', ['filter' => 'permission:customers.create']);
        $routes->post('(:num)/duzenle', 'Panel\\Customers::update/$1', ['filter' => 'permission:customers.create']);
        $routes->post('(:num)/sorumlu', 'Panel\\Customers::assign/$1', ['filter' => 'permission:customers.assign']);
        $routes->post('(:num)/gorusme', 'Panel\\Customers::addActivity/$1');
    });

    $routes->group('urunler', ['filter' => 'permission:products.view'], static function ($routes): void {
        $routes->get('/', 'Panel\\Products::index');
        $routes->get('yeni', 'Panel\\Products::create', ['filter' => 'permission:products.manage']);
        $routes->post('yeni', 'Panel\\Products::store', ['filter' => 'permission:products.manage']);
        $routes->post('toplu-fiyat', 'Panel\\Products::bulkPriceUpdate', ['filter' => 'permission:products.manage']);
        $routes->get('fiyat-gruplari', 'Panel\\Products::priceGroups', ['filter' => 'permission:products.manage']);
        $routes->post('fiyat-gruplari', 'Panel\\Products::storePriceGroup', ['filter' => 'permission:products.manage']);
        $routes->post('fiyat-gruplari/(:num)/durum', 'Panel\\Products::togglePriceGroup/$1', ['filter' => 'permission:products.manage']);
        $routes->get('(:num)/duzenle', 'Panel\\Products::edit/$1', ['filter' => 'permission:products.manage']);
        $routes->post('(:num)/duzenle', 'Panel\\Products::update/$1', ['filter' => 'permission:products.manage']);
        $routes->post('(:num)/durum', 'Panel\\Products::toggleStatus/$1', ['filter' => 'permission:products.manage']);
        $routes->post('(:num)/ozel-fiyat', 'Panel\\Products::storeSpecialPrice/$1', ['filter' => 'permission:products.manage']);
        $routes->post('(:num)/ozel-fiyat/(:num)/durum', 'Panel\\Products::toggleSpecialPrice/$1/$2', ['filter' => 'permission:products.manage']);
    });
});
