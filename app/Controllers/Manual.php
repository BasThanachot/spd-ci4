<?php

namespace App\Controllers;

/**
 * คู่มือการใช้งาน — พอร์ตจาก CI3 Manual
 */
class Manual extends BaseController
{
    public function index()
    {
        return $this->render('manual/index', ['title' => 'คู่มือการใช้งาน']);
    }
}
