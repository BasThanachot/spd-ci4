<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $canEdit = can_edit_documents(); ?>

<div class="bg-white rounded-xl border border-slate-200">
    <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 border-b border-slate-100">
        <h3 class="font-medium text-slate-700">รายการส่งหนังสือ ปี <?= esc($selected_year) ?></h3>
        <div class="flex flex-wrap items-center gap-2">
            <form method="get" class="flex items-center">
                <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-2.5 py-1.5 text-slate-500"><i class="fas fa-calendar-alt"></i></span>
                <select name="year" onchange="this.form.submit()" class="rounded-r-lg border border-slate-300 px-2 py-1.5 text-sm outline-none focus:border-navy-500">
                    <?php foreach ($available_years as $y): ?><option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
                </select>
            </form>
            <?php if ($canEdit): ?>
                <a href="<?= site_url('documents/sent/trashed') ?>" class="inline-flex items-center gap-1.5 bg-slate-500 hover:bg-slate-600 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-trash"></i> รายการที่ลบ</a>
                <a href="<?= site_url('documents/sent/create') ?>" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-plus"></i> ส่งหนังสือใหม่</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="p-5">
        <table class="datatable table-auto w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-200">
                    <th class="px-3 py-2 font-medium" style="width:60px">#</th>
                    <th class="px-3 py-2 font-medium">เลขส่ง</th>
                    <th class="px-3 py-2 font-medium">เลขหน่วย</th>
                    <th class="px-3 py-2 font-medium">วันที่ส่ง</th>
                    <th class="px-3 py-2 font-medium">ชื่อหนังสือ</th>
                    <th class="px-3 py-2 font-medium">ส่งจากแผนก</th>
                    <th class="px-3 py-2 font-medium">ถึงหน่วยงาน</th>
                    <th class="px-3 py-2 font-medium">ประเภทหนังสือ</th>
                    <th class="px-3 py-2 font-medium" style="width:150px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($documents as $d): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-3 py-2"><?= $no++ ?></td>
                        <td class="px-3 py-2 font-medium text-navy-700"><?= esc($d->sent_no) ?></td>
                        <td class="px-3 py-2"><?= esc($d->internal_no) ?></td>
                        <td class="px-3 py-2 whitespace-nowrap"><?= esc(thai_date($d->sent_date, true)) ?></td>
                        <td class="px-3 py-2"><?= esc($d->subject) ?></td>
                        <td class="px-3 py-2"><?= esc($d->department_name) ?></td>
                        <td class="px-3 py-2"><?= esc($d->agency_name) ?></td>
                        <td class="px-3 py-2"><?= esc($d->type_name) ?></td>
                        <td class="px-3 py-2">
                            <?php if ($canEdit): $fid = 'del-sent-' . $d->id; ?>
                                <form id="<?= $fid ?>" method="post" action="<?= site_url('documents/sent/delete/' . $d->id) ?>" class="hidden"><?= csrf_field() ?></form>
                            <?php endif; ?>
                            <div class="inline-flex items-center gap-1">
                                <a href="<?= site_url('documents/sent/view/' . $d->id) ?>" class="inline-flex items-center gap-1 bg-sky-500 hover:bg-sky-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-eye"></i> ดู</a>
                                <?php if ($canEdit): ?>
                                    <a href="<?= site_url('documents/sent/edit/' . $d->id) ?>" class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-edit"></i> แก้ไข</a>
                                    <button type="button" onclick="return confirmAction('<?= $fid ?>','ยืนยันการลบรายการนี้?')" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-trash"></i> ลบ</button>
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
