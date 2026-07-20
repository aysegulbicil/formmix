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
$routes->get('urun-gorselleri/(:segment)', 'ProductMedia::show/$1');
$routes->get('mobil', 'Mobile::index');

service('auth')->routes($routes, ['except' => ['register', 'logout']]);
$routes->post('logout', '\\CodeIgniter\\Shield\\Controllers\\LoginController::logoutAction');

$routes->group('api/v1', ['namespace' => 'App\\Controllers\\Api\\V1'], static function ($routes): void {
    $routes->post('auth/login', 'AuthController::login', ['filter' => 'auth-rates']);
    $routes->get('app/releases/current', 'OverviewController::release');
    $routes->group('', ['filter' => 'mobile-auth:mobile'], static function ($routes): void {
        $routes->post('auth/logout', 'AuthController::logout');
        $routes->get('me', 'AuthController::me');
        $routes->get('bootstrap', 'OverviewController::bootstrap');
        $routes->get('devices/current', 'OverviewController::device');
        $routes->put('devices/push-token', 'OverviewController::pushToken');
        $routes->get('notifications', 'OverviewController::notifications');
        $routes->post('notifications/(:num)/read', 'OverviewController::readNotification/$1');
        $routes->get('customers', 'CustomersController::index');
        $routes->post('customers/duplicate-check', 'CustomersController::duplicateCheck');
        $routes->get('customers/(:num)', 'CustomersController::show/$1');
        $routes->post('customers', 'CustomersController::create');
        $routes->put('customers/(:num)', 'CustomersController::update/$1');
        $routes->post('customers/(:num)/activities', 'CustomersController::activity/$1');
        $routes->post('customers/(:num)/assignment', 'CustomersController::assignment/$1');
        $routes->get('products', 'CatalogController::index');
        $routes->get('product-categories', 'CatalogController::categories');
        $routes->post('product-categories', 'CatalogController::createCategory');
        $routes->post('products', 'CatalogController::create');
        $routes->put('products/(:num)', 'CatalogController::update/$1');
        $routes->post('products/(:num)/status', 'CatalogController::status/$1');
        $routes->post('products/(:num)/archive', 'CatalogController::archive/$1');
        $routes->post('products/bulk-price', 'CatalogController::bulkPrice');
        $routes->get('price-groups', 'CatalogController::priceGroups');
        $routes->post('price-groups', 'CatalogController::createPriceGroup');
        $routes->post('price-groups/(:num)/status', 'CatalogController::priceGroupStatus/$1');
        $routes->get('products/(:num)/special-prices', 'CatalogController::specialPrices/$1');
        $routes->post('products/(:num)/special-prices', 'CatalogController::createSpecialPrice/$1');
        $routes->post('products/(:num)/special-prices/(:num)/status', 'CatalogController::specialPriceStatus/$1/$2');
        $routes->get('products/(:num)', 'CatalogController::show/$1');
        $routes->get('products/(:num)/price', 'CatalogController::price/$1');
        $routes->get('employees', 'DirectoryController::employees');
        $routes->get('employees/manage', 'EmployeesController::index');
        $routes->post('employees/manage', 'EmployeesController::create');
        $routes->get('employees/manage/(:num)', 'EmployeesController::show/$1');
        $routes->put('employees/manage/(:num)', 'EmployeesController::update/$1');
        $routes->post('employees/manage/(:num)/status', 'EmployeesController::status/$1');
        $routes->get('sales-documents', 'SalesDocumentsController::index');
        $routes->get('sales-documents/(:num)', 'SalesDocumentsController::show/$1');
        $routes->post('sales-documents', 'SalesDocumentsController::create');
        $routes->put('sales-documents/(:num)', 'SalesDocumentsController::update/$1');
        $routes->post('sales-documents/(:num)/submit', 'SalesDocumentsController::submit/$1');
        $routes->post('sales-documents/(:num)/cancel', 'SalesDocumentsController::cancel/$1');
        $routes->post('sales-documents/(:num)/finalize', 'SalesDocumentsController::finalize/$1');
        $routes->post('sales-documents/(:num)/convert-to-order', 'SalesDocumentsController::convert/$1');
        $routes->get('tasks', 'TasksController::index');
        $routes->post('tasks/(:num)/status', 'TasksController::status/$1');
        $routes->get('inventory', 'InventoryController::index');
        $routes->post('inventory/movements', 'InventoryController::movement');
        $routes->post('inventory/counts', 'InventoryController::count');
        $routes->post('warehouses', 'InventoryController::createWarehouse');
        $routes->post('orders/(:num)/reserve', 'InventoryController::reserve/$1');
        $routes->post('orders/(:num)/ship', 'InventoryController::ship/$1');
        $routes->get('suppliers', 'InventoryController::suppliers');
        $routes->post('suppliers', 'InventoryController::createSupplier');
        $routes->post('suppliers/(:num)/status', 'InventoryController::supplierStatus/$1');
        $routes->get('purchases', 'InventoryController::purchases');
        $routes->post('purchases', 'InventoryController::createPurchase');
        $routes->get('purchases/(:num)', 'InventoryController::showPurchase/$1');
        $routes->post('purchases/(:num)/receive', 'InventoryController::receivePurchase/$1');
        $routes->get('commissions', 'CommissionsController::index');
        $routes->post('commission-rules', 'CommissionsController::createRule');
        $routes->post('commissions/calculate', 'CommissionsController::calculate');
        $routes->post('commission-periods', 'CommissionsController::createPeriod');
        $routes->post('commission-periods/(:num)/pay', 'CommissionsController::payPeriod/$1');
        $routes->get('reports', 'ReportsController::index');
        $routes->get('reports/export/(:segment)/(:segment)', 'ReportsController::export/$1/$2');
    });
});

