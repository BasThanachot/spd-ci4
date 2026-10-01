<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ฐานสำหรับโมเดลข้อมูลหลักที่ใช้ soft delete (ดึงเฉพาะที่ยังไม่ถูกลบ)
 * พอร์ตจาก CI3 MY_Model
 */
abstract class BaseSoftModel extends Model
{
    protected $returnType     = 'object';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $useTimestamps   = false;

    /** ดึงรายการที่ยังไม่ถูกลบ เรียงตามคอลัมน์ที่ระบุ */
    public function getActive(?string $orderBy = null): array
    {
        $builder = $this->where('deleted_at', null);
        if ($orderBy) {
            $builder->orderBy($orderBy);
        }

        return $builder->findAll();
    }
}
