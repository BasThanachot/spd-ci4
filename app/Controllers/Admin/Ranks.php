<?php
namespace App\Controllers\Admin;
use App\Models\RankModel;
class Ranks extends AdminController
{
    private RankModel $m;
    public function __construct(){ $this->m = new RankModel(); }
    private array $rules = [
        'rank_name' => 'required|max_length[100]',
        'rank_short_name' => 'required|max_length[50]',
        'code' => 'required|max_length[20]',
    ];
    public function index(){ return $this->render('admin/ranks/index', ['title'=>'จัดการยศ','ranks'=>$this->m->getActive('code')]); }
    public function create(){
        if ($this->request->getMethod()==='post' && $this->validate($this->rules)) {
            $this->m->insert([
                'rank_name'=>$this->request->getPost('rank_name'),
                'rank_short_name'=>$this->request->getPost('rank_short_name'),
                'code'=>$this->request->getPost('code'),
                'created_at'=>date('Y-m-d H:i:s'),'created_by'=>current_user_id(),
            ]);
            return redirect()->to('admin/ranks')->with('success','เพิ่มยศเรียบร้อยแล้ว');
        }
        return $this->render('admin/ranks/form', ['title'=>'เพิ่มยศ','rank'=>null]);
    }
    public function edit($id){
        $row=$this->m->find($id);
        if(!$row){ throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
        if ($this->request->getMethod()==='post' && $this->validate($this->rules)) {
            $this->m->update($id,[
                'rank_name'=>$this->request->getPost('rank_name'),
                'rank_short_name'=>$this->request->getPost('rank_short_name'),
                'code'=>$this->request->getPost('code'),
                'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>current_user_id(),
            ]);
            return redirect()->to('admin/ranks')->with('success','แก้ไขยศเรียบร้อยแล้ว');
        }
        return $this->render('admin/ranks/form', ['title'=>'แก้ไขยศ','rank'=>$row]);
    }
    public function delete($id){ $this->m->update($id,['deleted_at'=>date('Y-m-d H:i:s'),'deleted_by'=>current_user_id()]); return redirect()->to('admin/ranks')->with('success','ลบยศเรียบร้อยแล้ว'); }
    public function trashed(){ return $this->render('admin/ranks/trashed', ['title'=>'รายการยศที่ถูกลบ','ranks'=>$this->m->onlyDeleted()->orderBy('rank_name')->findAll()]); }
    public function restore($id){ $this->m->update($id,['deleted_at'=>null,'deleted_by'=>null]); return redirect()->to('admin/ranks/trashed')->with('success','กู้คืนยศเรียบร้อยแล้ว'); }
}
