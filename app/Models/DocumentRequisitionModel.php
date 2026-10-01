<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * โมเดลใบเบิกพัสดุ (documents_requisition) — พอร์ตจาก CI3
 */
class DocumentRequisitionModel extends Model
{
    protected $table          = 'documents_requisition';
    protected $primaryKey     = 'id';
    protected $returnType     = 'object';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'department_id', 'delivery_unit_no', 'req_no', 'delivery_no', 'order_no', 'contract_no',
        'receive_date', 'warehouse_date', 'delivery_date', 'req_department', 'qty',
        'req_file', 'delivery_file', 'remark', 'receive_name', 'delivery_name', 'status',
        'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by',
    ];

    private function baseSelect()
    {
        return $this->db->table('documents_requisition r')
            ->select('r.*, departments.department_name, departments.department_code,
                agencies.agency_name AS req_department_name,
                creator.full_name AS creator_name, editor.full_name AS editor_name')
            ->join('departments', 'departments.id = r.department_id', 'left')
            ->join('agencies', 'agencies.id = r.req_department', 'left')
            ->join('users creator', 'creator.id = r.created_by', 'left')
            ->join('users editor', 'editor.id = r.updated_by', 'left');
    }

    private function applySearch($b, ?string $search)
    {
        if ($search) {
            $b->groupStart()->like('r.req_no', $search)->orLike('r.delivery_unit_no', $search)->groupEnd();
        }

        return $b;
    }

    public function getByYear(string $ys, string $ye, ?string $search = null): array
    {
        $b = $this->baseSelect()->where('r.deleted_at', null)
            ->where('r.receive_date >=', $ys)->where('r.receive_date <=', $ye);
        $this->applySearch($b, $search);

        return $b->orderBy('r.receive_date', 'DESC')->orderBy('r.id', 'DESC')->get()->getResult();
    }

    public function getByDeptAndYear(int $deptId, string $ys, string $ye, ?string $search = null): array
    {
        $b = $this->baseSelect()->where('r.deleted_at', null)
            ->where('r.department_id', $deptId)
            ->where('r.receive_date >=', $ys)->where('r.receive_date <=', $ye);
        $this->applySearch($b, $search);

        return $b->orderBy('r.receive_date', 'DESC')->orderBy('r.id', 'DESC')->get()->getResult();
    }

    public function findWithJoins($id)
    {
        return $this->baseSelect()->where('r.id', $id)->get()->getRow();
    }

    public function getDeleted(): array
    {
        return $this->baseSelect()->where('r.deleted_at IS NOT NULL', null, false)
            ->orderBy('r.id', 'DESC')->get()->getResult();
    }

    public function searchDocs(array $f): array
    {
        $b = $this->baseSelect()->where('r.deleted_at', null);

        if (! empty($f['keyword'])) {
            $kw = $f['keyword'];
            $b->groupStart()
              ->like('r.req_no', $kw)->orLike('r.delivery_unit_no', $kw)->orLike('r.delivery_no', $kw)
              ->orLike('r.order_no', $kw)->orLike('r.contract_no', $kw)
              ->groupEnd();
        }
        if (! empty($f['date_from']))      $b->where('r.receive_date >=', $f['date_from']);
        if (! empty($f['date_to']))        $b->where('r.receive_date <=', $f['date_to']);
        if (! empty($f['department_id']))  $b->where('r.department_id', $f['department_id']);
        if (! empty($f['req_department'])) $b->where('r.req_department', $f['req_department']);
        if (! empty($f['status']))         $b->where('r.status', $f['status']);

        return $b->orderBy('r.receive_date', 'DESC')->orderBy('r.id', 'DESC')->get()->getResult();
    }

    /** ใบเบิกล่าสุดในช่วงปี (ใช้ในแดชบอร์ด) */
    public function getRecentByPeriod(string $from, string $to, int $limit = 5): array
    {
        return $this->baseSelect()->where('r.deleted_at', null)
            ->where('r.receive_date >=', $from)->where('r.receive_date <=', $to)
            ->orderBy('r.receive_date', 'DESC')->orderBy('r.id', 'DESC')
            ->limit($limit)->get()->getResult();
    }

    public function countByPeriod(string $from, string $to): int
    {
        return $this->db->table($this->table)->where('deleted_at', null)
            ->where('receive_date >=', $from)->where('receive_date <=', $to)->countAllResults();
    }
}
