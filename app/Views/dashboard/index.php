<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
    $recvLabels = []; $recvValues = [];
    foreach ($received_by_type as $r) { $recvLabels[] = $r->type_name ?: 'ไม่ระบุประเภท'; $recvValues[] = (int) $r->total; }
    $sentLabels = []; $sentValues = [];
    foreach ($sent_by_type as $r) { $sentLabels[] = $r->type_name ?: 'ไม่ระบุประเภท'; $sentValues[] = (int) $r->total; }
    $isCurrent = ($selected_year === $current_year);

    // การ์ดสถิติ: [value, label, sublabel, ไอคอน, สีพื้น, สีเข้ม]
    function stat_box($value, $label, $sub, $icon, $from, $to, $link = null, $linkLabel = 'ดูทั้งหมด') {
        echo '<div class="relative overflow-hidden rounded-xl text-white shadow-sm bg-gradient-to-br '.$from.' '.$to.'">';
        echo '  <div class="p-5">';
        echo '    <div class="text-3xl font-bold">'.number_format($value).'</div>';
        echo '    <div class="text-sm/relaxed mt-1 opacity-95">'.$label.($sub ? ' <span class="opacity-80 text-xs">'.$sub.'</span>' : '').'</div>';
        echo '  </div>';
        echo '  <i class="fas '.$icon.' absolute -right-2 -bottom-2 text-white/20 text-7xl"></i>';
        if ($link) {
            echo '  <a href="'.$link.'" class="relative block bg-black/10 hover:bg-black/20 text-center text-xs py-1.5 transition">'.$linkLabel.' <i class="fas fa-arrow-circle-right ml-1"></i></a>';
        }
        echo '</div>';
    }
?>

<!-- แถวบน: เลือกปีงบประมาณ -->
<?php
    $olderBe = $selected_year - 1;                 // ปีงบเก่ากว่า
    $newerBe = min($selected_year + 1, $current_year); // ปีงบใหม่กว่า (ไม่เกินปีปัจจุบัน)
    $minBe   = end($available_years);              // ปีงบเก่าสุดที่มีให้เลือก
?>
<div class="flex items-center justify-between mb-4 gap-2 flex-wrap">
    <h2 class="text-lg font-semibold text-slate-700">
        <i class="fas fa-chart-line text-navy-500 mr-1"></i>
        ปีงบประมาณ <?= esc($selected_year) ?>
        <?php if ($selected_year === $current_year): ?><span class="ml-1 align-middle text-xs bg-emerald-100 text-emerald-700 rounded-full px-2 py-0.5">ปัจจุบัน</span><?php endif; ?>
    </h2>
    <div class="flex items-center gap-2">
        <!-- ย้อนปีเก่า -->
        <a href="<?= $selected_year > $minBe ? site_url('dashboard').'?year='.$olderBe : '#' ?>"
           class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 <?= $selected_year <= $minBe ? 'opacity-40 pointer-events-none' : '' ?>"
           title="ปีงบก่อนหน้า"><i class="fas fa-chevron-left"></i></a>
        <!-- dropdown เลือกปีงบ -->
        <form method="get" class="flex items-center">
            <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-white px-2.5 py-2 text-slate-500"><i class="fas fa-calendar-alt"></i></span>
            <select name="year" onchange="this.form.submit()" class="rounded-r-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 bg-white">
                <?php foreach ($available_years as $y): ?><option value="<?= $y ?>" <?= $y===$selected_year?'selected':'' ?>>ปีงบ <?= $y ?></option><?php endforeach; ?>
            </select>
        </form>
        <!-- ไปปีใหม่ -->
        <a href="<?= $selected_year < $current_year ? site_url('dashboard').'?year='.$newerBe : '#' ?>"
           class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 <?= $selected_year >= $current_year ? 'opacity-40 pointer-events-none' : '' ?>"
           title="ปีงบถัดไป"><i class="fas fa-chevron-right"></i></a>
    </div>
</div>

<?php if ($isCurrent): ?>
<!-- สถิติวันนี้ / เดือนนี้ (เฉพาะปีปัจจุบัน) -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <?php stat_box($received_today, 'รับหนังสือวันนี้', '(เดือนนี้ '.number_format($received_month).' ฉบับ)', 'fa-inbox', 'from-sky-500', 'to-sky-600'); ?>
    <?php stat_box($sent_today, 'ส่งหนังสือวันนี้', '(เดือนนี้ '.number_format($sent_month).' ฉบับ)', 'fa-paper-plane', 'from-amber-500', 'to-amber-600'); ?>
    <?php stat_box($requisition_today, 'ใบเบิกพัสดุวันนี้', '(เดือนนี้ '.number_format($requisition_month).' ใบ)', 'fa-boxes', 'from-violet-500', 'to-violet-600'); ?>
</div>
<?php endif; ?>

<!-- สรุปรายปี -->
<h5 class="text-slate-500 text-sm font-medium mb-2">สรุปปีงบประมาณ <?= esc($selected_year) ?></h5>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <?php stat_box($received_year, 'รับหนังสือทั้งหมด ปีงบ '.$selected_year, '', 'fa-inbox', 'from-emerald-500', 'to-emerald-600', site_url('documents/received')); ?>
    <?php stat_box($sent_year, 'ส่งหนังสือทั้งหมด ปีงบ '.$selected_year, '', 'fa-paper-plane', 'from-rose-500', 'to-rose-600', site_url('documents/sent')); ?>
    <?php stat_box($requisition_year, 'ใบเบิกพัสดุทั้งหมด ปีงบ '.$selected_year, '', 'fa-boxes', 'from-cyan-500', 'to-cyan-600', site_url('documents/requisition')); ?>
