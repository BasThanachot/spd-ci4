<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="bg-white rounded-xl border border-slate-200">
    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
        <h3 class="font-medium text-slate-700">รายการหน่วยงาน (ภายนอก)</h3>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('admin/agencies/trashed') ?>" class="inline-flex items-center gap-1.5 bg-slate-500 hover:bg-slate-600 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-trash"></i> รายการที่ลบ</a>
            <a href="<?= site_url('admin/agencies/create') ?>" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-plus"></i> เพิ่มหน่วยงาน</a>
        </div>
    </div>
    <div class="p-5">
        <table class="datatable table-auto w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="px-3 py-2 font-medium" style="width:60px">#</th>
                <th class="px-3 py-2 font-medium">ชื่อหน่วยงาน</th>
                <th class="px-3 py-2 font-medium">ที่อยู่</th>
                <th class="px-3 py-2 font-medium">โทรศัพท์</th>
                <th class="px-3 py-2 font-medium" style="width:130px">จัดการ</th>
            </tr></thead>
            <tbody>
            <?php $no=1; foreach ($agencies as $a): $fid='del-ag-'.$a->id; ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2"><?= $no++ ?></td>
                    <td class="px-3 py-2 font-medium text-slate-700"><?= esc($a->agency_name) ?></td>
                    <td class="px-3 py-2"><?= esc($a->address) ?></td>
                    <td class="px-3 py-2"><?= esc($a->phone) ?></td>
                    <td class="px-3 py-2">
                        <form id="<?= $fid ?>" method="post" action="<?= site_url('admin/agencies/delete/'.$a->id) ?>" class="hidden"><?= csrf_field() ?></form>
                        <div class="inline-flex gap-1">
                            <a href="<?= site_url('admin/agencies/edit/'.$a->id) ?>" class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-edit"></i> แก้ไข</a>
                            <button type="button" onclick="return confirmAction('<?= $fid ?>','ยืนยันการลบรายการนี้?')" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-trash"></i> ลบ</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
