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

<!-- แถวบน: เลือกปี -->
<div class="flex items-center justify-end mb-4">
    <form method="get" class="flex items-center">
        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-white px-2.5 py-2 text-slate-500"><i class="fas fa-calendar-alt"></i></span>
        <select name="year" onchange="this.form.submit()" class="rounded-r-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 bg-white">
            <?php foreach ($available_years as $y): ?><option value="<?= $y ?>" <?= $y===$selected_year?'selected':'' ?>><?= $y ?></option><?php endforeach; ?>
        </select>
    </form>
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
<h5 class="text-slate-500 text-sm font-medium mb-2">สรุปประจำปี <?= esc($selected_year) ?></h5>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <?php stat_box($received_year, 'รับหนังสือทั้งหมดปี '.$selected_year, '', 'fa-inbox', 'from-emerald-500', 'to-emerald-600', site_url('documents/received')); ?>
    <?php stat_box($sent_year, 'ส่งหนังสือทั้งหมดปี '.$selected_year, '', 'fa-paper-plane', 'from-rose-500', 'to-rose-600', site_url('documents/sent')); ?>
    <?php stat_box($requisition_year, 'ใบเบิกพัสดุทั้งหมดปี '.$selected_year, '', 'fa-boxes', 'from-cyan-500', 'to-cyan-600', site_url('documents/requisition')); ?>
</div>

<!-- กราฟแยกประเภท -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">รับหนังสือแยกตามประเภท (ปี <?= esc($selected_year) ?>)</h3></div>
        <div class="p-5"><canvas id="chartReceived" height="200"></canvas></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">ส่งหนังสือแยกตามประเภท (ปี <?= esc($selected_year) ?>)</h3></div>
        <div class="p-5"><canvas id="chartSent" height="200"></canvas></div>
    </div>
</div>

<!-- รายการล่าสุด -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- รับล่าสุด -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">รับหนังสือล่าสุด</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2 font-medium">เลขรับ</th><th class="px-4 py-2 font-medium">วันที่</th><th class="px-4 py-2 font-medium">ชื่อหนังสือ</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($recent_received as $d): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2"><a href="<?= site_url('documents/received/view/'.$d->id) ?>" class="text-navy-600 font-medium hover:underline"><?= esc($d->received_no) ?></a></td>
                    <td class="px-4 py-2 whitespace-nowrap"><?= esc(thai_date($d->received_date, true)) ?></td>
                    <td class="px-4 py-2 max-w-[160px] truncate" title="<?= esc($d->subject) ?>"><?= esc($d->subject) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_received)): ?><tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">ไม่มีข้อมูล</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- ส่งล่าสุด -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">ส่งหนังสือล่าสุด</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2 font-medium">เลขส่ง</th><th class="px-4 py-2 font-medium">วันที่</th><th class="px-4 py-2 font-medium">ชื่อหนังสือ</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($recent_sent as $d): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2"><a href="<?= site_url('documents/sent/view/'.$d->id) ?>" class="text-navy-600 font-medium hover:underline"><?= esc($d->sent_no) ?></a></td>
                    <td class="px-4 py-2 whitespace-nowrap"><?= esc(thai_date($d->sent_date, true)) ?></td>
                    <td class="px-4 py-2 max-w-[160px] truncate" title="<?= esc($d->subject) ?>"><?= esc($d->subject) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_sent)): ?><tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">ไม่มีข้อมูล</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- ใบเบิกล่าสุด -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100"><h3 class="font-medium text-slate-700">ใบเบิกพัสดุล่าสุด (ปี <?= esc($selected_year) ?>)</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2 font-medium">เลขที่ใบเบิก</th><th class="px-4 py-2 font-medium">วันที่รับ</th><th class="px-4 py-2 font-medium">คลัง</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($recent_requisition as $d): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2"><a href="<?= site_url('documents/requisition/view/'.$d->id) ?>" class="text-navy-600 font-medium hover:underline"><?= esc($d->req_no) ?></a></td>
                    <td class="px-4 py-2 whitespace-nowrap"><?= esc(thai_date($d->receive_date, true)) ?></td>
                    <td class="px-4 py-2"><?= esc($d->department_name) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_requisition)): ?><tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">ไม่มีข้อมูล</td></tr><?php endif; ?>
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
