<?php
namespace App\Models;
use CodeIgniter\Model;
class SettingModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['system_name','agency_name','phone','logo'];

    /** ดึงแถวตั้งค่า (มีแถวเดียว) */
    public function get()
    {
        return $this->db->table('system_settings')->get()->getRow();
    }

    /** บันทึกแบบ upsert (อัปเดตแถวแรก หรือสร้างใหม่) */
    public function save2(array $data): void
    {
        $existing = $this->get();
        if ($existing) {
            $this->db->table('system_settings')->where('id', $existing->id)->update($data);
        } else {
            $this->db->table('system_settings')->insert($data);
        }
    }
}
