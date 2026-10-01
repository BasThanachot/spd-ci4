<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ตรวจว่าล็อกอินแล้วหรือยัง ถ้ายัง เด้งไปหน้า login
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('user_id')) {
            // จำหน้าที่ตั้งใจจะไป เพื่อพากลับหลังล็อกอิน
            session()->setFlashdata('redirect_url', current_url());

            return redirect()->to('auth/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ไม่ต้องทำอะไร
    }
}
