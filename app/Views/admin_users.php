<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$users = $users ?? [];
$plantCounts = $plantCounts ?? [];
$totalUsers = count($users);
?>

<div class="mb-4">
<h2 style="color:#1b5e20; font-weight:700; margin-bottom:8px;">Users Management</h2>
<p class="text-muted">Manage and monitor all registered users</p>
</div>

<div class="row mb-4">
<div class="col-md-4 col-sm-6 mb-3">
<div class="card card-box p-4 h-100">
<div style="display:flex; align-items:center; gap:12px;">
<span class="material-icons" style="color:#2e7d32; font-size:32px;">people</span>
<div>
<h6 style="color:#666; margin:0; font-size:13px; font-weight:600;">Total Users</h6>
<h3 style="color:#1b5e20; margin:4px 0 0 0;"><?= esc((string) $totalUsers) ?></h3>
<small class="text-muted">Active users</small>
</div>
</div>
</div>
</div>
</div>

<div class="row mb-4">
<div class="col-12">
<div class="table-container" style="border-radius:12px; overflow:hidden;">
<div style="padding:20px; border-bottom:2px solid #e3ece6;">
<h5 style="margin:0; color:#1b5e20; font-weight:700;">Registered Users</h5>
</div>
<div class="table-responsive">
<table class="table table-hover mb-0" style="margin:0;">
<thead class="table-success">
<tr>
<th style="padding:14px; font-weight:700;">Name</th>
<th style="padding:14px; font-weight:700;">Username</th>
<th style="padding:14px; font-weight:700;">Email</th>
<th style="padding:14px; font-weight:700;">Total Plants</th>
<th style="padding:14px; font-weight:700;">Joined</th>
<th style="padding:14px; font-weight:700;">Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($users as $user): ?>
<?php 
$userId = (int) ($user['id'] ?? 0);
$plantCount = $plantCounts[$userId] ?? 0;
$userName = esc((string) ($user['name'] ?? ''));
$userEmail = esc((string) ($user['email'] ?? ''));
$joinedDate = esc((string) ($user['created_at'] ?? ''));
?>
<tr style="border-bottom:1px solid #eee;">
<td style="padding:14px;">
<strong style="color:#1b5e20;"><?= $userName ?></strong>
</td>
<td style="padding:14px;">
<span style="color:#666;">@<?= esc((string) ($user['username'] ?? '')) ?></span>
</td>
<td style="padding:14px;">
<small style="color:#888;"><?= $userEmail ?></small>
</td>
<td style="padding:14px;">
<span class="badge bg-success" style="padding:6px 12px; font-weight:600;"><?= $plantCount ?></span>
</td>
<td style="padding:14px;">
<small style="color:#888;"><?= $joinedDate ?></small>
</td>
<td style="padding:14px;">
<form method="post" action="<?= site_url('admin/users/' . $userId . '/delete') ?>" style="display:inline;" onsubmit="return confirm('Are you sure? This will delete the user and all their plants.');">
<?= csrf_field() ?>
<button type="submit" class="btn btn-danger btn-sm" style="padding:6px 12px; font-weight:600;">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if ($users === []): ?>
<tr>
<td colspan="6" style="padding:40px; text-align:center; color:#999;">
<span class="material-icons" style="display:block; margin-bottom:8px; color:#ddd; font-size:48px;">people_outline</span>
No users registered yet
</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>

<?= $this->endSection() ?>
