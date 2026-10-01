<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
    $isEdit = ! empty($doc);
    $v = static fn($f, $d = '') => esc(old($f, $isEdit ? ($doc->$f ?? '') : $d));
    $sel = static fn($f, $optId, $default = '') => (string) old($f, $isEdit ? ($doc->$f ?? '') : $default) === (string) $optId ? 'selected' : '';
?>

<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 p-5 sm:p-6 max-w-4xl">
    <?php if (session('errors')): ?>
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5">
            <ul class="list-disc list-inside"><?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if (! empty($upload_error)): ?>
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><?= esc($upload_error) ?></div>
    <?php endif; ?>

    <?php $action = $isEdit ? site_url('documents/sent/edit/' . $doc->id) : site_url('documents/sent/create'); ?>
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">วันที่ส่ง</label>
                <input type="date" name="sent_date" value="<?= $v('sent_date', date('Y-m-d')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">ปี พ.ศ.</label>
                <input type="text" value="<?= $isEdit ? esc($doc->fiscal_year) : to_buddhist_year() ?>" disabled class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
            </div>
            <?php if ($isEdit): ?>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">เลขส่ง</label>
                    <input type="text" value="<?= esc($doc->sent_no) ?>" disabled class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
                </div>
            <?php endif; ?>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">เลขหน่วย</label>
                <input type="text" name="internal_no" value="<?= $v('internal_no') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">ชื่อหนังสือ</label>
            <input type="text" name="subject" value="<?= $v('subject') ?>" maxlength="500" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">ส่งจากแผนก</label>
                <select name="from_department_id" class="select2 w-full">
                    <option value="">- เลือกแผนก -</option>
                    <?php foreach ($departments as $dp): ?><option value="<?= $dp->id ?>" <?= $sel('from_department_id', $dp->id) ?>><?= esc($dp->department_name) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">ถึงหน่วยงาน</label>
                <select name="to_agency_id" class="select2 w-full">
                    <option value="">- เลือกหน่วยงาน -</option>
                    <?php foreach ($agencies as $a): ?><option value="<?= $a->id ?>" <?= $sel('to_agency_id', $a->id) ?>><?= esc($a->agency_name) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">เลขรับอ้างอิง</label>
                <input type="text" name="ref_received_no" value="<?= $v('ref_received_no') ?>" maxlength="50" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">ประเภทหนังสือ</label>
                <select name="document_type_id" class="select2 w-full">
                    <option value="">- เลือกประเภท -</option>
                    <?php foreach ($document_types as $t): ?><option value="<?= $t->id ?>" <?= $sel('document_type_id', $t->id) ?>><?= esc($t->type_name) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">หมายเหตุ</label>
            <textarea name="note" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"><?= $v('note') ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">ไฟล์แนบ (PDF/JPG/PNG ไม่เกิน 5MB)</label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-navy-700 file:text-sm hover:file:bg-navy-100">
            <?php if ($isEdit && ! empty($doc->attachment_path)): ?>
                <p class="mt-1 text-sm"><a href="<?= site_url('uploads/' . $doc->attachment_path) ?>" target="_blank" class="text-navy-600 underline">ไฟล์แนบปัจจุบัน <i class="fas fa-paperclip"></i></a></p>
            <?php endif; ?>
        </div>

        <div class="pt-2">
            <button class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2">บันทึก</button>
            <a href="<?= site_url('documents/sent') ?>" class="ml-1 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">ยกเลิก</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
