<?php

namespace App\Controllers\Admin;

use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
use App\Models\RankModel;

class Personnel extends AdminController
{
    private PersonnelModel $m;

    public function __construct()
    {
        $this->m = new PersonnelModel();
    }

    private array $rules = [
        'first_name'    => 'required|max_length[100]',
        'last_name'     => 'required|max_length[100]',
        'position'      => 'permit_empty|max_length[150]',
        'rank_id'       => 'permit_empty|is_natural_no_zero',
        'department_id' => 'permit_empty|is_natural_no_zero',
    ];

    private function dropdowns(array &$data): void
    {
        $data['departments'] = (new DepartmentModel())->getActive('department_name');
        $data['ranks']       = (new RankModel())->getActive('code');
    }

    public function index()
    {
        return $this->render('admin/personnel/index', [
            'title'     => 'จัดการกำลังพล',
            'personnel' => $this->m->getActiveJoined(),
        ]);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'post' && $this->validate($this->rules)) {
            $this->m->insert([
                'rank_id'       => $this->request->getPost('rank_id') ?: null,
                'first_name'    => $this->request->getPost('first_name'),
                'last_name'     => $this->request->getPost('last_name'),
                'position'      => $this->request->getPost('position'),
                'department_id' => $this->request->getPost('department_id') ?: null,
                'created_at'    => date('Y-m-d H:i:s'),
                'created_by'    => current_user_id(),
            ]);

            return redirect()->to('admin/personnel')->with('success', 'เพิ่มกำลังพลเรียบร้อยแล้ว');
        }

        $data = ['title' => 'เพิ่มกำลังพล', 'person' => null];
        $this->dropdowns($data);

        return $this->render('admin/personnel/form', $data);
    }

    public function edit($id)
    {
        $person = $this->m->find($id);
        if (! $person) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'post' && $this->validate($this->rules)) {
            $this->m->update($id, [
                'rank_id'       => $this->request->getPost('rank_id') ?: null,
                'first_name'    => $this->request->getPost('first_name'),
                'last_name'     => $this->request->getPost('last_name'),
                'position'      => $this->request->getPost('position'),
                'department_id' => $this->request->getPost('department_id') ?: null,
                'updated_at'    => date('Y-m-d H:i:s'),
                'updated_by'    => current_user_id(),
            ]);

            return redirect()->to('admin/personnel')->with('success', 'แก้ไขกำลังพลเรียบร้อยแล้ว');
        }

        $data = ['title' => 'แก้ไขกำลังพล', 'person' => $person];
        $this->dropdowns($data);

        return $this->render('admin/personnel/form', $data);
    }

    public function delete($id)
    {
        $this->m->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => current_user_id()]);

        return redirect()->to('admin/personnel')->with('success', 'ลบกำลังพลเรียบร้อยแล้ว');
    }

    public function trashed()
    {
        return $this->render('admin/personnel/trashed', [
            'title'     => 'รายการกำลังพลที่ถูกลบ',
            'personnel' => $this->m->getDeletedJoined(),
        ]);
    }

    public function restore($id)
    {
        $this->m->update($id, ['deleted_at' => null, 'deleted_by' => null]);

        return redirect()->to('admin/personnel/trashed')->with('success', 'กู้คืนกำลังพลเรียบร้อยแล้ว');
    }
}
