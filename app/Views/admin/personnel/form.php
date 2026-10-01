<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit=!empty($person);
  $v=fn($f,$d='')=>esc(old($f,$isEdit?($person->$f??''):$d));
  $sel=fn($f,$optId)=> (string)old($f,$isEdit?($person->$f??''):'')===(string)$optId?'selected':''; ?>
<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 p-5 sm:p-6 max-w-3xl">
    <?php if (session('errors')): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><ul class="list-disc list-inside"><?php foreach((array)session('errors') as $e):?><li><?= esc($e) ?></li><?php endforeach;?></ul></div><?php endif; ?>
    <form method="post" action="<?= $isEdit?site_url('admin/personnel/edit/'.$person->id):site_url('admin/personnel/create') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-600 mb-1">ยศ</label>
                <select name="rank_id" class="select2 w-full"><option value="">- ไม่ระบุ -</option>
                    <?php foreach ($ranks as $r): ?><option value="<?= $r->id ?>" <?= $sel('rank_id',$r->id) ?>><?= esc($r->rank_name) ?></option><?php endforeach; ?>
                </select></div>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">แผนก</label>
                <select name="department_id" class="select2 w-full"><option value="">- ไม่ระบุ -</option>
                    <?php foreach ($departments as $dp): ?><option value="<?= $dp->id ?>" <?= $sel('department_id',$dp->id) ?>><?= esc($dp->department_name) ?></option><?php endforeach; ?>
                </select></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-600 mb-1">ชื่อ <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" value="<?= $v('first_name') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">นามสกุล <span class="text-red-500">*</span></label>
                <input type="text" name="last_name" value="<?= $v('last_name') ?>" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">ตำแหน่ง</label>
            <input type="text" name="position" value="<?= $v('position') ?>" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        <div class="pt-2"><button class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2">บันทึก</button>
            <a href="<?= site_url('admin/personnel') ?>" class="ml-1 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">ยกเลิก</a></div>
    </form>
</div>
<?= $this->endSection() ?>
