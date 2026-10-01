<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * โมเดลหนังสือรับ (documents_received) — พอร์ตจาก CI3 Document_received_model
 * ใช้ query builder ตรงเพื่อทำ join หลายตาราง
 */
class DocumentReceivedModel extends Model
{
    protected $table         = 'documents_received';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'received_no', 'received_date', 'subject', 'from_agency_id', 'to_department_id',
        'responsible_personnel_id', 'note', 'fiscal_year', 'document_type_id', 'attachment_path',
        'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by',
    ];

    /** base select + joins (คืน query builder) */
    private function baseSelect()
    {
        return $this->db->table('documents_received dr')
            ->select("dr.*, agencies.agency_name, departments.department_name,
                CONCAT_WS(' ', ranks.rank_name, personnel.first_name, personnel.last_name) AS responsible_name,
                document_types.type_name,
                creator.full_name AS creator_name, editor.full_name AS editor_name")
            ->join('agencies', 'agencies.id = dr.from_agency_id', 'left')
            ->join('departments', 'departments.id = dr.to_department_id', 'left')
            ->join('personnel', 'personnel.id = dr.responsible_personnel_id', 'left')
            ->join('ranks', 'ranks.id = personnel.rank_id', 'left')
            ->join('document_types', 'document_types.id = dr.document_type_id', 'left')
            ->join('users creator', 'creator.id = dr.created_by', 'left')
            ->join('users editor', 'editor.id = dr.updated_by', 'left');
    }

    /** รายการในปีงบประมาณ (ช่วงวันที่) + ค้นหาคำ */
    public function getByYear(string $yearStart, string $yearEnd, ?string $search = null): array
    {
        $b = $this->baseSelect()
            ->where('dr.deleted_at', null)
            ->where('dr.received_date >=', $yearStart)
            ->where('dr.received_date <=', $yearEnd);

        if ($search) {
            $b->groupStart()
              ->like('dr.subject', $search)
              ->orLike('dr.received_no', $search)
              ->groupEnd();
        }

        return $b->orderBy('dr.received_date', 'DESC')->orderBy('dr.id', 'DESC')->get()->getResult();
    }

    /** หนึ่งรายการพร้อม join (สำหรับหน้า view) */
    public function findWithJoins($id)
    {
        return $this->baseSelect()->where('dr.id', $id)->get()->getRow();
    }

    /** รายการที่ถูกลบ (ถังขยะ) */
    public function getDeleted(): array
    {
        return $this->baseSelect()
            ->where('dr.deleted_at IS NOT NULL', null, false)
            ->orderBy('dr.received_date', 'DESC')
            ->get()->getResult();
    }

    /** ค้นหาแบบหลายเงื่อนไข (ใช้ในหน้าค้นหาหนังสือรวม) */
    public function searchDocs(array $f): array
    {
        $b = $this->baseSelect()->where('dr.deleted_at', null);

        if (! empty($f['keyword'])) {
            $b->groupStart()->like('dr.subject', $f['keyword'])->orLike('dr.received_no', $f['keyword'])->groupEnd();
        }
        if (! empty($f['date_from']))        $b->where('dr.received_date >=', $f['date_from']);
        if (! empty($f['date_to']))          $b->where('dr.received_date <=', $f['date_to']);
        if (! empty($f['agency_id']))        $b->where('dr.from_agency_id', $f['agency_id']);
        if (! empty($f['department_id']))    $b->where('dr.to_department_id', $f['department_id']);
        if (! empty($f['document_type_id'])) $b->where('dr.document_type_id', $f['document_type_id']);

        return $b->orderBy('dr.received_date', 'DESC')->orderBy('dr.id', 'DESC')->get()->getResult();
    }

    /** นับแยกตามประเภทหนังสือ (ใช้ในกราฟแดชบอร์ด) */
    public function countByType(string $from, string $to): array
    {
        return $this->db->table('documents_received dr')
            ->select('document_types.type_name, COUNT(dr.id) AS total')
            ->join('document_types', 'document_types.id = dr.document_type_id', 'left')
            ->where('dr.deleted_at', null)
            ->where('dr.received_date >=', $from)
            ->where('dr.received_date <=', $to)
            ->groupBy('document_types.type_name')
            ->get()->getResult();
    }

    /** รายการรับล่าสุด (ใช้ในแดชบอร์ด) */
    public function getRecent(int $limit = 5): array
    {
        return $this->baseSelect()->where('dr.deleted_at', null)
            ->orderBy('dr.received_date', 'DESC')->orderBy('dr.id', 'DESC')
            ->limit($limit)->get()->getResult();
    }

    /** นับตามช่วงวันที่ (ใช้ในแดชบอร์ด) */
    public function countByPeriod(string $from, string $to): int
    {
        return $this->db->table($this->table)
            ->where('deleted_at', null)
            ->where('received_date >=', $from)
            ->where('received_date <=', $to)
            ->countAllResults();
    }
}
