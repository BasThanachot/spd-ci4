<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit=!empty($document_type); $v=fn($f,$d='')=>esc(old($f,$isEdit?($document_type->$f??''):$d)); ?>
<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 p-5 sm:p-6 max-w-2xl">
    <?php if (session('errors')): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><ul class="list-disc list-inside"><?php foreach((array)session('errors') as $e):?><li><?= esc($e) ?></li><?php endforeach;?></ul></div><?php endif; ?>
    <form method="post" action="<?= $isEdit?site_url('admin/document-types/edit/'.$document_type->id):site_url('admin/document-types/create') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">ชื่อประเภทหนังสือ <span class="text-red-500">*</span></label>
            <input type="text" name="type_name" value="<?= $v('type_name') ?>" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        <div class="pt-2"><button class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2">บันทึก</button>
            <a href="<?= site_url('admin/document-types') ?>" class="ml-1 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">ยกเลิก</a></div>
    </form>
</div>
<?= $this->endSection() ?>