</div>

<!-- กราฟแยกประเภท -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">รับหนังสือแยกตามประเภท (ปีงบ <?= esc($selected_year) ?>)</h3></div>
        <div class="p-5"><canvas id="chartReceived" height="200"></canvas></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">ส่งหนังสือแยกตามประเภท (ปีงบ <?= esc($selected_year) ?>)</h3></div>
        <div class="p-5"><canvas id="chartSent" height="200"></canvas></div>
    </div>
</div>

<!-- รายการล่าสุด -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- รับล่าสุด -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-medium text-slate-700">รับหนังสือล่าสุด</h3>
            <span class="text-xs text-slate-400"><i class="fas fa-hand-pointer mr-1"></i>คลิกแถวเพื่อดู</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2 font-medium">เลขรับ</th><th class="px-4 py-2 font-medium">วันที่</th><th class="px-4 py-2 font-medium">ชื่อหนังสือ</th><th class="px-2 py-2"></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($recent_received as $d): ?>
                <tr class="group hover:bg-navy-50 cursor-pointer transition" onclick="location.href='<?= site_url('documents/received/view/'.$d->id) ?>'">
                    <td class="px-4 py-2"><span class="text-navy-600 font-medium group-hover:underline"><?= esc($d->received_no) ?></span></td>
                    <td class="px-4 py-2 whitespace-nowrap"><?= esc(thai_date($d->received_date, true)) ?></td>
                    <td class="px-4 py-2 max-w-[160px] truncate" title="<?= esc($d->subject) ?>"><?= esc($d->subject) ?></td>
                    <td class="px-2 py-2 text-right text-slate-300 group-hover:text-navy-500"><i class="fas fa-eye"></i></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_received)): ?><tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">ไม่มีข้อมูล</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- ส่งล่าสุด -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-medium text-slate-700">ส่งหนังสือล่าสุด</h3>
            <span class="text-xs text-slate-400"><i class="fas fa-hand-pointer mr-1"></i>คลิกแถวเพื่อดู</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2 font-medium">เลขส่ง</th><th class="px-4 py-2 font-medium">วันที่</th><th class="px-4 py-2 font-medium">ชื่อหนังสือ</th><th class="px-2 py-2"></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($recent_sent as $d): ?>
                <tr class="group hover:bg-navy-50 cursor-pointer transition" onclick="location.href='<?= site_url('documents/sent/view/'.$d->id) ?>'">
                    <td class="px-4 py-2"><span class="text-navy-600 font-medium group-hover:underline"><?= esc($d->sent_no) ?></span></td>
                    <td class="px-4 py-2 whitespace-nowrap"><?= esc(thai_date($d->sent_date, true)) ?></td>
                    <td class="px-4 py-2 max-w-[160px] truncate" title="<?= esc($d->subject) ?>"><?= esc($d->subject) ?></td>
                    <td class="px-2 py-2 text-right text-slate-300 group-hover:text-navy-500"><i class="fas fa-eye"></i></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_sent)): ?><tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">ไม่มีข้อมูล</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- ใบเบิกล่าสุด -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-medium text-slate-700">ใบเบิกพัสดุล่าสุด (ปีงบ <?= esc($selected_year) ?>)</h3>
            <span class="text-xs text-slate-400"><i class="fas fa-hand-pointer mr-1"></i>คลิกแถวเพื่อดู</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2 font-medium">เลขที่ใบเบิก</th><th class="px-4 py-2 font-medium">วันที่รับ</th><th class="px-4 py-2 font-medium">คลัง</th><th class="px-2 py-2"></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($recent_requisition as $d): ?>
                <tr class="group hover:bg-navy-50 cursor-pointer transition" onclick="location.href='<?= site_url('documents/requisition/view/'.$d->id) ?>'">
                    <td class="px-4 py-2"><span class="text-navy-600 font-medium group-hover:underline"><?= esc($d->req_no) ?></span></td>
                    <td class="px-4 py-2 whitespace-nowrap"><?= esc(thai_date($d->receive_date, true)) ?></td>
                    <td class="px-4 py-2"><?= esc($d->department_name) ?></td>
                    <td class="px-2 py-2 text-right text-slate-300 group-hover:text-navy-500"><i class="fas fa-eye"></i></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_requisition)): ?><tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">ไม่มีข้อมูล</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(function () {
    var palette = ['#2b4a75','#059669','#f59e0b','#e11d48','#0891b2','#7c3aed','#ea580c'];
    var baseOpts = { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} };
    if (window.Chart) {
        new Chart(document.getElementById('chartReceived'), {
            type:'bar',
            data:{ labels: <?= json_encode($recvLabels) ?>, datasets:[{label:'จำนวน', data: <?= json_encode($recvValues) ?>, backgroundColor:palette, borderRadius:6}] },
            options: baseOpts
        });
        new Chart(document.getElementById('chartSent'), {
            type:'bar',
            data:{ labels: <?= json_encode($sentLabels) ?>, datasets:[{label:'จำนวน', data: <?= json_encode($sentValues) ?>, backgroundColor:palette, borderRadius:6}] },
            options: baseOpts
        });
    }
});
</script>
<?= $this->endSection() ?>
