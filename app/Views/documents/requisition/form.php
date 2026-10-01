<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit=!empty($doc);
  $v=fn($f,$d='')=>esc(old($f,$isEdit?($doc->$f??''):$d));
  $sel=fn($f,$optId)=> (string)old($f,$isEdit?($doc->$f??''):'')===(string)$optId?'selected':''; ?>
<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 p-5 sm:p-6 max-w-4xl">
    <?php if (session('errors')): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><ul class="list-disc list-inside"><?php foreach((array)session('errors') as $e):?><li><?= esc($e) ?></li><?php endforeach;?></ul></div><?php endif; ?>
    <?php if (!empty($upload_error)): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><?= esc($upload_error) ?></div><?php endif; ?>
    <form method="post" action="<?= $isEdit?site_url('documents/requisition/edit/'.$doc->id):site_url('documents/requisition/create') ?>" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-slate-600 mb-1">คลัง <span class="text-red-500">*</span></label>
                <select name="department_id" class="select2 w-full"><option value="">- เลือกคลัง -</option>
                    <?php foreach ($departments as $d): ?><option value="<?= $d->id ?>" <?= $sel('department_id',$d->id) ?>><?= esc(($d->department_code?'['.$d->department_code.'] ':'').$d->department_name) ?></option><?php endforeach; ?>
                </select></div>
            <?php if ($isEdit && $doc->delivery_unit_no): ?>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">เลขที่หน่วยจ่าย</label>
                <input type="text" value="<?= esc($doc->delivery_unit_no) ?>" disabled class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"></div>
            <?php endif; ?>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">วันที่รับ <span class="text-red-500">*</span></label>
                <input type="date" name="receive_date" value="<?= $v('receive_date', date('Y-m-d')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-slate-600 mb-1">เลขที่ใบเบิก <span class="text-red-500">*</span></label>
                <input type="text" name="req_no" value="<?= $v('req_no') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">ใบสั่งรับ</label>
                <input type="text" name="order_no" value="<?= $v('order_no') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">เลขที่สัญญา</label>
                <input type="text" name="contract_no" value="<?= $v('contract_no') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6"><label class="block text-sm font-medium text-slate-600 mb-1">หน่วยเบิก <span class="text-red-500">*</span></label>
                <select name="req_department" class="select2 w-full"><option value="">- เลือกหน่วยเบิก -</option>
                    <?php foreach ($agencies as $a): ?><option value="<?= $a->id ?>" <?= $sel('req_department',$a->id) ?>><?= esc($a->agency_name) ?></option><?php endforeach; ?>
                </select></div>
            <div class="md:col-span-3"><label class="block text-sm font-medium text-slate-600 mb-1">วันที่ลงคลัง</label>
                <input type="date" name="warehouse_date" value="<?= $v('warehouse_date') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div class="md:col-span-3"><label class="block text-sm font-medium text-slate-600 mb-1">จำนวนรายการ <span class="text-red-500">*</span></label>
                <input type="number" name="qty" min="0" value="<?= $v('qty') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">ไฟล์ใบเบิก (PDF/JPG/PNG ไม่เกิน 5MB)</label>
            <input type="file" name="req_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-navy-700 file:text-sm hover:file:bg-navy-100">
            <?php if ($isEdit && $doc->req_file): ?><p class="mt-1 text-sm"><a href="<?= site_url('uploads/'.$doc->req_file) ?>" target="_blank" class="text-navy-600 underline"><i class="fas fa-paperclip"></i> ไฟล์ใบเบิกปัจจุบัน</a></p><?php endif; ?></div>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">หมายเหตุ</label>
            <textarea name="remark" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"><?= $v('remark') ?></textarea></div>
        <div class="pt-2"><button class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2">บันทึก</button>
            <a href="<?= site_url('documents/requisition') ?>" class="ml-1 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">ยกเลิก</a></div>
    </form>
</div>
<?= $this->endSection() ?>
