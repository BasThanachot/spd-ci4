<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// หน้าแรก -> ไป dashboard (ถ้ายังไม่ล็อกอิน filter จะเด้งไป login เอง)
$routes->get('/', static function () {
    return redirect()->to('dashboard');
});

// ---- Auth (เปิดสาธารณะ) ----
// alias สั้น เผื่อมีการลิงก์/บุ๊กมาร์กมาที่ /login, /logout
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

$routes->get('auth/login', 'Auth::login');
$routes->post('auth/login', 'Auth::attemptLogin');
$routes->get('auth/logout', 'Auth::logout');

// ---- ส่วนที่ต้องล็อกอิน (คุมด้วย filter 'auth') ----
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    // ---- หนังสือรับ ----
    $routes->group('documents/received', static function ($routes) {
        $routes->get('/', 'Documents\Received::index');
        $routes->get('trashed', 'Documents\Received::trashed');
        $routes->get('create', 'Documents\Received::create');
        $routes->post('create', 'Documents\Received::create');
        $routes->get('view/(:num)', 'Documents\Received::view/$1');
        $routes->get('edit/(:num)', 'Documents\Received::edit/$1');
        $routes->post('edit/(:num)', 'Documents\Received::edit/$1');
        $routes->post('delete/(:num)', 'Documents\Received::delete/$1');
        $routes->post('restore/(:num)', 'Documents\Received::restore/$1');
    });

    // ---- หนังสือส่ง ----
    $routes->group('documents/sent', static function ($routes) {
        $routes->get('/', 'Documents\Sent::index');
        $routes->get('trashed', 'Documents\Sent::trashed');
        $routes->get('create', 'Documents\Sent::create');
        $routes->post('create', 'Documents\Sent::create');
        $routes->get('view/(:num)', 'Documents\Sent::view/$1');
        $routes->get('edit/(:num)', 'Documents\Sent::edit/$1');
        $routes->post('edit/(:num)', 'Documents\Sent::edit/$1');
        $routes->post('delete/(:num)', 'Documents\Sent::delete/$1');
        $routes->post('restore/(:num)', 'Documents\Sent::restore/$1');
    });

    // ---- ค้นหาหนังสือ (รวมรับ+ส่ง) ----
    $routes->get('documents/search', 'Documents\Search::index');

    // ---- คู่มือการใช้งาน ----
    $routes->get('manual', 'Manual::index');

    // ---- ใบเบิกพัสดุ ----
    $routes->group('documents/requisition', static function ($routes) {
        $routes->get('/', 'Documents\Requisition::index');
        $routes->get('search', 'Documents\Requisition::search');
        $routes->get('trashed', 'Documents\Requisition::trashed');
        $routes->get('create', 'Documents\Requisition::create');
        $routes->post('create', 'Documents\Requisition::create');
        $routes->get('view/(:num)', 'Documents\Requisition::view/$1');
        $routes->get('edit/(:num)', 'Documents\Requisition::edit/$1');
        $routes->post('edit/(:num)', 'Documents\Requisition::edit/$1');
        $routes->post('deliver/(:num)', 'Documents\Requisition::deliver/$1');
        $routes->post('cancel-delivery/(:num)', 'Documents\Requisition::cancelDelivery/$1');
        $routes->post('delete/(:num)', 'Documents\Requisition::delete/$1');
        $routes->post('restore/(:num)', 'Documents\Requisition::restore/$1');
    });

    // ---- ผู้ดูแลระบบ: ข้อมูลพื้นฐาน (CRUD + soft delete) ----
    $adminCrud = static function ($routes, string $seg, string $ctrl) {
        $routes->group($seg, static function ($routes) use ($ctrl) {
            $routes->get('/', $ctrl . '::index');
            $routes->get('trashed', $ctrl . '::trashed');
            $routes->get('create', $ctrl . '::create');
            $routes->post('create', $ctrl . '::create');
            $routes->get('edit/(:num)', $ctrl . '::edit/$1');
            $routes->post('edit/(:num)', $ctrl . '::edit/$1');
            $routes->post('delete/(:num)', $ctrl . '::delete/$1');
            $routes->post('restore/(:num)', $ctrl . '::restore/$1');
        });
    };
    $adminCrud($routes, 'admin/agencies', 'Admin\Agencies');
    $adminCrud($routes, 'admin/departments', 'Admin\Departments');
    $adminCrud($routes, 'admin/ranks', 'Admin\Ranks');
    $adminCrud($routes, 'admin/document-types', 'Admin\DocumentTypes');
    $adminCrud($routes, 'admin/personnel', 'Admin\Personnel');
    $adminCrud($routes, 'admin/users', 'Admin\Users');

    // ตั้งค่าระบบ
    $routes->get('admin/settings', 'Admin\Settings::index');
    $routes->post('admin/settings', 'Admin\Settings::index');
    $routes->post('admin/settings/delete-logo', 'Admin\Settings::deleteLogo');

    // เมนูโมดูลที่ยังไม่ได้ทำในสเตจนี้ -> หน้า "อยู่ระหว่างพัฒนา"
    $routes->get('soon', 'Dashboard::soon');
    $routes->get('soon/(:segment)', 'Dashboard::soon/$1');
});
