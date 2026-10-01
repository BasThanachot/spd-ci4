<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $v=fn($f,$d='')=>esc(old($f,$settings?($settings->$f??''):$d)); ?>
<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 p-5 sm:p-6 max-w-2xl">
    <?php if (session('errors')): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><ul class="list-disc list-inside"><?php foreach((array)session('errors') as $e):?><li><?= esc($e) ?></li><?php endforeach;?></ul></div><?php endif; ?>
    <form method="post" action="<?= site_url('admin/settings') ?>" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">ชื่อระบบ <span class="text-red-500">*</span></label>
            <input type="text" name="system_name" value="<?= $v('system_name','ระบบสารบรรณ อร.') ?>" maxlength="200" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">ชื่อหน่วยงาน</label>
            <input type="text" name="agency_name" value="<?= $v('agency_name') ?>" maxlength="200" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" value="<?= $v('phone') ?>" maxlength="50" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">โลโก้</label>
            <?php if ($settings && $settings->logo): ?>
                <div class="mb-2 flex items-center gap-3">
                    <img src="<?= site_url('uploads/logo/'.$settings->logo) ?>" alt="โลโก้" class="h-16 border border-slate-200 rounded p-1">
                    <?php $lf='del-logo'; ?>
                    <form id="<?= $lf ?>" method="post" action="<?= site_url('admin/settings/delete-logo') ?>" class="hidden"><?= csrf_field() ?></form>
                    <button type="button" onclick="return confirmAction('<?= $lf ?>','ยืนยันการลบโลโก้?')" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white rounded px-2 py-1 text-xs"><i class="fas fa-trash"></i> ลบโลโก้</button>
                </div>
            <?php endif; ?>
            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/svg+xml" class="w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-navy-700 file:text-sm hover:file:bg-navy-100">
            <small class="text-slate-400">jpg, png, gif, svg — ไม่เกิน 2MB</small></div>
        <div class="pt-2"><button class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2"><i class="fas fa-save mr-1"></i> บันทึก</button></div>
    </form>
</div>
<?= $this->endSection() ?>
