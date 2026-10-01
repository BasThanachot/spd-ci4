<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $roleLabels=['admin'=>'ผู้ดูแลระบบ','staff'=>'เจ้าหน้าที่ธุรการ','warehouse'=>'เจ้าหน้าที่คลัง','user'=>'ผู้ใช้งานทั่วไป']; ?>
<div class="bg-white rounded-xl border border-slate-200">
    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
        <h3 class="font-medium text-slate-700">รายการผู้ใช้งาน</h3>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('admin/users/trashed') ?>" class="inline-flex items-center gap-1.5 bg-slate-500 hover:bg-slate-600 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-trash"></i> รายการที่ลบ</a>
            <a href="<?= site_url('admin/users/create') ?>" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-plus"></i> เพิ่มผู้ใช้งาน</a>
        </div>
    </div>
    <div class="p-5">
        <table class="datatable table-auto w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="px-3 py-2 font-medium" style="width:60px">#</th>
                <th class="px-3 py-2 font-medium">ชื่อผู้ใช้งาน</th>
                <th class="px-3 py-2 font-medium">ชื่อ-สกุล</th>
                <th class="px-3 py-2 font-medium">สิทธิ์</th>
                <th class="px-3 py-2 font-medium">กำลังพล</th>
                <th class="px-3 py-2 font-medium">สถานะ</th>
                <th class="px-3 py-2 font-medium" style="width:130px">จัดการ</th>
            </tr></thead>
            <tbody>
            <?php $no=1; foreach ($users as $u): $fid='del-us-'.$u->id; ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2"><?= $no++ ?></td>
                    <td class="px-3 py-2 font-medium text-slate-700"><?= esc($u->username) ?></td>
                    <td class="px-3 py-2"><?= esc($u->full_name) ?></td>
                    <td class="px-3 py-2"><span class="inline-block rounded bg-slate-100 text-slate-600 px-2 py-0.5 text-xs"><?= esc($roleLabels[$u->role] ?? $u->role) ?></span></td>
                    <td class="px-3 py-2"><?= esc($u->personnel_name) ?></td>
                    <td class="px-3 py-2">
                        <?php if ((int)$u->is_active===1): ?><span class="inline-block rounded bg-green-100 text-green-700 px-2 py-0.5 text-xs">ใช้งาน</span>
                        <?php else: ?><span class="inline-block rounded bg-red-100 text-red-700 px-2 py-0.5 text-xs">ระงับ</span><?php endif; ?>
                    </td>
                    <td class="px-3 py-2">
                        <form id="<?= $fid ?>" method="post" action="<?= site_url('admin/users/delete/'.$u->id) ?>" class="hidden"><?= csrf_field() ?></form>
                        <div class="inline-flex gap-1">
                            <a href="<?= site_url('admin/users/edit/'.$u->id) ?>" class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-edit"></i> แก้ไข</a>
                            <?php if ((int)$u->id !== (int)($currentUser['user_id'] ?? 0)): ?>
                            <button type="button" onclick="return confirmAction('<?= $fid ?>','ยืนยันการลบผู้ใช้นี้?')" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-trash"></i> ลบ</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
