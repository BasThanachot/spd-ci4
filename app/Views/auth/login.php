<?= "<!DOCTYPE html>\n" ?>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — <?= esc(site_setting('system_name', 'ระบบรับ-ส่งหนังสือ')) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/logo.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/sarabun.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="min-h-screen bg-gradient-to-br from-navy-800 to-navy-600 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-24 h-24 mx-auto rounded-2xl bg-white flex items-center justify-center mb-3 shadow-lg p-2">
                <img src="<?= base_url('assets/logo.png') ?>" alt="โลโก้ กรมอู่ทหารเรือ" class="w-full h-full object-contain">
            </div>
            <h1 class="text-white text-xl font-semibold"><?= esc(site_setting('system_name', 'ระบบรับ-ส่งหนังสือ')) ?></h1>
            <p class="text-navy-200 text-sm"><?= esc(site_setting('agency_name', 'กรมอู่ทหารเรือ')) ?><?php if ($p = site_setting('phone', '')): ?> · โทร. <?= esc($p) ?><?php endif; ?></p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-7">
            <h2 class="text-lg font-semibold text-slate-800 mb-1">เข้าสู่ระบบ</h2>
            <p class="text-sm text-slate-400 mb-5">กรอกชื่อผู้ใช้งานและรหัสผ่าน</p>

            <?php if ($msg = session()->getFlashdata('error')): ?>
                <div class="mb-4 flex items-start gap-2 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2.5">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L14.7 3.9a2 2 0 00-3.4 0z"/></svg>
                    <span><?= esc($msg) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($msg = session()->getFlashdata('success')): ?>
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-3 py-2.5">
                    <?= esc($msg) ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('auth/login') ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1.5">ชื่อผู้ใช้งาน</label>
                    <input type="text" name="username" value="<?= esc(old('username')) ?>" autofocus required
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20 outline-none"
                           placeholder="เช่น admin">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1.5">รหัสผ่าน</label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20 outline-none"
                           placeholder="••••••••">
                </div>
                <button type="submit"
                        class="w-full bg-navy-600 hover:bg-navy-700 text-white font-medium rounded-lg py-2.5 text-sm transition shadow-sm">
                    เข้าสู่ระบบ
                </button>
            </form>
        </div>

        <p class="text-center text-navy-300 text-xs mt-5">© <?= date('Y') + 543 ?> กรมอู่ทหารเรือ</p>
    </div>

</body>
</html>
