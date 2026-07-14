<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'field_sales';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'owner' => [
            'title'       => 'İşletme Sahibi',
            'description' => 'Sistemin tamamını yönetir ve kritik işlemleri onaylar.',
        ],
        'sales_manager' => [
            'title'       => 'Satış Yöneticisi',
            'description' => 'Müşteri dağılımını, saha ekibini ve sipariş onaylarını yönetir.',
        ],
        'field_sales' => [
            'title'       => 'Saha Personeli',
            'description' => 'Kendi müşterilerini, ziyaretlerini, tekliflerini ve siparişlerini yönetir.',
        ],
        'accounting' => [
            'title'       => 'Muhasebe',
            'description' => 'Cari, tahsilat, ödeme ve mali kayıtları yönetir.',
        ],
        'warehouse' => [
            'title'       => 'Depo',
            'description' => 'Stok, hazırlama, mal kabul ve sevkiyat işlemlerini yönetir.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'panel.access'              => 'Çalışan paneline giriş',
        'users.manage'              => 'Kullanıcı ve görev yönetimi',
        'employees.view'            => 'Personel kayıtlarını görüntüleme',
        'employees.manage'          => 'Personel kayıtlarını yönetme',
        'settings.manage'           => 'Sistem ayarlarını yönetme',
        'customers.view-all'        => 'Bütün müşterileri görme',
        'customers.view-own'        => 'Kendi müşterilerini görme',
        'customers.create'          => 'Müşteri oluşturma',
        'customers.assign'          => 'Müşteriyi personele atama',
        'visits.manage-own'         => 'Kendi ziyaretlerini yönetme',
        'visits.view-all'           => 'Bütün ziyaretleri görme',
        'products.view'             => 'Ürünleri ve satış fiyatlarını görme',
        'products.manage'           => 'Ürün ve satış fiyatlarını yönetme',
        'products.view-cost'        => 'Alış fiyatını görme',
        'orders.create'             => 'Teklif ve sipariş oluşturma',
        'orders.view-all'           => 'Bütün siparişleri görme',
        'orders.approve'            => 'Standart siparişleri onaylama',
        'orders.approve-high'       => 'Yüksek indirimli siparişi onaylama',
        'orders.fulfill'            => 'Sipariş hazırlama ve sevkiyat',
        'purchases.manage'          => 'Alış işlemlerini yönetme',
        'stock.manage'              => 'Stok hareketlerini yönetme',
        'collections.notify'        => 'Tahsilat bildirimi oluşturma',
        'finance.manage'            => 'Cari, tahsilat ve ödeme yönetimi',
        'commissions.view-own'      => 'Kendi primini görme',
        'commissions.view-all'      => 'Bütün primleri görme',
        'commissions.manage'        => 'Prim kurallarını ve dönemlerini yönetme',
        'reports.view'              => 'Yönetim raporlarını görme',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'owner' => [
            'panel.*', 'users.*', 'employees.*', 'settings.*', 'customers.*', 'visits.*',
            'products.*', 'orders.*', 'purchases.*', 'stock.*', 'collections.*',
            'finance.*', 'commissions.*', 'reports.*',
        ],
        'sales_manager' => [
            'panel.access', 'employees.*', 'customers.*', 'visits.*', 'orders.create',
            'orders.view-all', 'orders.approve', 'products.view', 'products.manage',
            'commissions.view-all', 'reports.view',
        ],
        'field_sales' => [
            'panel.access', 'customers.view-own', 'customers.create',
            'visits.manage-own', 'products.view', 'orders.create', 'collections.notify', 'commissions.view-own',
        ],
        'accounting' => [
            'panel.access', 'customers.view-all', 'customers.create', 'orders.view-all',
            'products.view', 'products.view-cost', 'purchases.manage', 'finance.manage',
            'commissions.view-all', 'reports.view',
        ],
        'warehouse' => [
            'panel.access', 'products.view', 'orders.view-all', 'orders.fulfill', 'purchases.manage', 'stock.manage',
        ],
    ];
}
