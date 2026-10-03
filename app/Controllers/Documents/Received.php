<?php

namespace App\Controllers\Documents;

use App\Controllers\BaseController;
use App\Models\DocumentReceivedModel;
use App\Models\AgencyModel;
use App\Models\DepartmentModel;
use App\Models\PersonnelModel;
use App\Models\DocumentTypeModel;

/**
 * หนังสือรับ — พอร์ตจาก CI3 documents/Received
 * สิทธิ์แก้ไข (create/edit/delete/restore): เฉพาะ admin, staff
 */
class Received extends BaseController
{
    private DocumentReceivedModel $docs;

    public function __construct()
    {
        $this->docs = new DocumentReceivedModel();
    }

    /** กันผู้ที่ไม่มีสิทธิ์แก้ไข (user, warehouse) ออกจาก action ที่เปลี่ยนข้อมูล */
    private function denyIfReadonly()
    {
        if (! can_edit_documents()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ไม่มีสิทธิ์ทำรายการนี้');
        }
    }

    private function dropdowns(array &$data): void
    {
        $data['agencies']       = (new AgencyModel())->getActive('agency_name');
        $data['departments']    = (new DepartmentModel())->getActive('department_name');
        $data['personnel']      = (new PersonnelModel())->getActiveWithRank();
        $data['document_types'] = (new DocumentTypeModel())->getActive('type_name');
    }

    private function rules(): array
    {
        return [
            'received_date'    => 'required|valid_date[Y-m-d]',
            'subject'          => 'required|max_length[500]',
            'from_agency_id'   => 'required|is_natural_no_zero',
            'to_department_id' => 'required|is_natural_no_zero',
            'document_type_id' => 'required|is_natural_no_zero',
        ];
    }

    public function index()
    {
        // ปีงบประมาณ (เริ่ม 1 ต.ค.) — default ปีงบปัจจุบันเสมอ
        $currentBe  = to_fiscal_year_be();
        $selectedBe = (int) ($this->request->getGet('year') ?: $currentBe);
        $selectedBe = max(min($selectedBe, $currentBe), $currentBe - 4);
        $ys         = ($selectedBe - 543 - 1) . '-10-01';
        $ye         = ($selectedBe - 543) . '-09-30';
        $q          = $this->request->getGet('q');

        $data = [
            'title'           => 'รับหนังสือ',
            'q'               => $q,
            'selected_year'   => $selectedBe,
            'available_years' => range($currentBe, $currentBe - 4),
            'documents'       => $this->docs->getByYear($ys, $ye, $q ?: null),
        ];

        return $this->render('documents/received/index', $data);
    }

    public function view($id)
    {
        $doc = $this->docs->findWithJoins($id);
        if (! $doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('documents/received/view', ['title' => 'รายละเอียดรับหนังสือ', 'doc' => $doc]);
    }

    public function create()
    {
        $this->denyIfReadonly();
        $uploadError = null;

        if ($this->request->getMethod() === 'post' && $this->validate($this->rules())) {
            $upload = upload_attachment('attachment', 'received');

            if (! $upload['success']) {
                $uploadError = $upload['error'];
            } else {
                $receivedDate = $this->request->getPost('received_date');
                $receivedNo   = generate_doc_no('received');

                $this->docs->insert([
                    'received_no'              => $receivedNo,
                    'received_date'            => $receivedDate,
                    'subject'                  => $this->request->getPost('subject'),
                    'from_agency_id'           => $this->request->getPost('from_agency_id'),
                    'to_department_id'         => $this->request->getPost('to_department_id'),
                    'responsible_personnel_id' => $this->request->getPost('responsible_personnel_id') ?: null,
                    'note'                     => $this->request->getPost('note'),
                    'fiscal_year'              => to_buddhist_year($receivedDate),
                    'document_type_id'         => $this->request->getPost('document_type_id'),
                    'attachment_path'          => $upload['path'],
                    'created_at'               => date('Y-m-d H:i:s'),
                    'created_by'               => current_user_id(),
                ]);

                return redirect()->to('documents/received')
                    ->with('success', 'บันทึกรับหนังสือเรียบร้อยแล้ว เลขรับ ' . $receivedNo);
            }
        }

        $data = ['title' => 'เพิ่มรับหนังสือ', 'doc' => null, 'upload_error' => $uploadError];
        $this->dropdowns($data);

        return $this->render('documents/received/form', $data);
    }

    public function edit($id)
    {
        $this->denyIfReadonly();

        $doc = $this->docs->find($id);
        if (! $doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $uploadError = null;

        if ($this->request->getMethod() === 'post' && $this->validate($this->rules())) {
            $upload = upload_attachment('attachment', 'received');

            if (! $upload['success']) {
                $uploadError = $upload['error'];
            } else {
                $receivedDate = $this->request->getPost('received_date');

                $update = [
                    'received_date'            => $receivedDate,
                    'subject'                  => $this->request->getPost('subject'),
                    'from_agency_id'           => $this->request->getPost('from_agency_id'),
                    'to_department_id'         => $this->request->getPost('to_department_id'),
                    'responsible_personnel_id' => $this->request->getPost('responsible_personnel_id') ?: null,
                    'note'                     => $this->request->getPost('note'),
                    'fiscal_year'              => to_buddhist_year($receivedDate),
                    'document_type_id'         => $this->request->getPost('document_type_id'),
                    'updated_at'               => date('Y-m-d H:i:s'),
                    'updated_by'               => current_user_id(),
                ];
                if ($upload['path']) {
                    $update['attachment_path'] = $upload['path'];
                }

                $this->docs->update($id, $update);

                return redirect()->to('documents/received')->with('success', 'แก้ไขรับหนังสือเรียบร้อยแล้ว');
            }
        }

        $data = ['title' => 'แก้ไขรับหนังสือ', 'doc' => $doc, 'upload_error' => $uploadError];
        $this->dropdowns($data);

        return $this->render('documents/received/form', $data);
    }

    public function delete($id)
    {
        $this->denyIfReadonly();
        $this->docs->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => current_user_id()]);

        return redirect()->to('documents/received')->with('success', 'ลบรายการรับหนังสือเรียบร้อยแล้ว');
    }

    public function trashed()
    {
        $this->denyIfReadonly();

        return $this->render('documents/received/trashed', [
            'title'     => 'รายการรับหนังสือที่ถูกลบ',
            'documents' => $this->docs->getDeleted(),
        ]);
    }

    public function restore($id)
    {
        $this->denyIfReadonly();
        $this->docs->update($id, ['deleted_at' => null, 'deleted_by' => null]);

        return redirect()->to('documents/received/trashed')->with('success', 'กู้คืนรายการเรียบร้อยแล้ว');
    }
}
