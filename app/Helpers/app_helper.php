<?php

/**
 * app_helper — ฟังก์ชันช่วยเหลือเฉพาะระบบสารบรรณ อร. (พอร์ตจาก CI3)
 * โหลดอัตโนมัติผ่าน BaseController ($helpers)
 */

use Config\Database;

if (! function_exists('to_buddhist_year')) {
    /** คืนปี พ.ศ. ของวันที่ที่ระบุ (หรือวันนี้) */
    function to_buddhist_year(?string $date = null): int
    {
        $year = $date ? (int) date('Y', strtotime($date)) : (int) date('Y');

        return $year + 543;
    }
}

if (! function_exists('thai_month_names')) {
    function thai_month_names(bool $short = false): array
    {
        return $short
            ? [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.']
            : [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    }
}

if (! function_exists('thai_date')) {
    /** จัดรูปแบบวันที่เป็นข้อความไทย เช่น '2 กรกฎาคม 2569' */
    function thai_date($date = null, bool $short = false): string
    {
        $ts = is_int($date) ? $date : ($date ? strtotime($date) : time());
        if ($ts === false) {
            return '';
        }
        $day    = (int) date('j', $ts);
        $month  = (int) date('n', $ts);
        $months = thai_month_names($short);
        $be     = (int) date('Y', $ts) + 543;

        return $short
            ? $day . ' ' . $months[$month] . ' ' . substr((string) $be, -2)
            : $day . ' ' . $months[$month] . ' ' . $be;
    }
}

if (! function_exists('thai_datetime')) {
    function thai_datetime($date = null, bool $short = false): string
    {
        $ts = is_int($date) ? $date : ($date ? strtotime($date) : time());
        if ($ts === false) {
            return '';
        }

        return thai_date($ts, $short) . ' ' . date('H:i', $ts) . ' น.';
    }
}

if (! function_exists('to_fiscal_year_be')) {
    /**
     * คืนปีงบประมาณ (พ.ศ.) ของวันที่ — ปีงบเริ่ม ต.ค.
     * เดือน >= 10 นับเป็นปีงบถัดไป
     */
    function to_fiscal_year_be(?string $date = null): int
    {
        $ts    = $date ? strtotime($date) : time();
        $year  = (int) date('Y', $ts);
        $month = (int) date('n', $ts);
        if ($month >= 10) {
            $year++;
        }

        return $year + 543;
    }
}

if (! function_exists('generate_doc_no')) {
    /**
     * สร้างเลขหนังสือรันนิ่งรูปแบบ yyNNNNN (2 หลักท้ายของ พ.ศ. + ลำดับ 5 หลัก)
     * รีเซ็ตทุกปีงบประมาณ ใช้ ON DUPLICATE KEY UPDATE ปลอดภัยเมื่อเรียกพร้อมกัน
     *
     * @param string $type 'received' หรือ 'sent'
     * @return string|false
     */
    function generate_doc_no(string $type)
    {
        $db = Database::connect();

        $be = to_buddhist_year();
        $yy = substr((string) $be, -2);

        $ok = $db->query(
            'INSERT INTO doc_counters (counter_type, fiscal_year_be, last_number)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)',
            [$type, $be]
        );
        if (! $ok) {
            return false;
        }

        $row  = $db->query('SELECT LAST_INSERT_ID() AS next_number')->getRow();
        $next = (int) $row->next_number;

        return $yy . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('generate_delivery_unit_no')) {
    /**
     * สร้างเลขที่หน่วยจ่ายรูปแบบ ccNNNN (2 ตัวแรกของรหัสคลัง + ลำดับ 4 หลัก)
     * รีเซ็ตตามปีงบประมาณและคลัง (requisition_counters)
     *
     * @return string|false
     */
    function generate_delivery_unit_no(int $departmentId, ?string $departmentCode, ?string $date = null)
    {
        $db = \Config\Database::connect();

        $cc = strtoupper(substr(str_pad((string) $departmentCode, 2, '0'), 0, 2));
        $be = to_fiscal_year_be($date);

        $ok = $db->query(
            'INSERT INTO requisition_counters (department_id, fiscal_year_be, last_number)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)',
            [$departmentId, $be]
        );
        if (! $ok) {
            return false;
        }

        $row  = $db->query('SELECT LAST_INSERT_ID() AS next_number')->getRow();
        $next = (int) $row->next_number;

        return $cc . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('upload_attachment')) {
    /**
     * จัดการอัปโหลดไฟล์แนบ (ไม่บังคับ) — คืน array(success, path, error)
     * บันทึกไฟล์ที่ public/uploads/<subdir>/
     */
    function upload_attachment(string $field, string $subdir): array
    {
        $request = service('request');
        $file    = $request->getFile($field);

        if ($file === null || $file->getName() === '' || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'path' => null, 'error' => null];
        }

        if (! $file->isValid()) {
            return ['success' => false, 'path' => null, 'error' => $file->getErrorString()];
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            return ['success' => false, 'path' => null, 'error' => 'อนุญาตเฉพาะไฟล์ pdf, jpg, png'];
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            return ['success' => false, 'path' => null, 'error' => 'ไฟล์ต้องไม่เกิน 5 MB'];
        }

        $targetDir = FCPATH . 'uploads/' . $subdir;
        if (! is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return ['success' => true, 'path' => $subdir . '/' . $newName, 'error' => null];
    }
}

/* ---------- การตั้งค่าระบบ (system_settings) ---------- */

if (! function_exists('site_settings')) {
    /** อ่านแถวตั้งค่าระบบ (แคชไว้ต่อ 1 request) */
    function site_settings()
    {
        static $s = null;
        if ($s === null) {
            try {
                $s = (new \App\Models\SettingModel())->get() ?: false;
            } catch (\Throwable $e) {
                $s = false;
            }
        }

        return $s;
    }
}

if (! function_exists('site_setting')) {
    /** อ่านค่าตั้งค่ารายตัว พร้อมค่า default */
    function site_setting(string $key, string $default = ''): string
    {
        $s = site_settings();

        return ($s && ! empty($s->$key)) ? (string) $s->$key : $default;
    }
}

/* ---------- role / auth helpers ---------- */

if (! function_exists('current_user_id')) {
    function current_user_id()
    {
        return session()->get('user_id');
    }
}

if (! function_exists('current_role')) {
    function current_role()
    {
        return session()->get('role');
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return current_role() === 'admin';
    }
}

if (! function_exists('can_edit_documents')) {
    /** แก้ไขหนังสือรับ/ส่งได้เฉพาะ admin และ staff */
    function can_edit_documents(): bool
    {
        return in_array(current_role(), ['admin', 'staff'], true);
    }
}
