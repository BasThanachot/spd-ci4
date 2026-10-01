<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * โมเดลผู้ใช้ระบบ (ตาราง users) — พอร์ตจาก CI3 User_model
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'username', 'password', 'full_name', 'role', 'personnel_id',
        'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by',
    ];

    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }

    public function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $builder = $this->where('username', $username);
        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    /** รายการผู้ใช้พร้อมชื่อกำลังพลที่ผูกไว้ (หน้าจัดการผู้ใช้) */
    public function getActiveJoined(): array
    {
        return $this->db->table('users u')
            ->select("u.*, CONCAT_WS(' ', ranks.rank_name, personnel.first_name, personnel.last_name) AS personnel_name")
            ->join('personnel', 'personnel.id = u.personnel_id', 'left')
            ->join('ranks', 'ranks.id = personnel.rank_id', 'left')
            ->where('u.deleted_at', null)
            ->orderBy('u.username')
            ->get()->getResult();
    }

    public function getDeletedJoined(): array
    {
        return $this->db->table('users u')
            ->select("u.*, CONCAT_WS(' ', ranks.rank_name, personnel.first_name, personnel.last_name) AS personnel_name")
            ->join('personnel', 'personnel.id = u.personnel_id', 'left')
            ->join('ranks', 'ranks.id = personnel.rank_id', 'left')
            ->where('u.deleted_at IS NOT NULL', null, false)
            ->orderBy('u.username')
            ->get()->getResult();
    }
}
