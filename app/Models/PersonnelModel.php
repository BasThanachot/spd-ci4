<?php

namespace App\Models;

class PersonnelModel extends BaseSoftModel
{
    protected $table = 'personnel';
    protected $allowedFields = ['rank_id', 'first_name', 'last_name', 'department_id', 'position', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by'];

    private function baseSelect()
    {
        return $this->db->table('personnel p')
            ->select("p.*, departments.department_name, ranks.rank_name,
                CONCAT_WS(' ', ranks.rank_name, p.first_name, p.last_name) AS display_name,
                CONCAT_WS(' ', ranks.rank_name, p.first_name, p.last_name) AS full_label")
            ->join('departments', 'departments.id = p.department_id', 'left')
            ->join('ranks', 'ranks.id = p.rank_id', 'left');
    }

    /** รายการกำลังพลพร้อม ยศ+แผนก (สำหรับหน้าจัดการ และ dropdown) */
    public function getActiveJoined(?string $orderBy = null): array
    {
        return $this->baseSelect()
            ->where('p.deleted_at', null)
            ->orderBy($orderBy ?: 'p.first_name')
            ->get()->getResult();
    }

    /** คงชื่อเดิมที่ฟอร์มหนังสือรับ/ส่งเรียกใช้ */
    public function getActiveWithRank(): array
    {
        return $this->getActiveJoined();
    }

    public function getDeletedJoined(): array
    {
        return $this->baseSelect()
            ->where('p.deleted_at IS NOT NULL', null, false)
            ->orderBy('p.first_name')
            ->get()->getResult();
    }
}
