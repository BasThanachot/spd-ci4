<?php
namespace App\Controllers\Admin;
use App\Models\DepartmentModel;
class Departments extends AdminController
{
    private DepartmentModel $m;
    public function __construct(){ $this->m = new DepartmentModel(); }
    private array $rules = [
        'department_name' => 'required|max_length[150]',
        'department_code' => 'permit_empty|max_length[50]',
    ];
    public function index(){
        return $this->render('admin/departments/index', ['title'=>'จัดการแผนก','departments'=>$this->m->getActive('department_code')]);
    }
    public function create(){
        if ($this->request->getMethod()==='post' && $this->validate($this->rules)) {
            $this->m->insert([
                'department_code'=>$this->request->getPost('department_code'),
                'department_name'=>$this->request->getPost('department_name'),
                'created_at'=>date('Y-m-d H:i:s'),'created_by'=>current_user_id(),
            ]);
            return redirect()->to('admin/departments')->with('success','เพิ่มแผนกเรียบร้อยแล้ว');
        }
        return $this->render('admin/departments/form', ['title'=>'เพิ่มแผนก','department'=>null]);
    }
    public function edit($id){
        $row=$this->m->find($id);
        if(!$row){ throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
        if ($this->request->getMethod()==='post' && $this->validate($this->rules)) {
            $this->m->update($id,[
                'department_code'=>$this->request->getPost('department_code'),
                'department_name'=>$this->request->getPost('department_name'),
                'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>current_user_id(),
            ]);
            return redirect()->to('admin/departments')->with('success','แก้ไขแผนกเรียบร้อยแล้ว');
        }
        return $this->render('admin/departments/form', ['title'=>'แก้ไขแผนก','department'=>$row]);
    }
    public function delete($id){ $this->m->update($id,['deleted_at'=>date('Y-m-d H:i:s'),'deleted_by'=>current_user_id()]); return redirect()->to('admin/departments')->with('success','ลบแผนกเรียบร้อยแล้ว'); }
    public function trashed(){ return $this->render('admin/departments/trashed', ['title'=>'รายการแผนกที่ถูกลบ','departments'=>$this->m->onlyDeleted()->orderBy('department_name')->findAll()]); }
    public function restore($id){ $this->m->update($id,['deleted_at'=>null,'deleted_by'=>null]); return redirect()->to('admin/departments/trashed')->with('success','กู้คืนแผนกเรียบร้อยแล้ว'); }
}
