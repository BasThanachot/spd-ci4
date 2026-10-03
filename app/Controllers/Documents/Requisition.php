<?php

namespace App\Controllers\Documents;

use App\Controllers\BaseController;
use App\Models\DocumentRequisitionModel;
use App\Models\DepartmentModel;
use App\Models\AgencyModel;
use Config\Database;

/**
 * ใบเบิกพัสดุ — พอร์ตจาก CI3 documents/Requisition
 * workflow: ลงรับ (staff/admin) -> สั่งจ่าย (warehouse/admin) -> ยกเลิก (admin)
 * warehouse เห็นเฉพาะคลังของตนเอง
 */
class Requisition extends BaseController
{
    private DocumentRequisitionModel $docs;
    private int $currentDeptId = 0;
    private string $role = '';

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->docs = new DocumentRequisitionModel();
        $this->role = (string) current_role();

        // หา department ของ warehouse จากกำลังพลที่ผูกไว้
        if ($this->role === 'warehouse') {
            $row = Database::connect()->table('users u')
                ->select('personnel.department_id')
                ->join('personnel', 'personnel.id = u.personnel_id', 'left')
                ->where('u.id', current_user_id())
                ->get()->getRow();
            $this->currentDeptId = ($row && $row->department_id) ? (int) $row->department_id : 0;
        }
    }

    private function denyUnlessStaff()
    {
        if (! in_array($this->role, ['admin', 'staff'], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('สำหรับเจ้าหน้าที่ธุรการเท่านั้น');
        }
    }

    private function denyUnlessWarehouseOrAdmin()
    {
        if (! in_array($this->role, ['admin', 'warehouse'], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('สำหรับเจ้าหน้าที่คลังเท่านั้น');
        }
    }

    private function dropdowns(array &$data): void
    {
        $data['departments'] = (new DepartmentModel())->getActive('department_name');
        $data['agencies']    = (new AgencyModel())->getActive('agency_name');
    }

    private function createRules(): array
    {
        return [
            'department_id'  => 'required|is_natural_no_zero',
            'receive_date'   => 'required|valid_date[Y-m-d]',
            'req_no'         => 'required|max_length[100]',
            'order_no'       => 'permit_empty|max_length[100]',
            'contract_no'    => 'permit_empty|max_length[100]',
            'qty'            => 'required|is_natural',
            'req_department' => 'required|is_natural_no_zero',
        ];
    }

    public function index()
    {
        // ปีงบประมาณ (เริ่ม 1 ต.ค.) — default ปีงบปัจจุบันเสมอ
        $currentBe  = to_fiscal_year_be();
        $selectedBe = (int) ($this->request->getGet('year') ?: $currentBe);
        $selectedBe = max(min($selectedBe, $currentBe), $currentBe - 4);
        $q          = $this->request->getGet('q') ?: null;
        $ys         = ($selectedBe - 543 - 1) . '-10-01';
        $ye         = ($selectedBe - 543) . '-09-30';

        $documents = $this->role === 'warehouse'
            ? $this->docs->getByDeptAndYear($this->currentDeptId, $ys, $ye, $q)
            : $this->docs->getByYear($ys, $ye, $q);

        return $this->render('documents/requisition/index', [
            'title'           => 'รับใบเบิกพัสดุ',
            'role'            => $this->role,
            'selected_year'   => $selectedBe,
            'available_years' => range($currentBe, $currentBe - 4),
            'documents'       => $documents,
        ]);
    }

    public function search()
    {
        $filters = [
            'keyword'        => $this->request->getGet('keyword'),
            'date_from'      => $this->request->getGet('date_from'),
            'date_to'        => $this->request->getGet('date_to'),
            'department_id'  => $this->request->getGet('department_id'),
            'req_department' => $this->request->getGet('req_department'),
            'status'         => $this->request->getGet('status'),
        ];
        if ($this->role === 'warehouse') {
            $filters['department_id'] = $this->currentDeptId;
        }

        $hasFilter = (bool) ($filters['keyword'] || $filters['date_from'] || $filters['date_to']
            || $filters['department_id'] || $filters['req_department'] || $filters['status']);

        $data = $filters;
        $data['title']      = 'ค้นหาใบเบิกพัสดุ';
        $data['role']       = $this->role;
        $data['results']    = $hasFilter ? $this->docs->searchDocs($filters) : [];
        $data['has_filter'] = $hasFilter;
        $this->dropdowns($data);

        return $this->render('documents/requisition/search', $data);
    }

    public function view($id)
    {
        $doc = $this->docs->findWithJoins($id);
        if (! $doc || $doc->deleted_at) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if ($this->role === 'warehouse' && (int) $doc->department_id !== $this->currentDeptId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ไม่มีสิทธิ์ดูรายการนี้');
        }

        return $this->render('documents/requisition/view', [
            'title' => 'รายละเอียดใบเบิกพัสดุ', 'role' => $this->role, 'doc' => $doc,
        ]);
    }

    public function create()
    {
        $this->denyUnlessStaff();
        $uploadError = null;

        if ($this->request->getMethod() === 'post' && $this->validate($this->createRules())) {
            $deptId   = (int) $this->request->getPost('department_id');
            $dept     = (new DepartmentModel())->find($deptId);
            $deptCode = ($dept && $dept->department_code) ? $dept->department_code : 'XX';
            $recvDate = $this->request->getPost('receive_date');

            $deliveryUnitNo = generate_delivery_unit_no($deptId, $deptCode, $recvDate);

            $upload = upload_attachment('req_file', 'requisition');
            if (! $upload['success']) {
                $uploadError = $upload['error'];
            } else {
                $warehouseDate = $this->request->getPost('warehouse_date');
                $this->docs->insert([
                    'department_id'    => $deptId,
                    'delivery_unit_no' => $deliveryUnitNo,
                    'req_no'           => $this->request->getPost('req_no'),
                    'order_no'         => $this->request->getPost('order_no'),
                    'contract_no'      => $this->request->getPost('contract_no'),
                    'receive_date'     => $recvDate,
                    'warehouse_date'   => $warehouseDate ?: null,
                    'req_department'   => $this->request->getPost('req_department') ?: null,
                    'qty'              => $this->request->getPost('qty') ?: null,
                    'req_file'         => $upload['path'],
                    'remark'           => $this->request->getPost('remark'),
                    'status'           => 'received',
                    'created_at'       => date('Y-m-d H:i:s'),
                    'created_by'       => current_user_id(),
                ]);

                return redirect()->to('documents/requisition')
                    ->with('success', 'ลงรับใบเบิกพัสดุเรียบร้อยแล้ว เลขที่หน่วยจ่าย ' . $deliveryUnitNo);
            }
        }

        $data = ['title' => 'ลงรับใบเบิกพัสดุ', 'doc' => null, 'upload_error' => $uploadError];
        $this->dropdowns($data);

        return $this->render('documents/requisition/form', $data);
    }

    public function edit($id)
    {
        $this->denyUnlessStaff();

        $doc = $this->docs->find($id);
        if (! $doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if ($doc->status === 'delivered') {
            return redirect()->to('documents/requisition')->with('error', 'ไม่สามารถแก้ไขรายการที่รับพัสดุแล้ว');
        }

        $uploadError = null;

        if ($this->request->getMethod() === 'post' && $this->validate($this->createRules())) {
            $upload = upload_attachment('req_file', 'requisition');
            if (! $upload['success']) {
                $uploadError = $upload['error'];
            } else {
                $warehouseDate = $this->request->getPost('warehouse_date');
                $update = [
                    'department_id'  => (int) $this->request->getPost('department_id'),
                    'req_no'         => $this->request->getPost('req_no'),
                    'order_no'       => $this->request->getPost('order_no'),
                    'contract_no'    => $this->request->getPost('contract_no'),
                    'receive_date'   => $this->request->getPost('receive_date'),
                    'warehouse_date' => $warehouseDate ?: null,
                    'req_department' => $this->request->getPost('req_department') ?: null,
                    'qty'            => $this->request->getPost('qty') ?: null,
                    'remark'         => $this->request->getPost('remark'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                    'updated_by'     => current_user_id(),
                ];
                if ($upload['path']) {
                    $update['req_file'] = $upload['path'];
                }
                $this->docs->update($id, $update);

                return redirect()->to('documents/requisition')->with('success', 'แก้ไขใบเบิกพัสดุเรียบร้อยแล้ว');
            }
        }

        $data = ['title' => 'แก้ไขใบเบิกพัสดุ', 'doc' => $doc, 'upload_error' => $uploadError];
        $this->dropdowns($data);

        return $this->render('documents/requisition/form', $data);
    }

    public function deliver($id)
    {
        $this->denyUnlessWarehouseOrAdmin();

        $doc = $this->docs->find($id);
        if (! $doc || $doc->deleted_at) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if ($this->role === 'warehouse' && (int) $doc->department_id !== $this->currentDeptId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ไม่มีสิทธิ์ดำเนินการรายการนี้');
        }
        if ($doc->status === 'delivered') {
            return redirect()->to('documents/requisition/view/' . $id)->with('error', 'รายการนี้รับพัสดุแล้ว');
        }

        $rules = [
            'delivery_no'   => 'required|max_length[100]',
            'delivery_date' => 'required|valid_date[Y-m-d]',
            'delivery_name' => 'permit_empty|max_length[150]',
            'receive_name'  => 'permit_empty|max_length[150]',
        ];

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $upload = upload_attachment('delivery_file', 'requisition');
            if (! $upload['success']) {
                return redirect()->to('documents/requisition/view/' . $id)->with('error', $upload['error']);
            }

            $update = [
                'status'        => 'delivered',
                'delivery_no'   => $this->request->getPost('delivery_no'),
                'delivery_date' => $this->request->getPost('delivery_date'),
                'delivery_name' => $this->request->getPost('delivery_name'),
                'receive_name'  => $this->request->getPost('receive_name'),
            ];
            if ($upload['path']) {
                $update['delivery_file'] = $upload['path'];
            }
            $this->docs->update($id, $update);

            return redirect()->to('documents/requisition/view/' . $id)->with('success', 'สั่งจ่ายพัสดุเรียบร้อยแล้ว');
        }

        // validation ล้มเหลว -> กลับไปหน้า view พร้อม error
        return redirect()->to('documents/requisition/view/' . $id)->withInput();
    }

    public function cancelDelivery($id)
    {
        if ($this->role !== 'admin') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('สำหรับผู้ดูแลระบบเท่านั้น');
        }

        $doc = $this->docs->find($id);
        if (! $doc || $doc->deleted_at) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if ($doc->status !== 'delivered') {
            return redirect()->to('documents/requisition/view/' . $id)->with('error', 'รายการนี้ยังไม่ได้สั่งจ่าย');
        }

        $this->docs->update($id, [
            'status'        => 'received',
            'delivery_no'   => null,
            'delivery_date' => null,
            'delivery_name' => null,
            'receive_name'  => null,
            'delivery_file' => null,
        ]);

        return redirect()->to('documents/requisition/view/' . $id)->with('success', 'ยกเลิกการสั่งจ่ายเรียบร้อยแล้ว');
    }

    public function delete($id)
    {
        $this->denyUnlessStaff();
        $this->docs->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => current_user_id()]);

        return redirect()->to('documents/requisition')->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }

    public function trashed()
    {
        $this->denyUnlessStaff();

        return $this->render('documents/requisition/trashed', [
            'title'     => 'รายการใบเบิกพัสดุที่ถูกลบ',
            'documents' => $this->docs->getDeleted(),
        ]);
    }

    public function restore($id)
    {
        $this->denyUnlessStaff();
        $this->docs->update($id, ['deleted_at' => null, 'deleted_by' => null]);

        return redirect()->to('documents/requisition/trashed')->with('success', 'กู้คืนรายการเรียบร้อยแล้ว');
    }
}
