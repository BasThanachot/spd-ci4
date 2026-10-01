<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 max-w-4xl">
    <div class="px-5 py-3 border-b border-slate-100">
        <h3 class="font-medium text-slate-700">เลขส่ง <?= esc($doc->sent_no) ?></h3>
    </div>
    <div class="p-5">
        <table class="w-full text-sm border border-slate-200">
            <tbody class="divide-y divide-slate-200">
                <?php
                $rows = [
                    'วันที่ส่ง'      => esc(thai_date($doc->sent_date, true)),
                    'ปี พ.ศ.'       => esc($doc->fiscal_year),
                    'เลขหน่วย'      => esc($doc->internal_no),
                    'ชื่อหนังสือ'     => esc($doc->subject),
                    'ส่งจากแผนก'    => esc($doc->department_name),
                    'ถึงหน่วยงาน'    => esc($doc->agency_name),
                    'เลขรับอ้างอิง'  => esc($doc->ref_received_no),
                    'ประเภทหนังสือ' => esc($doc->type_name),
                    'หมายเหตุ'      => nl2br(esc($doc->note ?? '')),
                ];
                foreach ($rows as $k => $val): ?>
                    <tr>
                        <th class="bg-slate-50 text-left align-top text-slate-500 font-medium px-3 py-2 border-r border-slate-200" style="width:220px"><?= $k ?></th>
                        <td class="px-3 py-2"><?= $val ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th class="bg-slate-50 text-left text-slate-500 font-medium px-3 py-2 border-r border-slate-200">ไฟล์แนบ</th>
                    <td class="px-3 py-2">
                        <?php if (! empty($doc->attachment_path)): ?>
                            <a href="<?= site_url('uploads/' . $doc->attachment_path) ?>" target="_blank" class="text-navy-600 underline"><i class="fas fa-paperclip"></i> เปิดไฟล์แนบ</a>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                </tr>
                <tr><th class="bg-slate-50 text-left text-slate-500 font-medium px-3 py-2 border-r border-slate-200">บันทึกเมื่อ</th><td class="px-3 py-2"><?= esc(thai_datetime($doc->created_at, true)) ?></td></tr>
                <tr><th class="bg-slate-50 text-left text-slate-500 font-medium px-3 py-2 border-r border-slate-200">ผู้บันทึก</th><td class="px-3 py-2"><?= esc($doc->creator_name ?: '-') ?></td></tr>
                <tr><th class="bg-slate-50 text-left text-slate-500 font-medium px-3 py-2 border-r border-slate-200">แก้ไขล่าสุด</th><td class="px-3 py-2"><?= $doc->updated_at ? esc(thai_datetime($doc->updated_at, true)) : '-' ?></td></tr>
                <tr><th class="bg-slate-50 text-left text-slate-500 font-medium px-3 py-2 border-r border-slate-200">ผู้แก้ไข</th><td class="px-3 py-2"><?= esc($doc->editor_name ?: '-') ?></td></tr>
            </tbody>
        </table>

        <div class="mt-4">
            <a href="<?= site_url('documents/sent') ?>" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">กลับ</a>
            <?php if (can_edit_documents()): ?>
                <a href="<?= site_url('documents/sent/edit/' . $doc->id) ?>" class="ml-1 inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg px-4 py-2"><i class="fas fa-edit"></i> แก้ไข</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
