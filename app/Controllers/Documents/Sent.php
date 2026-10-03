<?php

namespace App\Controllers\Documents;

use App\Controllers\BaseController;
use App\Models\DocumentSentModel;
use App\Models\AgencyModel;
use App\Models\DepartmentModel;
use App\Models\DocumentTypeModel;

/**
 * หนังสือส่ง — พอร์ตจาก CI3 documents/Sent
 * สิทธิ์แก้ไข: admin, staff เท่านั้น
 */
class Sent extends BaseController
{
    private DocumentSentModel $docs;

    public function __construct()
    {
        $this->docs = new DocumentSentModel();
    }

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
        $data['document_types'] = (new DocumentTypeModel())->getActive('type_name');
    }

    private function rules(): array
    {
        return [
            'sent_date'          => 'required|valid_date[Y-m-d]',
            'subject'            => 'required|max_length[500]',
            'from_department_id' => 'required|is_natural_no_zero',
            'to_agency_id'       => 'required|is_natural_no_zero',
            'document_type_id'   => 'required|is_natural_no_zero',
            'internal_no'        => 'permit_empty|max_length[100]',
            'ref_received_no'    => 'permit_empty|max_length[50]',
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

        return $this->render('documents/sent/index', [
            'title'           => 'ส่งหนังสือ',
            'q'               => $q,
            'selected_year'   => $selectedBe,
            'available_years' => range($currentBe, $currentBe - 4),
            'documents'       => $this->docs->getByYear($ys, $ye, $q ?: null),
        ]);
    }

    public function view($id)
    {
        $doc = $this->docs->findWithJoins($id);
        if (! $doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('documents/sent/view', ['title' => 'รายละเอียดส่งหนังสือ', 'doc' => $doc]);
    }

    public function create()
    {
        $this->denyIfReadonly();
        $uploadError = null;

        if ($this->request->getMethod() === 'post' && $this->validate($this->rules())) {
            $upload = upload_attachment('attachment', 'sent');

            if (! $upload['success']) {
                $uploadError = $upload['error'];
            } else {
                $sentDate = $this->request->getPost('sent_date');
                $sentNo   = generate_doc_no('sent');

                $this->docs->insert([
                    'sent_no'            => $sentNo,
                    'sent_date'          => $sentDate,
                    'internal_no'        => $this->request->getPost('internal_no'),
                    'subject'            => $this->request->getPost('subject'),
                    'from_department_id' => $this->request->getPost('from_department_id'),
                    'to_agency_id'       => $this->request->getPost('to_agency_id'),
                    'ref_received_no'    => $this->request->getPost('ref_received_no'),
                    'note'               => $this->request->getPost('note'),
                    'fiscal_year'        => to_buddhist_year($sentDate),
                    'document_type_id'   => $this->request->getPost('document_type_id'),
                    'attachment_path'    => $upload['path'],
                    'created_at'         => date('Y-m-d H:i:s'),
                    'created_by'         => current_user_id(),
                ]);

                return redirect()->to('documents/sent')
                    ->with('success', 'บันทึกส่งหนังสือเรียบร้อยแล้ว เลขส่ง ' . $sentNo);
            }
        }

        $data = ['title' => 'เพิ่มส่งหนังสือ', 'doc' => null, 'upload_error' => $uploadError];
        $this->dropdowns($data);

        return $this->render('documents/sent/form', $data);
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
            $upload = upload_attachment('attachment', 'sent');

            if (! $upload['success']) {
                $uploadError = $upload['error'];
            } else {
                $sentDate = $this->request->getPost('sent_date');

                $update = [
                    'sent_date'          => $sentDate,
                    'internal_no'        => $this->request->getPost('internal_no'),
                    'subject'            => $this->request->getPost('subject'),
                    'from_department_id' => $this->request->getPost('from_department_id'),
                    'to_agency_id'       => $this->request->getPost('to_agency_id'),
                    'ref_received_no'    => $this->request->getPost('ref_received_no'),
                    'note'               => $this->request->getPost('note'),
                    'fiscal_year'        => to_buddhist_year($sentDate),
                    'document_type_id'   => $this->request->getPost('document_type_id'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                    'updated_by'         => current_user_id(),
                ];
                if ($upload['path']) {
                    $update['attachment_path'] = $upload['path'];
                }

                $this->docs->update($id, $update);

                return redirect()->to('documents/sent')->with('success', 'แก้ไขส่งหนังสือเรียบร้อยแล้ว');
            }
        }

        $data = ['title' => 'แก้ไขส่งหนังสือ', 'doc' => $doc, 'upload_error' => $uploadError];
        $this->dropdowns($data);

        return $this->render('documents/sent/form', $data);
    }

    public function delete($id)
    {
        $this->denyIfReadonly();
        $this->docs->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => current_user_id()]);

        return redirect()->to('documents/sent')->with('success', 'ลบรายการส่งหนังสือเรียบร้อยแล้ว');
    }

    public function trashed()
    {
        $this->denyIfReadonly();

        return $this->render('documents/sent/trashed', [
            'title'     => 'รายการส่งหนังสือที่ถูกลบ',
            'documents' => $this->docs->getDeleted(),
        ]);
    }

    public function restore($id)
    {
        $this->denyIfReadonly();
        $this->docs->update($id, ['deleted_at' => null, 'deleted_by' => null]);

        return redirect()->to('documents/sent/trashed')->with('success', 'กู้คืนรายการเรียบร้อยแล้ว');
    }
}
