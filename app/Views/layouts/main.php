<?= "<!DOCTYPE html>\n" ?>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $sysName = site_setting('system_name', 'ระบบรับ-ส่งหนังสือ'); $sysAgency = site_setting('agency_name', 'กรมอู่ทหารเรือ'); $sysPhone = site_setting('phone', ''); ?>
    <title><?= isset($title) ? esc($title) . ' | ' : '' ?><?= esc($sysName) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/logo.png') ?>">

    <!-- Local assets (offline-ready) -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/sarabun.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/datatables/css/jquery.dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/datatables/css/buttons.dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/select2/css/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/toastr/toastr.min.css') ?>">
</head>
<body class="bg-slate-100 text-slate-800 text-[15px]">

<?php
    $user = $currentUser ?? null;
    $fullName = $user['full_name'] ?? '';
    $role = $user['role'] ?? '';
    $roleLabels = ['admin'=>'ผู้ดูแลระบบ','staff'=>'เจ้าหน้าที่ธุรการ','warehouse'=>'เจ้าหน้าที่คลัง','user'=>'ผู้ใช้งานทั่วไป'];
    $roleLabel = $roleLabels[$role] ?? '';
    $isAdmin = $role === 'admin';

    $uri  = service('uri');
    // ใช้ getSegments() (คืน array) อ่านแบบปลอดภัย — getSegment() จะโยน error
    // ถ้า index เกิน total+1 แม้ใส่ default (เช่นหน้า dashboard ที่มี segment เดียว)
    $segs = $uri->getSegments();
    $s1 = $segs[0] ?? '';
    $s2 = $segs[1] ?? '';
    $s3 = $segs[2] ?? '';

    // เมนู: [label, fa-icon, url, active]
    function nav_item($label, $icon, $url, $active) {
        $cls = $active ? 'bg-navy-600 text-white' : 'text-navy-100 hover:bg-navy-600/50 hover:text-white';
        echo '<a href="'.$url.'" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition '.$cls.'">'
           . '<i class="fas '.$icon.' w-5 text-center"></i><span>'.$label.'</span></a>';
    }
    function nav_header($t){ echo '<div class="px-4 pt-4 pb-1 text-[11px] uppercase tracking-wider text-navy-300">'.$t.'</div>'; }
?>

<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-navy-700 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200">
        <a href="<?= site_url('dashboard') ?>" class="h-16 flex items-center gap-3 px-5 border-b border-navy-600/60 shrink-0">
            <img src="<?= base_url('assets/logo.png') ?>" alt="โลโก้" class="w-10 h-10 object-contain shrink-0">
            <div class="text-white leading-tight">
                <div class="font-semibold text-sm"><?= esc($sysName) ?></div>
                <div class="text-navy-200 text-xs"><?= esc($sysAgency) ?></div>
            </div>
        </a>

        <!-- user panel -->
        <div class="px-5 py-3 border-b border-navy-600/60 text-navy-100">
            <div class="text-sm font-medium text-white"><?= esc($fullName) ?></div>
            <?php if ($roleLabel): ?><div class="text-xs text-navy-300 mt-0.5"><?= esc($roleLabel) ?></div><?php endif; ?>
            <?php if ($sysPhone): ?><div class="text-xs text-navy-300 mt-1"><i class="fas fa-phone fa-xs mr-1"></i><?= esc($sysPhone) ?></div><?php endif; ?>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-1">
            <?php nav_item('แดชบอร์ด', 'fa-tachometer-alt', site_url('dashboard'), $s1==='dashboard'||$s1===''); ?>
            <?php nav_item('รับหนังสือ', 'fa-inbox', site_url('documents/received'), $s1==='documents'&&$s2==='received'); ?>
            <?php nav_item('ส่งหนังสือ', 'fa-paper-plane', site_url('documents/sent'), $s1==='documents'&&$s2==='sent'); ?>
            <?php nav_item('รับใบเบิกพัสดุ', 'fa-boxes', site_url('documents/requisition'), $s1==='documents'&&$s2==='requisition'&&$s3!=='search'); ?>
            <?php nav_item('ค้นหาหนังสือ', 'fa-search', site_url('documents/search'), $s1==='documents'&&$s2==='search'); ?>
            <?php nav_item('ค้นหาใบเบิก', 'fa-boxes', site_url('documents/requisition/search'), false); ?>

            <?php if ($isAdmin): ?>
                <?php nav_header('ระบบจัดการข้อมูลพื้นฐาน'); ?>
                <?php nav_item('จัดการผู้ใช้งาน', 'fa-user-cog', site_url('admin/users'), $s1==='admin'&&$s2==='users'); ?>
                <?php nav_item('จัดการหน่วยงาน (ภายนอก)', 'fa-building', site_url('admin/agencies'), $s1==='admin'&&$s2==='agencies'); ?>
                <?php nav_item('จัดการแผนก', 'fa-sitemap', site_url('admin/departments'), $s1==='admin'&&$s2==='departments'); ?>
                <?php nav_item('จัดการกำลังพล', 'fa-users', site_url('admin/personnel'), $s1==='admin'&&$s2==='personnel'); ?>
                <?php nav_item('จัดการยศ', 'fa-medal', site_url('admin/ranks'), $s1==='admin'&&$s2==='ranks'); ?>
                <?php nav_item('จัดการประเภทหนังสือ', 'fa-tags', site_url('admin/document-types'), $s1==='admin'&&$s2==='document_types'); ?>
                <?php nav_item('ตั้งค่าระบบ', 'fa-cog', site_url('admin/settings'), $s1==='admin'&&$s2==='settings'); ?>
            <?php endif; ?>

            <?php nav_header('อื่นๆ'); ?>
            <?php nav_item('คู่มือการใช้งาน', 'fa-book', site_url('manual'), $s1==='manual'); ?>
        </nav>
    </aside>

    <div id="backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-20 bg-black/40 hidden lg:hidden"></div>

    <!-- Main -->
    <div class="flex-1 flex flex-col lg:ml-64 min-w-0">
        <!-- Navbar -->
        <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="p-2 -ml-2 text-slate-600 hover:bg-slate-100 rounded-lg"><i class="fas fa-bars"></i></button>
                <a href="<?= site_url('dashboard') ?>" class="hidden sm:inline text-sm text-slate-600 hover:text-navy-600">หน้าหลัก</a>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-slate-600"><i class="fas fa-user mr-1 text-slate-400"></i><?= esc($fullName) ?>
                    <?php if ($roleLabel): ?><span class="ml-1 inline-block rounded bg-slate-200 text-slate-600 px-2 py-0.5 text-xs"><?= esc($roleLabel) ?></span><?php endif; ?>
                </span>
                <a href="<?= site_url('auth/logout') ?>" class="text-red-600 hover:text-red-700" title="ออกจากระบบ"><i class="fas fa-sign-out-alt mr-1"></i>ออกจากระบบ</a>
            </div>
        </header>

        <!-- content-header -->
        <?php if (! empty($title)): ?>
            <div class="px-4 sm:px-6 pt-5 pb-1">
                <h1 class="text-xl font-semibold text-slate-800"><?= esc($title) ?></h1>
            </div>
        <?php endif; ?>

        <main class="flex-1 p-4 sm:p-6 pt-3">
            <?= $this->renderSection('content') ?>
        </main>

        <footer class="px-6 py-3 text-xs text-slate-400 border-t border-slate-200 flex justify-between">
            <span><?= esc($sysName) ?> <?= esc($sysAgency) ?><?= $sysPhone ? ' · โทร. '.esc($sysPhone) : '' ?></span><span>Version 1.0 (CI4)</span>
        </footer>
    </div>
