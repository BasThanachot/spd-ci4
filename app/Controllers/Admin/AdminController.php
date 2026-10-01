<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * ฐานสำหรับส่วนผู้ดูแลระบบ — เข้าถึงได้เฉพาะ role admin
 * (ตรรกะเดียวกับ CI3 Admin_Controller)
 */
abstract class AdminController extends BaseController
{
    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        if (! is_admin()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('สำหรับผู้ดูแลระบบเท่านั้น');
        }
    }
}
