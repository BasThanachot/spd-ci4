<?php

namespace App\Controllers\Admin;

use App\Models\SettingModel;

class Settings extends AdminController
{
    private SettingModel $m;

    public function __construct()
    {
        $this->m = new SettingModel();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'system_name' => 'required|max_length[200]',
                'agency_name' => 'permit_empty|max_length[200]',
                'phone'       => 'permit_empty|max_length[50]',
            ];

            if ($this->validate($rules)) {
                $save = [
                    'system_name' => $this->request->getPost('system_name'),
                    'agency_name' => $this->request->getPost('agency_name'),
                    'phone'       => $this->request->getPost('phone'),
                ];

                // อัปโหลดโลโก้ (ไม่บังคับ)
                $logo = $this->request->getFile('logo');
                if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
                    $ext = strtolower($logo->getClientExtension());
                    if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg'], true)) {
                        return redirect()->to('admin/settings')->with('error', 'อนุญาตเฉพาะไฟล์รูปภาพ (jpg, png, gif, svg)');
                    }
                    if ($logo->getSize() > 2 * 1024 * 1024) {
                        return redirect()->to('admin/settings')->with('error', 'โลโก้ต้องไม่เกิน 2 MB');
                    }

                    $dir = FCPATH . 'uploads/logo';
                    if (! is_dir($dir)) {
                        @mkdir($dir, 0775, true);
                    }
                    // ลบโลโก้เก่า
                    $old = $this->m->get();
                    if ($old && $old->logo && is_file($dir . '/' . $old->logo)) {
                        @unlink($dir . '/' . $old->logo);
                    }
                    $newName = 'logo_' . time() . '.' . $ext;
                    $logo->move($dir, $newName);
                    $save['logo'] = $newName;
                }

                $this->m->save2($save);

                return redirect()->to('admin/settings')->with('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
            }
        }

        return $this->render('admin/settings/form', [
            'title'    => 'ตั้งค่าระบบ',
            'settings' => $this->m->get(),
        ]);
    }

    public function deleteLogo()
    {
        $setting = $this->m->get();
        if ($setting && $setting->logo) {
            $path = FCPATH . 'uploads/logo/' . $setting->logo;
            if (is_file($path)) {
                @unlink($path);
            }
            $this->m->save2(['logo' => null]);
        }

        return redirect()->to('admin/settings')->with('success', 'ลบโลโก้เรียบร้อยแล้ว');
    }
}