</div>

<!-- scripts (local, offline-ready) -->
<script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/js/jszip.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/js/buttons.html5.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/js/buttons.print.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/toastr/toastr.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/chartjs/chart.umd.js') ?>"></script>
<script>
    function toggleSidebar(){document.getElementById('sidebar').classList.toggle('-translate-x-full');document.getElementById('backdrop').classList.toggle('hidden');}

    $(function () {
        $('.datatable').DataTable({
            dom: "<'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3'<'flex items-center gap-2'lB>f>" +
                 "<'overflow-x-auto'tr>" +
                 "<'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mt-3'ip>",
            buttons: [
                { extend:'excelHtml5', text:'<i class="fas fa-file-excel"></i> Excel', title: document.title.split('|')[0].trim(), exportOptions:{columns:':not(:last-child)'} },
                { extend:'print', text:'<i class="fas fa-print"></i> พิมพ์', title: document.title.split('|')[0].trim(), exportOptions:{columns:':not(:last-child)'} }
            ],
            language: {
                search:'ค้นหา:', lengthMenu:'แสดง _MENU_ รายการ',
                info:'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ', infoEmpty:'ไม่มีข้อมูล',
                zeroRecords:'ไม่พบข้อมูลที่ค้นหา', paginate:{previous:'ก่อนหน้า',next:'ถัดไป'}
            }
        });
        $('.select2').select2({ width:'100%', placeholder:'— เลือก —' });

        toastr.options = { positionClass:'toast-top-right', timeOut:3500, progressBar:true };
        <?php if ($m = session()->getFlashdata('success')): ?>toastr.success(<?= json_encode($m) ?>);<?php endif; ?>
        <?php if ($m = session()->getFlashdata('error')): ?>toastr.error(<?= json_encode($m) ?>);<?php endif; ?>
    });

    // ยืนยันการทำรายการแล้ว submit ฟอร์มที่ซ่อนไว้ (เหมือน confirmAction เดิม)
    function confirmAction(formId, message){
        Swal.fire({
            text: message || 'ยืนยันการทำรายการนี้?', icon:'warning',
            showCancelButton:true, confirmButtonText:'ยืนยัน', cancelButtonText:'ยกเลิก',
            confirmButtonColor:'#2b4a75'
        }).then(function(r){ if(r.isConfirmed){ document.getElementById(formId).submit(); } });
        return false;
    }
</script>
</body>
</html>
