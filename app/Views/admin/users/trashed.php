<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="bg-white rounded-xl border border-slate-200">
    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
        <h3 class="font-medium text-slate-700">รายการผู้ใช้งานที่ถูกลบ</h3>
        <a href="<?= site_url('admin/users') ?>" class="inline-flex items-center gap-1.5 bg-slate-500 hover:bg-slate-600 text-white text-sm rounded-lg px-3 py-1.5"><i class="fas fa-arrow-left"></i> กลับ</a>
    </div>
    <div class="p-5">
        <table class="datatable table-auto w-full text-sm">
            <thead><tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="px-3 py-2 font-medium" style="width:60px">#</th>
                <th class="px-3 py-2 font-medium">ชื่อผู้ใช้งาน</th>
                <th class="px-3 py-2 font-medium">ชื่อ-สกุล</th>
                <th class="px-3 py-2 font-medium" style="width:110px">จัดการ</th>
            </tr></thead>
            <tbody>
            <?php $no=1; foreach ($users as $u): $fid='res-us-'.$u->id; ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2"><?= $no++ ?></td>
                    <td class="px-3 py-2"><?= esc($u->username) ?></td>
                    <td class="px-3 py-2"><?= esc($u->full_name) ?></td>
                    <td class="px-3 py-2">
                        <form id="<?= $fid ?>" method="post" action="<?= site_url('admin/users/restore/'.$u->id) ?>" class="hidden"><?= csrf_field() ?></form>
                        <button type="button" onclick="return confirmAction('<?= $fid ?>','กู้คืนผู้ใช้นี้?')" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded px-2 py-1 text-xs"><i class="fas fa-undo"></i> กู้คืน</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
