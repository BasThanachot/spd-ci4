<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * ล็อกอิน / ล็อกเอาต์
 * พอร์ตตรรกะจาก CI3: Auth controller (bcrypt + is_active + soft delete)
 */
class Auth extends BaseController
{
    /**
     * แสดงหน้าฟอร์มล็อกอิน
     */
    public function login()
    {
        // ล็อกอินอยู่แล้ว -> ไป dashboard
        if ($this->session->get('user_id')) {
            return redirect()->to('dashboard');
        }

        return view('auth/login');
    }

    /**
     * รับข้อมูลจากฟอร์มและตรวจสอบ
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|trim',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('auth/login')
                ->withInput()
                ->with('error', 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน');
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        $users = new UserModel();
        $user  = $users->findByUsername($username);

        // ตรวจ: มีผู้ใช้ + เปิดใช้งาน + รหัสผ่านตรง (bcrypt)
        if ($user && (int) $user->is_active === 1 && password_verify($password, $user->password)) {
            $this->session->regenerate();
            $this->session->set([
                'user_id'   => $user->id,
                'username'  => $user->username,
                'full_name' => $user->full_name,
                'role'      => $user->role,
            ]);

            // พากลับไปหน้าที่ตั้งใจจะไปก่อนโดนเด้ง (ถ้ามี)
            $redirect = $this->session->getFlashdata('redirect_url');

            return redirect()->to($redirect ?: 'dashboard');
        }

        return redirect()->to('auth/login')
            ->withInput()
            ->with('error', 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง หรือบัญชีถูกระงับการใช้งาน');
    }

    /**
     * ออกจากระบบ
     */
    public function logout()
    {
        $this->session->destroy();

        return redirect()->to('auth/login')
            ->with('success', 'ออกจากระบบเรียบร้อยแล้ว');
    }
}
