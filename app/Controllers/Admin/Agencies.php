<?php

namespace App\Controllers\Admin;

use App\Models\AgencyModel;

class Agencies extends AdminController
{
    private AgencyModel $m;

    public function __construct()
    {
        $this->m = new AgencyModel();
    }

    private array $rules = [
        'agency_name' => 'required|max_length[200]',
        'address'     => 'permit_empty|max_length[300]',
        'phone'       => 'permit_empty|max_length[50]',
    ];

    public function index()
    {
        return $this->render('admin/agencies/index', [
            'title'    => 'จัดการหน่วยงาน (ภายนอก)',
            'agencies' => $this->m->getActive('agency_name'),
        ]);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'post' && $this->validate($this->rules)) {
            $this->m->insert([
                'agency_name' => $this->request->getPost('agency_name'),
                'address'     => $this->request->getPost('address'),
                'phone'       => $this->request->getPost('phone'),
                'created_at'  => date('Y-m-d H:i:s'),
                'created_by'  => current_user_id(),
            ]);

            return redirect()->to('admin/agencies')->with('success', 'เพิ่มหน่วยงานเรียบร้อยแล้ว');
        }

        return $this->render('admin/agencies/form', ['title' => 'เพิ่มหน่วยงาน', 'agency' => null]);
    }

    public function edit($id)
    {
        $agency = $this->m->find($id);
        if (! $agency) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'post' && $this->validate($this->rules)) {
            $this->m->update($id, [
                'agency_name' => $this->request->getPost('agency_name'),
                'address'     => $this->request->getPost('address'),
                'phone'       => $this->request->getPost('phone'),
                'updated_at'  => date('Y-m-d H:i:s'),
                'updated_by'  => current_user_id(),
            ]);

            return redirect()->to('admin/agencies')->with('success', 'แก้ไขหน่วยงานเรียบร้อยแล้ว');
        }

        return $this->render('admin/agencies/form', ['title' => 'แก้ไขหน่วยงาน', 'agency' => $agency]);
    }

    public function delete($id)
    {
        $this->m->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => current_user_id()]);

        return redirect()->to('admin/agencies')->with('success', 'ลบหน่วยงานเรียบร้อยแล้ว');
    }

    public function trashed()
    {
        return $this->render('admin/agencies/trashed', [
            'title'    => 'รายการหน่วยงานที่ถูกลบ',
            'agencies' => $this->m->onlyDeleted()->orderBy('agency_name')->findAll(),
        ]);
    }

    public function restore($id)
    {
        $this->m->update($id, ['deleted_at' => null, 'deleted_by' => null]);

        return redirect()->to('admin/agencies/trashed')->with('success', 'กู้คืนหน่วยงานเรียบร้อยแล้ว');
    }
}
