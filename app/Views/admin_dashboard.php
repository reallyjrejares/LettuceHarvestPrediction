<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$totalUsers = (int) ($totalUsers ?? 0);
$adminCount = (int) ($adminCount ?? 0);
$regularCount = (int) ($regularCount ?? 0);
$totalPlants = (int) ($totalPlants ?? 0);
$usersWithPlants = (int) ($usersWithPlants ?? 0);
$averagePlantsPerUser = (float) ($averagePlantsPerUser ?? 0);
$upcomingHarvests = (int) ($upcomingHarvests ?? 0);
$overdueHarvests = (int) ($overdueHarvests ?? 0);
$topGrower = $topGrower ?? null;
$recentUsers = $recentUsers ?? [];
$recentPlants = $recentPlants ?? [];
?>

<h3 class="mb-4">Admin Dashboard</h3>

<div class="row mb-4">
<div class="col-md-3 col-sm-6 mb-3">
<div class="card card-box p-3 h-100">
<h6>Total Users</h6>
<h3><?= esc((string) $totalUsers) ?></h3>
<small class="text-muted"><?= esc((string) $regularCount) ?> active users</small>
</div>
</div>
<div class="col-md-3 col-sm-6 mb-3">
<div class="card card-box p-3 h-100">
<h6>Total Plants</h6>
<h3><?= esc((string) $totalPlants) ?></h3>
<small class="text-muted"><?= esc((string) $usersWithPlants) ?> growers</small>
</div>
</div>
<div class="col-md-3 col-sm-6 mb-3">
<div class="card card-box p-3 h-100">
<h6>Upcoming Harvests</h6>
<h3 class="text-success"><?= esc((string) $upcomingHarvests) ?></h3>
<small class="text-muted">Due within 7 days</small>
</div>
</div>
<div class="col-md-3 col-sm-6 mb-3">
<div class="card card-box p-3 h-100">
<h6>Overdue Harvests</h6>
<h3 class="text-danger"><?= esc((string) $overdueHarvests) ?></h3>
<small class="text-muted">Passed predicted date</small>
</div>
</div>
</div>

<div class="row mb-4">
<div class="col-lg-6 mb-3">
<div class="table-container">
<h5 class="mb-3">User Activity</h5>
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-success">
<tr>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Joined</th>
</tr>
</thead>
<tbody>
<?php foreach ($recentUsers as $user): ?>
<tr>
<td><?= esc((string) ($user['name'] ?? '')) ?></td>
<td><?= esc((string) ($user['username'] ?? '')) ?></td>
<td><?= esc((string) ($user['email'] ?? '')) ?></td>
<td><?= esc((string) ($user['created_at'] ?? '')) ?></td>
</tr>
<?php endforeach; ?>
<?php if ($recentUsers === []): ?>
<tr><td colspan="4" class="text-muted">No recent user activity.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<div class="col-lg-6 mb-3">
<div class="table-container">
<h5 class="mb-3">User Plant Count</h5>
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-success">
<tr>
<th>User</th>
<th>Username</th>
<th>Total Plants</th>
</tr>
</thead>
<tbody>
<?php 
$hasPlantData = false;
foreach ($recentUsers as $user): 
    $userId = (int) ($user['id'] ?? 0);
    // Count plants for this user
    $plantCount = 0;
    foreach ($recentPlants as $plant) {
        if ((int) ($plant['user_id'] ?? 0) === $userId) {
            $plantCount++;
        }
    }
    if ($plantCount > 0) {
        $hasPlantData = true;
    }
?>
<tr>
<td><?= esc((string) ($user['name'] ?? '')) ?></td>
<td><?= esc((string) ($user['username'] ?? '')) ?></td>
<td><span class="badge bg-success"><?= $plantCount ?></span></td>
</tr>
<?php endforeach; ?>
<?php if (!$hasPlantData && $recentUsers !== []): ?>
<tr><td colspan="3" class="text-muted">No plant data yet.</td></tr>
<?php endif; ?>
<?php if ($recentUsers === []): ?>
<tr><td colspan="3" class="text-muted">No users yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>

<div class="row mb-4">
<div class="col-12 mb-3">
<div class="table-container">
<h5 class="mb-3">Overall Activity Log</h5>
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-success">
<tr>
<th>ID</th>
<th>Plant Variety</th>
<th>User</th>
<th>Planted Date</th>
<th>Predicted Harvest</th>
</tr>
</thead>
<tbody>
<?php foreach ($recentPlants as $plant): ?>
<tr>
<td><?= esc((string) ($plant['id'] ?? '')) ?></td>
<td><?= esc((string) ($plant['variety'] ?? 'Unknown')) ?></td>
<td><?= esc((string) ($plant['username'] ?? 'N/A')) ?></td>
<td><?= esc((string) ($plant['date_planted'] ?? '')) ?></td>
<td><?= esc((string) ($plant['predicted_harvest'] ?? '')) ?></td>
</tr>
<?php endforeach; ?>
<?php if ($recentPlants === []): ?>
<tr><td colspan="5" class="text-muted">No plant records yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>

<?= $this->endSection() ?>
