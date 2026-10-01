<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Models\PersonnelModel;

class Users extends AdminController
{
    private UserModel $m;

    public function __construct()
    {
        $this->m = new UserModel();
    }

    public function index()
    {
        return $this->render('admin/users/index', [
            'title' => 'จัดการผู้ใช้งาน',
            'users' => $this->m->getActiveJoined(),
        ]);
    }

    public function create()
    {
        $duplicate = false;

        $rules = [
            'username'     => 'required|max_length[50]',
            'password'     => 'required|min_length[6]',
            'full_name'    => 'required|max_length[150]',
            'role'         => 'required|in_list[admin,staff,warehouse,user]',
            'personnel_id' => 'permit_empty|is_natural_no_zero',
        ];

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $username = $this->request->getPost('username');
            if ($this->m->usernameExists($username)) {
                $duplicate = true;
            } else {
                $this->m->insert([
                    'username'     => $username,
                    'password'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'full_name'    => $this->request->getPost('full_name'),
                    'role'         => $this->request->getPost('role'),
                    'personnel_id' => $this->request->getPost('personnel_id') ?: null,
                    'is_active'    => 1,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'created_by'   => current_user_id(),
                ]);

                return redirect()->to('admin/users')->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
            }
        }

        return $this->render('admin/users/form', [
            'title'     => 'เพิ่มผู้ใช้งาน',
            'user'      => null,
            'duplicate' => $duplicate,
            'personnel' => (new PersonnelModel())->getActiveJoined(),
        ]);
    }

    public function edit($id)
    {
        $user = $this->m->find($id);
        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $duplicate = false;

        $rules = [
            'username'     => 'required|max_length[50]',
            'password'     => 'permit_empty|min_length[6]',
            'full_name'    => 'required|max_length[150]',
            'role'         => 'required|in_list[admin,staff,warehouse,user]',
            'personnel_id' => 'permit_empty|is_natural_no_zero',
        ];

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $username = $this->request->getPost('username');
            if ($this->m->usernameExists($username, (int) $id)) {
                $duplicate = true;
            } else {
                $update = [
                    'username'     => $username,
                    'full_name'    => $this->request->getPost('full_name'),
                    'role'         => $this->request->getPost('role'),
                    'personnel_id' => $this->request->getPost('personnel_id') ?: null,
                    'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
                    'updated_at'   => date('Y-m-d H:i:s'),
                    'updated_by'   => current_user_id(),
                ];
                // เปลี่ยนรหัสเฉพาะเมื่อกรอกใหม่ (เว้นว่าง = คงรหัสเดิม)
                $pw = $this->request->getPost('password');
                if (! empty($pw)) {
                    $update['password'] = password_hash($pw, PASSWORD_DEFAULT);
                }

                $this->m->update($id, $update);

                return redirect()->to('admin/users')->with('success', 'แก้ไขผู้ใช้งานเรียบร้อยแล้ว');
            }
        }

        return $this->render('admin/users/form', [
            'title'     => 'แก้ไขผู้ใช้งาน',
            'user'      => $user,
            'duplicate' => $duplicate,
            'personnel' => (new PersonnelModel())->getActiveJoined(),
        ]);
    }

    public function delete($id)
    {
        // กันลบบัญชีที่กำลังใช้งานอยู่
        if ((int) $id === (int) current_user_id()) {
            return redirect()->to('admin/users')->with('error', 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้');
        }

        $this->m->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => current_user_id()]);

        return redirect()->to('admin/users')->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function trashed()
    {
        return $this->render('admin/users/trashed', [
            'title' => 'รายการผู้ใช้งานที่ถูกลบ',
            'users' => $this->m->getDeletedJoined(),
        ]);
    }

    public function restore($id)
    {
        $this->m->update($id, ['deleted_at' => null, 'deleted_by' => null]);

        return redirect()->to('admin/users/trashed')->with('success', 'กู้คืนผู้ใช้งานเรียบร้อยแล้ว');
    }
}
