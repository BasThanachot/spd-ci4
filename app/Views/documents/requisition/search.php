<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 p-5 sm:p-6">
    <h3 class="font-medium text-slate-700 mb-4"><i class="fas fa-search text-navy-600"></i> เงื่อนไขการค้นหา</h3>
    <form method="get" action="<?= site_url('documents/requisition/search') ?>" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-5"><label class="block text-sm font-medium text-slate-600 mb-1">คำค้นหา</label>
                <input type="text" name="keyword" value="<?= esc($keyword) ?>" placeholder="เลขที่ใบเบิก / หน่วยจ่าย / สั่งจ่าย / ใบสั่งรับ / สัญญา" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium text-slate-600 mb-1">วันที่รับ ตั้งแต่</label>
                <input type="date" name="date_from" value="<?= esc($date_from) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium text-slate-600 mb-1">วันที่รับ ถึง</label>
                <input type="date" name="date_to" value="<?= esc($date_to) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div class="md:col-span-3"><label class="block text-sm font-medium text-slate-600 mb-1">สถานะ</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500">
                    <option value="">- ทั้งหมด -</option>
                    <option value="received" <?= $status==='received'?'selected':'' ?>>สั่งจ่ายแล้ว</option>
                    <option value="delivered" <?= $status==='delivered'?'selected':'' ?>>รับพัสดุแล้ว</option>
                </select></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <?php if ($role!=='warehouse'): ?>
            <div class="md:col-span-4"><label class="block text-sm font-medium text-slate-600 mb-1">คลัง</label>
                <select name="department_id" class="select2 w-full"><option value="">- ทั้งหมด -</option>
                    <?php foreach ($departments as $d): ?><option value="<?= $d->id ?>" <?= (string)$department_id===(string)$d->id?'selected':'' ?>><?= esc($d->department_name) ?></option><?php endforeach; ?>
                </select></div>
            <?php endif; ?>
            <div class="md:col-span-4"><label class="block text-sm font-medium text-slate-600 mb-1">หน่วยเบิก</label>
                <select name="req_department" class="select2 w-full"><option value="">- ทั้งหมด -</option>
                    <?php foreach ($agencies as $a): ?><option value="<?= $a->id ?>" <?= (string)$req_department===(string)$a->id?'selected':'' ?>><?= esc($a->agency_name) ?></option><?php endforeach; ?>
                </select></div>
        </div>
        <div><button class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2"><i class="fas fa-search"></i> ค้นหา</button>
            <a href="<?= site_url('documents/requisition/search') ?>" class="ml-1 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">ล้างเงื่อนไข</a></div>
    </form>
</div>

<?php if ($has_filter): ?>
<div class="bg-white rounded-xl border border-slate-200 mt-5">
    <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">ผลการค้นหา (<?= count($results) ?> รายการ)</h3></div>
    <div class="p-5">
        <?php if (empty($results)): ?><p class="text-slate-400">ไม่พบข้อมูลที่ตรงกับเงื่อนไขการค้นหา</p>
        <?php else: ?>
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
                <th class="px-3 py-2 font-medium" style="width:90px">จัดการ</th>
            </tr></thead>
            <tbody>
            <?php $no=1; foreach ($results as $d): ?>
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
                    <td class="px-3 py-2"><a href="<?= site_url('documents/requisition/view/'.$d->id) ?>" class="inline-flex items-center gap-1 bg-sky-500 hover:bg-sky-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-eye"></i> ดู</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
