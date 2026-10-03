<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isStaff = in_array($role, ['admin','staff'], true); ?>
<div class="bg-white rounded-xl border border-slate-200">
    <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 border-b border-slate-100">
        <h3 class="font-medium text-slate-700">รายการใบเบิกพัสดุ ปีงบประมาณ <?= esc($selected_year) ?></h3>
        <div class="flex flex-wrap items-center gap-2">
            <form method="get" class="flex items-center">
                <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-2.5 py-1.5 text-slate-500"><i class="fas fa-calendar-alt"></i></span>
                <select name="year" onchange="this.form.submit()" class="rounded-r-lg border border-slate-300 px-2 py-1.5 text-sm outline-none focus:border-navy-500">
                    <?php foreach ($available_years as $y): ?><option value="<?= $y ?>" <?= $y==$selected_year?'selected':'' ?>>ปีงบ <?= $y ?></option><?php endforeach; ?>
                </select>
            </form>
            <?php if ($isStaff): ?>
                <a href="<?= site_url('documents/requisition/trashed') ?>" class="inline-flex items-center gap-1.5 bg-slate-500 hover:bg-slate-600 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-trash"></i> รายการที่ลบ</a>
                <a href="<?= site_url('documents/requisition/create') ?>" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-plus"></i> ลงรับใบเบิกใหม่</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="p-5">
        <table class="datatable table-auto w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="px-3 py-2 font-medium" style="width:50px">#</th>
                <th class="px-3 py-2 font-medium">เลขที่หน่วยจ่าย</th>
                <th class="px-3 py-2 font-medium">เลขที่ใบเบิก</th>
                <th class="px-3 py-2 font-medium">คลัง</th>
                <th class="px-3 py-2 font-medium">หน่วยเบิก</th>
                <th class="px-3 py-2 font-medium">วันที่รับ</th>
                <th class="px-3 py-2 font-medium text-center" style="width:60px">จำนวน</th>
                <th class="px-3 py-2 font-medium text-center" style="width:120px">สถานะ</th>
                <th class="px-3 py-2 font-medium" style="width:170px">จัดการ</th>
            </tr></thead>
            <tbody>
            <?php $no=1; foreach ($documents as $d): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2"><?= $no++ ?></td>
                    <td class="px-3 py-2 font-medium text-navy-700"><?= esc($d->delivery_unit_no) ?></td>
                    <td class="px-3 py-2"><?= esc($d->req_no) ?></td>
                    <td class="px-3 py-2"><?= esc($d->department_name) ?></td>
                    <td class="px-3 py-2"><?= esc($d->req_department_name) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap"><?= esc(thai_date($d->receive_date, true)) ?></td>
                    <td class="px-3 py-2 text-center"><?= esc($d->qty) ?></td>
                    <td class="px-3 py-2 text-center">
                        <?php if ($d->status==='delivered'): ?><span class="inline-block rounded bg-green-100 text-green-700 px-2 py-0.5 text-xs">รับพัสดุแล้ว</span>
                        <?php else: ?><span class="inline-block rounded bg-amber-100 text-amber-700 px-2 py-0.5 text-xs">สั่งจ่ายแล้ว</span><?php endif; ?>
                    </td>
                    <td class="px-3 py-2">
                        <?php if ($isStaff && $d->status==='received'): $fid='del-req-'.$d->id; ?>
                            <form id="<?= $fid ?>" method="post" action="<?= site_url('documents/requisition/delete/'.$d->id) ?>" class="hidden"><?= csrf_field() ?></form>
                        <?php endif; ?>
                        <div class="inline-flex items-center gap-1">
                            <a href="<?= site_url('documents/requisition/view/'.$d->id) ?>" class="inline-flex items-center gap-1 bg-sky-500 hover:bg-sky-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-eye"></i> ดู</a>
                            <?php if ($isStaff && $d->status==='received'): ?>
                                <a href="<?= site_url('documents/requisition/edit/'.$d->id) ?>" class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-edit"></i> แก้ไข</a>
                                <button type="button" onclick="return confirmAction('del-req-<?= $d->id ?>','ยืนยันการลบรายการนี้?')" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-trash"></i> ลบ</button>
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
