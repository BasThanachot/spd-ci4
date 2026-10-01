<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
    $roles = [
        'admin'     => ['label' => 'ผู้ดูแลระบบ (Admin)', 'icon' => 'fa-user-shield'],
        'staff'     => ['label' => 'เจ้าหน้าที่ธุรการ (Staff)', 'icon' => 'fa-user-tie'],
        'warehouse' => ['label' => 'เจ้าหน้าที่คลังพัสดุ (Warehouse)', 'icon' => 'fa-warehouse'],
        'user'      => ['label' => 'ผู้ใช้งานทั่วไป (User)', 'icon' => 'fa-user'],
    ];
    $myRole     = $currentUser['role'] ?? '';
    $activeRole = isset($roles[$myRole]) ? $myRole : 'admin';
?>

<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200" x-data>
    <!-- role tabs -->
    <div class="flex flex-wrap border-b border-slate-200" id="manual-tabs">
        <?php foreach ($roles as $key => $r): ?>
            <button type="button" data-tab="<?= $key ?>"
                    class="manual-tab inline-flex items-center gap-1.5 px-4 py-3 text-sm border-b-2 -mb-px transition
                           <?= $key === $activeRole ? 'border-navy-600 text-navy-700 font-medium' : 'border-transparent text-slate-500 hover:text-navy-600' ?>">
                <i class="fas <?= $r['icon'] ?>"></i><?= $r['label'] ?>
                <?php if ($key === $activeRole): ?><span class="ml-1 rounded bg-navy-100 text-navy-700 px-2 py-0.5 text-xs">บทบาทของคุณ</span><?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="p-5 sm:p-6">
        <?php
        // แต่ละบทบาท: [intro, [ [หัวข้อ, ไอคอน, htmlเนื้อหา], ... ]]
        $panels = [
            'admin' => [
                'ผู้ดูแลระบบมีสิทธิ์เข้าถึงและจัดการได้ทุกส่วนของระบบ',
                [
                    ['แดชบอร์ด', 'fa-tachometer-alt', '<ul class="list-disc list-inside space-y-1"><li>ดูสรุปสถิติรับ/ส่งหนังสือและใบเบิกพัสดุ รายวัน รายเดือน รายปี</li><li>เลือกปีงบประมาณเพื่อดูข้อมูลย้อนหลังได้สูงสุด 5 ปี</li></ul>'],
                    ['รับหนังสือ / ส่งหนังสือ', 'fa-inbox', '<ul class="list-disc list-inside space-y-1"><li>เพิ่ม แก้ไข ลบ และกู้คืนรายการรับ-ส่งหนังสือได้ทั้งหมด</li><li>ค้นหา/กรองรายการตามปีและคำค้น พร้อมแนบไฟล์เอกสาร</li></ul>'],
                    ['รับใบเบิกพัสดุ', 'fa-boxes', '<ul class="list-disc list-inside space-y-1"><li>ลงรับใบเบิกใหม่ แก้ไข ลบ และกู้คืนได้ทุกรายการ (เหมือน Staff)</li><li>สั่งจ่ายพัสดุแทนเจ้าหน้าที่คลังได้ทุกแผนก (เหมือน Warehouse)</li><li><strong>ยกเลิกการสั่งจ่าย</strong> ได้ — สิทธิ์นี้มีเฉพาะ Admin เท่านั้น</li></ul>'],
                    ['จัดการข้อมูลพื้นฐาน', 'fa-cogs', '<p class="mb-1">เมนูนี้แสดงเฉพาะ Admin ใช้จัดการข้อมูลอ้างอิงที่เมนูอื่นเรียกใช้:</p><ul class="list-disc list-inside space-y-1"><li><strong>จัดการผู้ใช้งาน</strong> — เพิ่ม/แก้ไขบัญชีผู้ใช้และกำหนดบทบาท (admin/staff/warehouse/user)</li><li><strong>จัดการหน่วยงาน (ภายนอก)</strong> — รายชื่อหน่วยงานต้นทาง/ปลายทางของหนังสือ</li><li><strong>จัดการแผนก</strong> — แผนกภายในหน่วยงาน ใช้ผูกกับคลังพัสดุของ Warehouse</li><li><strong>จัดการกำลังพล</strong> — รายชื่อบุคลากร ใช้เลือกเป็นผู้รับผิดชอบเอกสาร</li><li><strong>จัดการยศ</strong> — ยศ/ตำแหน่งของกำลังพล</li><li><strong>จัดการประเภทหนังสือ</strong> — ประเภทของหนังสือรับ</li><li><strong>ตั้งค่าระบบ</strong> — ชื่อระบบ โลโก้ ชื่อหน่วยงาน เบอร์โทร</li></ul>'],
                ],
            ],
            'staff' => [
                'เจ้าหน้าที่ธุรการดูแลงานสารบรรณ (รับ-ส่งหนังสือ) และงานลงรับใบเบิกพัสดุ',
                [
                    ['รับหนังสือ', 'fa-inbox', '<ol class="list-decimal list-inside space-y-1"><li>ไปที่เมนู <strong>รับหนังสือ</strong> แล้วกด <strong>เพิ่มรับหนังสือ</strong></li><li>กรอกวันที่รับ ชื่อเรื่อง หน่วยงานที่ส่งมา แผนกผู้รับ ประเภทหนังสือ และแนบไฟล์เอกสาร</li><li>บันทึก ระบบจะออกเลขรับให้อัตโนมัติ</li><li>สามารถแก้ไข ลบ หรือกู้คืนรายการที่ลบไปแล้วได้จากเมนู <strong>รายการที่ลบ</strong></li></ol>'],
                    ['ส่งหนังสือ', 'fa-paper-plane', '<p>ขั้นตอนเหมือนกับรับหนังสือ แต่ใช้บันทึกหนังสือที่หน่วยงานส่งออกไปยังหน่วยงานภายนอก</p>'],
                    ['รับใบเบิกพัสดุ (ลงรับ)', 'fa-boxes', '<ol class="list-decimal list-inside space-y-1"><li>กดปุ่ม <strong>ลงรับใบเบิกใหม่</strong> เลือกคลัง (แผนก) กรอกเลขที่ใบเบิก วันที่รับ จำนวนรายการ และหน่วยเบิก</li><li>ระบบจะออก <strong>เลขที่หน่วยจ่าย</strong> ให้อัตโนมัติ</li><li>แก้ไขได้เฉพาะรายการที่ยัง <span class="rounded bg-amber-100 text-amber-700 px-1.5 py-0.5 text-xs">สั่งจ่ายแล้ว</span> เท่านั้น — เมื่อคลังรับพัสดุแล้วจะแก้ไขไม่ได้</li><li>ลบ/กู้คืนรายการได้เช่นเดียวกับรับ-ส่งหนังสือ</li></ol><p class="mt-2 text-slate-400 text-sm"><i class="fas fa-info-circle mr-1"></i>การสั่งจ่ายพัสดุเป็นหน้าที่ของเจ้าหน้าที่คลัง (Warehouse) ไม่ใช่ Staff</p>'],
                ],
            ],
            'warehouse' => [
                'เจ้าหน้าที่คลังพัสดุจะเห็นเฉพาะใบเบิกของคลัง (แผนก) ที่ตนเองสังกัดเท่านั้น',
                [
                    ['รับใบเบิกพัสดุ (สั่งจ่าย)', 'fa-boxes', '<ol class="list-decimal list-inside space-y-1"><li>ไปที่เมนู <strong>รับใบเบิกพัสดุ</strong> จะเห็นเฉพาะรายการของคลังตนเอง</li><li>เปิดรายการที่สถานะ <span class="rounded bg-amber-100 text-amber-700 px-1.5 py-0.5 text-xs">สั่งจ่ายแล้ว</span> แล้วกด <strong>ดู</strong></li><li>กรอกเลขที่สั่งจ่าย วันที่สั่งจ่าย ชื่อผู้จ่ายพัสดุ/ผู้รับพัสดุ และแนบไฟล์ใบสั่งจ่าย (ถ้ามี)</li><li>บันทึก สถานะจะเปลี่ยนเป็น <span class="rounded bg-green-100 text-green-700 px-1.5 py-0.5 text-xs">รับพัสดุแล้ว</span></li></ol><p class="mt-2 text-slate-400 text-sm"><i class="fas fa-info-circle mr-1"></i>ไม่สามารถลงรับใบเบิกใหม่ แก้ไข หรือลบรายการได้ (เป็นหน้าที่ของ Staff) และการยกเลิกการสั่งจ่ายทำได้โดย Admin เท่านั้น</p>'],
                ],
            ],
            'user' => [
                'ผู้ใช้งานทั่วไปมีสิทธิ์เข้าดูข้อมูลได้อย่างเดียว (Read-only) ไม่สามารถเพิ่ม แก้ไข หรือลบรายการใดๆ ได้',
                [
                    ['ดูและค้นหาข้อมูล', 'fa-search', '<ul class="list-disc list-inside space-y-1"><li>ดูรายการ <strong>รับหนังสือ</strong>, <strong>ส่งหนังสือ</strong> และ <strong>รับใบเบิกพัสดุ</strong> ได้ทั้งหมด</li><li>ใช้ช่องค้นหาและตัวกรองปีในแต่ละหน้าเพื่อค้นหารายการที่ต้องการ</li><li>กดปุ่ม <strong>ดู</strong> ที่แต่ละรายการเพื่อดูรายละเอียดและไฟล์แนบ</li></ul>'],
                ],
            ],
        ];
        ?>

        <?php foreach ($panels as $key => $panel): ?>
            <div class="manual-panel <?= $key === $activeRole ? '' : 'hidden' ?>" data-panel="<?= $key ?>">
                <p class="text-slate-500 text-sm mb-4"><?= esc($panel[0]) ?></p>
                <div class="space-y-2">
                    <?php foreach ($panel[1] as $i => $acc): ?>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <button type="button" class="manual-acc w-full flex items-center gap-2 px-4 py-3 text-left text-sm font-medium text-slate-700 bg-slate-50 hover:bg-slate-100">
                                <i class="fas <?= $acc[1] ?> text-navy-600 w-5 text-center"></i>
                                <span class="flex-1"><?= esc($acc[0]) ?></span>
                                <i class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
                            </button>
                            <div class="manual-acc-body px-4 py-3 text-sm text-slate-600 <?= $i === 0 ? '' : 'hidden' ?>">
                                <?= $acc[2] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // สลับแท็บบทบาท
    document.querySelectorAll('.manual-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tab = this.dataset.tab;
            document.querySelectorAll('.manual-tab').forEach(function (b) {
                var on = b.dataset.tab === tab;
                b.classList.toggle('border-navy-600', on);
                b.classList.toggle('text-navy-700', on);
                b.classList.toggle('font-medium', on);
                b.classList.toggle('border-transparent', !on);
                b.classList.toggle('text-slate-500', !on);
            });
            document.querySelectorAll('.manual-panel').forEach(function (p) {
                p.classList.toggle('hidden', p.dataset.panel !== tab);
            });
        });
    });
    // accordion
    document.querySelectorAll('.manual-acc').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var body = this.nextElementSibling;
            var chev = this.querySelector('.fa-chevron-down');
            body.classList.toggle('hidden');
            if (chev) chev.style.transform = body.classList.contains('hidden') ? '' : 'rotate(180deg)';
        });
    });
</script>
<?= $this->endSection() ?>
