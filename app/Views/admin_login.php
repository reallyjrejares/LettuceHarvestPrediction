<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Harvest Admin Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('css/style.css?v=2') ?>">
</head>
<body class="auth-body">

<?php $successMessage = session()->getFlashdata('success'); ?>
<?php if ($successMessage): ?>
<div class="auth-success-banner">
<?= $successMessage ?>
</div>
<?php endif; ?>

<div class="auth-card">
<img src="<?= base_url('mylogo.png') ?>" alt="Smart Harvest Logo" class="auth-logo">

<h3 class="auth-title">Smart Harvest</h3>
<p class="auth-subtitle">Administrator</p>


<?php if (session()->getFlashdata('adminErrors')): ?>
<div class="alert alert-danger auth-alert">
<ul class="auth-error-list">
<?php foreach (session()->getFlashdata('adminErrors') as $error): ?>
<li><?= esc($error) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<form method="post" action="<?= site_url('admin/login') ?>">
<?= csrf_field() ?>
<div class="mb-3">
<label for="username" class="form-label auth-label">Username</label>
<input type="text" id="username" name="username" class="form-control auth-input" placeholder="Enter your username" value="<?= old('username') ?>">
</div>

<div class="mb-4">
<label for="password" class="form-label auth-label">Password</label>
<div class="auth-password-wrap">
<input type="password" id="password" name="password" class="form-control auth-input" placeholder="Enter your password">
<button type="button" class="auth-eye" id="togglePassword" aria-label="Show password">
<span class="material-icons" id="eyeIcon">visibility</span>
</button>
</div>
</div>

<button type="submit" class="btn auth-btn w-100">Sign In</button>
</form>

<a href="<?= site_url('/') ?>" class="btn btn-link w-100 mt-2">Back to User Login</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const togglePassword=document.getElementById("togglePassword");
const passwordInput=document.getElementById("password");
const eyeIcon=document.getElementById("eyeIcon");

togglePassword.addEventListener("click",function(){
const isPassword=passwordInput.type==="password";
passwordInput.type=isPassword?"text":"password";
eyeIcon.textContent=isPassword?"visibility_off":"visibility";
});

const banner=document.querySelector('.auth-success-banner');
if(banner){
setTimeout(()=>{
banner.classList.add('auth-success-hide');
setTimeout(()=>banner.remove(),300);
},3000);
}

</script>

</body>
</html>
