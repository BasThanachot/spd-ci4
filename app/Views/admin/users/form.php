<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit=!empty($user);
  $v=fn($f,$d='')=>esc(old($f,$isEdit?($user->$f??''):$d));
  $roles=['admin'=>'ผู้ดูแลระบบ','staff'=>'เจ้าหน้าที่ธุรการ','warehouse'=>'เจ้าหน้าที่คลัง','user'=>'ผู้ใช้งานทั่วไป'];
  $curRole=old('role',$isEdit?$user->role:'user');
  $curPer=old('personnel_id',$isEdit?$user->personnel_id:''); ?>
<div class="bg-white rounded-xl border-t-4 border-navy-600 border border-slate-200 p-5 sm:p-6 max-w-3xl">
    <?php if (!empty($duplicate)): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5">ชื่อผู้ใช้งานนี้มีอยู่แล้ว กรุณาใช้ชื่ออื่น</div><?php endif; ?>
    <?php if (session('errors')): ?><div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"><ul class="list-disc list-inside"><?php foreach((array)session('errors') as $e):?><li><?= esc($e) ?></li><?php endforeach;?></ul></div><?php endif; ?>
    <form method="post" action="<?= $isEdit?site_url('admin/users/edit/'.$user->id):site_url('admin/users/create') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-600 mb-1">ชื่อผู้ใช้งาน <span class="text-red-500">*</span></label>
                <input type="text" name="username" value="<?= $v('username') ?>" maxlength="50" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">รหัสผ่าน <?php if(!$isEdit):?><span class="text-red-500">*</span><?php else:?><span class="text-slate-400 text-xs">(เว้นว่าง = คงรหัสเดิม)</span><?php endif;?></label>
                <input type="password" name="password" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-600 mb-1">ชื่อ-สกุล <span class="text-red-500">*</span></label>
            <input type="text" name="full_name" value="<?= $v('full_name') ?>" maxlength="150" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-600 mb-1">สิทธิ์การใช้งาน <span class="text-red-500">*</span></label>
                <select name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20">
                    <?php foreach ($roles as $k=>$lbl): ?><option value="<?= $k ?>" <?= (string)$curRole===$k?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
                </select></div>
            <div><label class="block text-sm font-medium text-slate-600 mb-1">ผูกกับกำลังพล</label>
                <select name="personnel_id" class="select2 w-full"><option value="">- ไม่ระบุ -</option>
                    <?php foreach ($personnel as $p): ?><option value="<?= $p->id ?>" <?= (string)$curPer===(string)$p->id?'selected':'' ?>><?= esc($p->display_name) ?></option><?php endforeach; ?>
                </select></div>
        </div>
        <?php if ($isEdit): ?>
        <div><label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" <?= (old('is_active', $user->is_active)?'checked':'') ?> class="rounded border-slate-300 text-navy-600"> เปิดใช้งานบัญชี
        </label></div>
        <?php endif; ?>
        <div class="pt-2"><button class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-medium rounded-lg px-5 py-2">บันทึก</button>
            <a href="<?= site_url('admin/users') ?>" class="ml-1 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">ยกเลิก</a></div>
    </form>
</div>
<?= $this->endSection() ?>