$routes->group('panel', ['filter' => ['session', 'permission:panel.access']], static function ($routes): void {
    $routes->get('/', 'Panel\\Dashboard::index');
    $routes->get('kullanim-rehberi', 'Panel\\UserGuide::index');

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
        $routes->post('(:num)/arsivle', 'Panel\\Products::archive/$1', ['filter' => 'permission:products.manage']);
        $routes->post('(:num)/ozel-fiyat', 'Panel\\Products::storeSpecialPrice/$1', ['filter' => 'permission:products.manage']);
        $routes->post('(:num)/ozel-fiyat/(:num)/durum', 'Panel\\Products::toggleSpecialPrice/$1/$2', ['filter' => 'permission:products.manage']);
    });

    $routes->group('siparisler', static function ($routes): void {
        $routes->get('/', 'Panel\\SalesDocuments::index');
        $routes->get('yeni', 'Panel\\SalesDocuments::create', ['filter' => 'permission:orders.create']);
        $routes->post('yeni', 'Panel\\SalesDocuments::store', ['filter' => 'permission:orders.create']);
        $routes->get('fiyat', 'Panel\\SalesDocuments::price', ['filter' => 'permission:orders.create']);
        $routes->get('(:num)', 'Panel\\SalesDocuments::show/$1');
        $routes->get('(:num)/duzenle', 'Panel\\SalesDocuments::edit/$1', ['filter' => 'permission:orders.create']);
        $routes->post('(:num)/duzenle', 'Panel\\SalesDocuments::update/$1', ['filter' => 'permission:orders.create']);
        $routes->post('(:num)/gonder', 'Panel\\SalesDocuments::submit/$1', ['filter' => 'permission:orders.create']);
        $routes->post('(:num)/kesinlestir', 'Panel\\SalesDocuments::finalizeQuote/$1', ['filter' => 'permission:orders.create']);
        $routes->post('(:num)/surec', 'Panel\\SalesDocuments::progress/$1');
        $routes->post('(:num)/onayla', 'Panel\\SalesDocuments::approve/$1', ['filter' => 'permission:orders.approve']);
        $routes->post('(:num)/reddet', 'Panel\\SalesDocuments::reject/$1', ['filter' => 'permission:orders.approve']);
        $routes->post('(:num)/iptal', 'Panel\\SalesDocuments::cancel/$1');
        $routes->post('(:num)/siparise-cevir', 'Panel\\SalesDocuments::convert/$1', ['filter' => 'permission:orders.create']);
    });
    $routes->group('stok', ['filter' => 'permission:stock.manage'], static function ($routes): void {
        $routes->get('/', 'Panel\\Inventory::index');
        $routes->post('hareket', 'Panel\\Inventory::storeMovement');
        $routes->post('sayim', 'Panel\\Inventory::storeCount', ['filter' => 'permission:stock.count']);
        $routes->post('depo', 'Panel\\Inventory::storeWarehouse', ['filter' => 'permission:warehouses.manage']);
        $routes->post('siparis/(:num)/ayir', 'Panel\\Inventory::reserveOrder/$1', ['filter' => 'permission:orders.fulfill']);
        $routes->post('siparis/(:num)/sevk', 'Panel\\Inventory::shipOrder/$1', ['filter' => 'permission:orders.fulfill']);
    });
    $routes->group('tedarikciler', ['filter' => 'permission:purchases.manage'], static function ($routes): void {
        $routes->get('/', 'Panel\\Inventory::suppliers');
        $routes->post('/', 'Panel\\Inventory::storeSupplier', ['filter' => 'permission:suppliers.manage']);
        $routes->post('(:num)/durum', 'Panel\\Inventory::toggleSupplier/$1', ['filter' => 'permission:suppliers.manage']);
    });
    $routes->group('alislar', ['filter' => 'permission:purchases.manage'], static function ($routes): void {
        $routes->get('/', 'Panel\\Inventory::purchases');
        $routes->get('yeni', 'Panel\\Inventory::createPurchase', ['filter' => 'permission:purchases.create']);
        $routes->post('yeni', 'Panel\\Inventory::storePurchase', ['filter' => 'permission:purchases.create']);
        $routes->get('(:num)', 'Panel\\Inventory::showPurchase/$1');
        $routes->post('(:num)/mal-kabul', 'Panel\\Inventory::receivePurchase/$1', ['filter' => 'permission:purchases.receive']);
    });
    $routes->group('primler', static function ($routes): void {
        $routes->get('/', 'Panel\\Commissions::index');
        $routes->post('kural', 'Panel\\Commissions::storeRule', ['filter' => 'permission:commissions.manage']);
        $routes->post('hesapla', 'Panel\\Commissions::calculate', ['filter' => 'permission:commissions.manage']);
        $routes->post('donem', 'Panel\\Commissions::storePeriod', ['filter' => 'permission:commissions.manage']);
        $routes->post('donem/(:num)/ode', 'Panel\\Commissions::payPeriod/$1', ['filter' => 'permission:commissions.manage']);
    });
    $routes->group('raporlar', ['filter' => 'permission:reports.view'], static function ($routes): void {
        $routes->get('/', 'Panel\\Reports::index');
        $routes->get('disari-aktar/(:segment)/(:segment)', 'Panel\\Reports::export/$1/$2');
    });
});
