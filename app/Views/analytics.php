<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h3 class="mb-4">Analytics</h3>

<div class="row mb-4">
<div class="col-md-4">
<div class="card card-box p-3">
<h6>Total Batches</h6>
<h3><?= esc($totalBatches ?? 0) ?></h3>
</div>
</div>
<div class="col-md-4">
<div class="card card-box p-3">
<h6>Avg. Growth Days</h6>
<h3><?= esc($avgGrowthDays ?? 0) ?></h3>
</div>
</div>
<div class="col-md-4">
<div class="card card-box p-3">
<h6>Harvest Success Rate</h6>
<h3><?= esc($harvestSuccess ?? 0) ?>%</h3>
</div>
</div>
</div>

<div class="row mb-4">
<div class="col-md-6">
<div class="card card-box p-4">
<h5>Top Lettuce Varieties</h5>
<?php if (! empty($topVarieties)): ?>
<div class="list-group mt-3">
<?php foreach ($topVarieties as $variety => $count): ?>
<div class="list-group-item d-flex justify-content-between align-items-center">
<span><?= esc($variety) ?></span>
<span class="badge bg-primary rounded-pill"><?= esc($count) ?></span>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p class="text-muted mt-3">No variety data yet.</p>
<?php endif; ?>
</div>
</div>

<div class="col-md-6">
<div class="card card-box p-4">
<h5>Recent Plants</h5>
<?php if (! empty($plants)): ?>
<div class="table-responsive">
<table class="table table-sm table-borderless">
<thead>
<tr>
<th>Variety</th>
<th>Planted</th>
<th>Harvest Date</th>
</tr>
</thead>
<tbody>
<?php $displayedCount = 0; foreach ($plants as $plant):
if ($displayedCount >= 5) break; $displayedCount++;
$harvestDate = $plant['predicted_harvest'] ?? 'N/A';
?>
<tr>
<td><?= esc($plant['variety']) ?></td>
<td><?= esc($plant['date_planted']) ?></td>
<td><?= esc($harvestDate) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p class="text-muted mt-3">No plant records yet.</p>
<?php endif; ?>
</div>
</div>
</div>

<div class="ai-box">
<h5>Insights</h5>
<?php if ($totalBatches > 0): ?>
<ul class="mb-0">
<li>You've grown <strong><?= esc($totalBatches) ?></strong> batches of lettuce</li>
<li>Average growth cycle is <strong><?= esc($avgGrowthDays) ?> days</strong></li>
<li>Harvest success rate: <strong><?= esc($harvestSuccess) ?>%</strong></li>
<?php if (! empty($topVarieties)): ?>
<li>Most popular variety: <strong><?= esc(array_key_first($topVarieties)) ?></strong></li>
<?php endif; ?>
</ul>
<?php else: ?>
<p class="text-muted">Start planting lettuce to see your analytics and insights here.</p>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
