<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Smart Harvest') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('css/dashboard.css?v=7') ?>">
<?= $this->renderSection('styles') ?>
</head>
<body>

<?php
$session = session();
$isUser = $session->get('is_logged_in') === true && (bool) $session->get('user_id');
$isAdmin = $session->get('admin_logged_in') === true && (bool) $session->get('admin_id');
$displayName = 'User';
$displayEmail = null;
$displayUsername = null;
if ($isAdmin) {
    $displayName = $session->get('admin_username') ?: 'Administrator';
    $displayUsername = $session->get('admin_username') ?: 'administrator';
} elseif ($isUser) {
    $displayName = $session->get('name') ?: $session->get('username') ?: 'User';
    $displayEmail = $session->get('email') ?: null;
    $displayUsername = $session->get('username') ?: null;
}
?>

<button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation" aria-controls="sidebarNav" aria-expanded="false">
<span class="material-icons">menu</span>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="mobile-topbar">
<div class="mobile-topbar-brand">
<img src="<?= base_url('mylogo.png') ?>" alt="Smart Harvest Logo">
<span class="mobile-topbar-title">Smart Harvest</span>
</div>
<div class="mobile-topbar-page"><?= esc($title ?? 'Dashboard') ?></div>
</div>

<div class="sidebar" id="sidebarNav">
<h4>
<img src="<?= base_url('mylogo.png') ?>">
Smart Harvest
</h4>
<?php if ($isUser): ?>
<a href="<?= site_url('dashboard') ?>" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
<a href="<?= site_url('lettuce-records') ?>" class="<?= ($activePage ?? '') === 'records' ? 'active' : '' ?>">Lettuce Records</a>
<a href="<?= site_url('predictions') ?>" class="<?= ($activePage ?? '') === 'predictions' ? 'active' : '' ?>">Predictions</a>
<a href="<?= site_url('analytics') ?>" class="<?= ($activePage ?? '') === 'analytics' ? 'active' : '' ?>">Analytics</a>
<?php endif; ?>
<?php if ($isAdmin): ?>
<a href="<?= site_url('admin') ?>" class="<?= ($activePage ?? '') === 'admin-dashboard' ? 'active' : '' ?>">Admin Dashboard</a>
<a href="<?= site_url('admin/users') ?>" class="<?= ($activePage ?? '') === 'admin-users' ? 'active' : '' ?>">Users</a>
<?php endif; ?>

<div class="sidebar-footer">
<div class="sidebar-account">
<div class="sidebar-account-top">
<span class="material-icons sidebar-account-icon">person</span>
<div>
<div class="sidebar-account-name"><?= esc($displayName) ?></div>
<?php if ($displayEmail): ?>
<div class="sidebar-account-sub"><?= esc($displayEmail) ?></div>
<?php elseif ($displayUsername): ?>
<div class="sidebar-account-sub"><?= esc($displayUsername) ?></div>
<?php endif; ?>
<?php if ($isAdmin): ?>
<div class="sidebar-account-sub">Administrator</div>
<?php endif; ?>
</div>
</div>
<a href="<?= site_url('logout') ?>" class="btn btn-danger btn-sm w-100">Log out</a>
</div>
</div>
</div>

<div class="main">
<?= $this->renderSection('content') ?>
</div>

<?= $this->renderSection('scripts') ?>
<script>
(()=>{
const menuBtn=document.getElementById("mobileMenuBtn");
const sidebar=document.getElementById("sidebarNav");
const overlay=document.getElementById("sidebarOverlay");

if(!menuBtn||!sidebar||!overlay){
return;
}

const openMenu=()=>{
sidebar.classList.add("is-open");
overlay.classList.add("is-open");
menuBtn.setAttribute("aria-expanded","true");
document.body.classList.add("no-scroll");
};

const closeMenu=()=>{
sidebar.classList.remove("is-open");
overlay.classList.remove("is-open");
menuBtn.setAttribute("aria-expanded","false");
document.body.classList.remove("no-scroll");
};

menuBtn.addEventListener("click",()=>{
if(sidebar.classList.contains("is-open")){
closeMenu();
return;
}
openMenu();
});

overlay.addEventListener("click",closeMenu);
sidebar.querySelectorAll("a").forEach(link=>{
link.addEventListener("click",closeMenu);
});

window.addEventListener("resize",()=>{
if(window.innerWidth>=992){
closeMenu();
}
});
})();
</script>
</body>
</html>
