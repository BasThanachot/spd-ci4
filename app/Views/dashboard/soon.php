<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 mx-auto rounded-2xl bg-navy-50 text-navy-600 flex items-center justify-center mb-5">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
        </div>
        <h2 class="text-xl font-semibold text-slate-800 mb-2">อยู่ระหว่างพัฒนา</h2>
        <p class="text-slate-500 text-sm mb-6">
            เมนูนี้<?= $module ? ' (<b>' . esc($module) . '</b>)' : '' ?>
            จะเปิดใช้งานในสเตจถัดไปของการย้ายระบบมาเป็น CI4
        </p>
        <a href="<?= site_url('dashboard') ?>"
           class="inline-flex items-center gap-2 bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            กลับหน้าแดชบอร์ด
        </a>
    </div>
</div>

<?= $this->endSection() ?>
