<?php
namespace App\Controllers\Admin;
use App\Models\DocumentTypeModel;
class DocumentTypes extends AdminController
{
    private DocumentTypeModel $m;
    public function __construct(){ $this->m = new DocumentTypeModel(); }
    private array $rules = ['type_name' => 'required|max_length[150]'];
    public function index(){ return $this->render('admin/document_types/index', ['title'=>'จัดการประเภทหนังสือ','document_types'=>$this->m->getActive('type_name')]); }
    public function create(){
        if ($this->request->getMethod()==='post' && $this->validate($this->rules)) {
            $this->m->insert(['type_name'=>$this->request->getPost('type_name'),'created_at'=>date('Y-m-d H:i:s'),'created_by'=>current_user_id()]);
            return redirect()->to('admin/document-types')->with('success','เพิ่มประเภทหนังสือเรียบร้อยแล้ว');
        }
        return $this->render('admin/document_types/form', ['title'=>'เพิ่มประเภทหนังสือ','document_type'=>null]);
    }
    public function edit($id){
        $row=$this->m->find($id);
        if(!$row){ throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
        if ($this->request->getMethod()==='post' && $this->validate($this->rules)) {
            $this->m->update($id,['type_name'=>$this->request->getPost('type_name'),'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>current_user_id()]);
            return redirect()->to('admin/document-types')->with('success','แก้ไขประเภทหนังสือเรียบร้อยแล้ว');
        }
        return $this->render('admin/document_types/form', ['title'=>'แก้ไขประเภทหนังสือ','document_type'=>$row]);
    }
    public function delete($id){ $this->m->update($id,['deleted_at'=>date('Y-m-d H:i:s'),'deleted_by'=>current_user_id()]); return redirect()->to('admin/document-types')->with('success','ลบประเภทหนังสือเรียบร้อยแล้ว'); }
    public function trashed(){ return $this->render('admin/document_types/trashed', ['title'=>'รายการประเภทหนังสือที่ถูกลบ','document_types'=>$this->m->onlyDeleted()->orderBy('type_name')->findAll()]); }
    public function restore($id){ $this->m->update($id,['deleted_at'=>null,'deleted_by'=>null]); return redirect()->to('admin/document-types/trashed')->with('success','กู้คืนประเภทหนังสือเรียบร้อยแล้ว'); }
}
