<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isStaff=in_array($role,['admin','staff'],true);
  function reqrow($k,$v){ echo '<tr><th class="bg-slate-50 text-left align-top text-slate-500 font-medium px-3 py-2 border-r border-slate-200" style="width:220px">'.$k.'</th><td class="px-3 py-2">'.$v.'</td></tr>'; } ?>

<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 max-w-4xl">
    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
        <h3 class="font-medium text-slate-700">เลขที่หน่วยจ่าย: <strong><?= esc($doc->delivery_unit_no ?: '-') ?></strong>
            <?php if ($doc->status==='delivered'): ?><span class="ml-2 inline-block rounded bg-green-100 text-green-700 px-2 py-0.5 text-xs">รับพัสดุแล้ว</span>
            <?php else: ?><span class="ml-2 inline-block rounded bg-amber-100 text-amber-700 px-2 py-0.5 text-xs">สั่งจ่ายแล้ว</span><?php endif; ?>
        </h3>
    </div>
    <div class="p-5">
        <h6 class="text-slate-400 text-sm mb-2">ข้อมูลการรับ</h6>
        <table class="w-full text-sm border border-slate-200"><tbody class="divide-y divide-slate-200">
            <?php
            reqrow('คลัง', esc($doc->department_name));
            reqrow('วันที่รับ', $doc->receive_date?esc(thai_date($doc->receive_date,true)):'-');
            reqrow('วันที่ลงคลัง', $doc->warehouse_date?esc(thai_date($doc->warehouse_date,true)):'-');
            reqrow('เลขที่ใบเบิก', esc($doc->req_no ?: '-'));
            reqrow('ใบสั่งรับ', esc($doc->order_no ?: '-'));
            reqrow('เลขที่สัญญา', esc($doc->contract_no ?: '-'));
            reqrow('หน่วยเบิก', esc($doc->req_department_name ?: '-'));
            reqrow('จำนวนรายการ', esc($doc->qty ?: '-'));
            reqrow('ไฟล์ใบเบิก', $doc->req_file ? '<a href="'.site_url('uploads/'.$doc->req_file).'" target="_blank" class="text-navy-600 underline"><i class="fas fa-paperclip"></i> เปิดไฟล์ใบเบิก</a>' : '-');
            reqrow('หมายเหตุ', nl2br(esc($doc->remark ?: '-')));
            reqrow('บันทึกเมื่อ', $doc->created_at?esc(thai_datetime($doc->created_at,true)):'-');
            reqrow('ผู้บันทึก', esc($doc->creator_name ?: '-'));
            reqrow('แก้ไขล่าสุด', $doc->updated_at?esc(thai_datetime($doc->updated_at,true)):'-');
            reqrow('ผู้แก้ไข', esc($doc->editor_name ?: '-'));
            ?>
        </tbody></table>

        <?php if ($doc->status==='delivered'): ?>
        <h6 class="text-slate-400 text-sm mb-2 mt-5">ข้อมูลการสั่งจ่าย</h6>
        <table class="w-full text-sm border border-slate-200"><tbody class="divide-y divide-slate-200">
            <?php
            reqrow('เลขที่สั่งจ่าย', esc($doc->delivery_no ?: '-'));
            reqrow('วันที่สั่งจ่าย', $doc->delivery_date?esc(thai_date($doc->delivery_date,true)):'-');
            reqrow('ผู้จ่ายพัสดุ', esc($doc->delivery_name ?: '-'));
            reqrow('ผู้รับพัสดุ', esc($doc->receive_name ?: '-'));
            reqrow('ไฟล์ใบสั่งจ่าย', $doc->delivery_file ? '<a href="'.site_url('uploads/'.$doc->delivery_file).'" target="_blank" class="text-navy-600 underline"><i class="fas fa-paperclip"></i> เปิดไฟล์ใบสั่งจ่าย</a>' : '-');
            ?>
        </tbody></table>
        <?php endif; ?>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="<?= site_url('documents/requisition') ?>" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">กลับ</a>
            <?php if ($isStaff && $doc->status==='received'): ?>
                <a href="<?= site_url('documents/requisition/edit/'.$doc->id) ?>" class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg px-4 py-2"><i class="fas fa-edit"></i> แก้ไข</a>
            <?php endif; ?>
            <?php if ($role==='admin' && $doc->status==='delivered'): $cf='cancel-delivery-'.$doc->id; ?>
                <form id="<?= $cf ?>" method="post" action="<?= site_url('documents/requisition/cancel-delivery/'.$doc->id) ?>" class="hidden"><?= csrf_field() ?></form>
                <button type="button" onclick="return confirmAction('<?= $cf ?>','ยืนยันการยกเลิกสั่งจ่าย? ข้อมูลการสั่งจ่ายจะถูกลบออกทั้งหมด')" class="inline-flex items-center gap-1.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg px-4 py-2"><i class="fas fa-times-circle"></i> ยกเลิกสั่งจ่าย</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($doc->status==='received' && in_array($role,['admin','warehouse'],true)): ?>
<div class="bg-white rounded-xl border-t-4 border-emerald-500 border border-slate-200 max-w-4xl mt-5">
    <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700"><i class="fas fa-truck text-emerald-600"></i> สั่งจ่ายพัสดุ</h3></div>
    <div class="p-5">
        <?php if (session('errors')): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><ul class="list-disc list-inside"><?php foreach((array)session('errors') as $e):?><li><?= esc($e) ?></li><?php endforeach;?></ul></div><?php endif; ?>
        <form method="post" action="<?= site_url('documents/requisition/deliver/'.$doc->id) ?>" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-slate-600 mb-1">เลขที่สั่งจ่าย <span class="text-red-500">*</span></label>
                    <input type="text" name="delivery_no" value="<?= esc(old('delivery_no')) ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
                <div><label class="block text-sm font-medium text-slate-600 mb-1">วันที่สั่งจ่าย <span class="text-red-500">*</span></label>
                    <input type="date" name="delivery_date" value="<?= esc(old('delivery_date', date('Y-m-d'))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
                <div><label class="block text-sm font-medium text-slate-600 mb-1">ผู้จ่ายพัสดุ</label>
                    <input type="text" name="delivery_name" value="<?= esc(old('delivery_name')) ?>" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-slate-600 mb-1">ผู้รับพัสดุ</label>
                    <input type="text" name="receive_name" value="<?= esc(old('receive_name', $doc->receive_name ?? '')) ?>" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">ไฟล์ใบสั่งจ่าย (PDF/JPG/PNG ไม่เกิน 5MB)</label>
                <input type="file" name="delivery_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-emerald-700 file:text-sm hover:file:bg-emerald-100"></div>
            <div><button type="submit" onclick="return confirm('ยืนยันการสั่งจ่ายพัสดุ? ไม่สามารถแก้ไขได้หลังจากนี้')" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg px-5 py-2"><i class="fas fa-check"></i> ยืนยันสั่งจ่าย</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
