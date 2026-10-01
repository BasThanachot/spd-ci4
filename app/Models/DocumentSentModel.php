<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * โมเดลหนังสือส่ง (documents_sent) — พอร์ตจาก CI3 Document_sent_model
 */
class DocumentSentModel extends Model
{
    protected $table          = 'documents_sent';
    protected $primaryKey     = 'id';
    protected $returnType     = 'object';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'sent_no', 'sent_date', 'internal_no', 'subject', 'from_department_id', 'to_agency_id',
        'ref_received_no', 'note', 'fiscal_year', 'document_type_id', 'attachment_path',
        'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by',
    ];

    private function baseSelect()
    {
        return $this->db->table('documents_sent ds')
            ->select("ds.*, departments.department_name, agencies.agency_name, document_types.type_name,
                creator.full_name AS creator_name, editor.full_name AS editor_name")
            ->join('departments', 'departments.id = ds.from_department_id', 'left')
            ->join('agencies', 'agencies.id = ds.to_agency_id', 'left')
            ->join('document_types', 'document_types.id = ds.document_type_id', 'left')
            ->join('users creator', 'creator.id = ds.created_by', 'left')
            ->join('users editor', 'editor.id = ds.updated_by', 'left');
    }

    public function getByYear(string $yearStart, string $yearEnd, ?string $search = null): array
    {
        $b = $this->baseSelect()
            ->where('ds.deleted_at', null)
            ->where('ds.sent_date >=', $yearStart)
            ->where('ds.sent_date <=', $yearEnd);

        if ($search) {
            $b->groupStart()
              ->like('ds.subject', $search)->orLike('ds.sent_no', $search)->orLike('ds.internal_no', $search)
              ->groupEnd();
        }

        return $b->orderBy('ds.sent_date', 'DESC')->orderBy('ds.id', 'DESC')->get()->getResult();
    }

    public function findWithJoins($id)
    {
        return $this->baseSelect()->where('ds.id', $id)->get()->getRow();
    }

    public function getDeleted(): array
    {
        return $this->baseSelect()
            ->where('ds.deleted_at IS NOT NULL', null, false)
            ->orderBy('ds.sent_date', 'DESC')
            ->get()->getResult();
    }

    /** ค้นหาแบบหลายเงื่อนไข (ใช้ในหน้าค้นหาหนังสือรวม) */
    public function searchDocs(array $f): array
    {
        $b = $this->baseSelect()->where('ds.deleted_at', null);

        if (! empty($f['keyword'])) {
            $b->groupStart()->like('ds.subject', $f['keyword'])->orLike('ds.sent_no', $f['keyword'])->orLike('ds.internal_no', $f['keyword'])->groupEnd();
        }
        if (! empty($f['date_from']))        $b->where('ds.sent_date >=', $f['date_from']);
        if (! empty($f['date_to']))          $b->where('ds.sent_date <=', $f['date_to']);
        if (! empty($f['agency_id']))        $b->where('ds.to_agency_id', $f['agency_id']);
        if (! empty($f['department_id']))    $b->where('ds.from_department_id', $f['department_id']);
        if (! empty($f['document_type_id'])) $b->where('ds.document_type_id', $f['document_type_id']);

        return $b->orderBy('ds.sent_date', 'DESC')->orderBy('ds.id', 'DESC')->get()->getResult();
    }

    /** นับแยกตามประเภทหนังสือ (ใช้ในกราฟแดชบอร์ด) */
    public function countByType(string $from, string $to): array
    {
        return $this->db->table('documents_sent ds')
            ->select('document_types.type_name, COUNT(ds.id) AS total')
            ->join('document_types', 'document_types.id = ds.document_type_id', 'left')
            ->where('ds.deleted_at', null)
            ->where('ds.sent_date >=', $from)
            ->where('ds.sent_date <=', $to)
            ->groupBy('document_types.type_name')
            ->get()->getResult();
    }

    /** รายการส่งล่าสุด (ใช้ในแดชบอร์ด) */
    public function getRecent(int $limit = 5): array
    {
        return $this->baseSelect()->where('ds.deleted_at', null)
            ->orderBy('ds.sent_date', 'DESC')->orderBy('ds.id', 'DESC')
            ->limit($limit)->get()->getResult();
    }

    public function countByPeriod(string $from, string $to): int
    {
        return $this->db->table($this->table)
            ->where('deleted_at', null)
            ->where('sent_date >=', $from)
            ->where('sent_date <=', $to)
            ->countAllResults();
    }
}
