<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Harvest Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="css/style.css?v=2">
</head>
<body class="auth-body">

<?php $successMessage = session()->getFlashdata('success'); ?>
<?php if ($successMessage): ?>
<div class="auth-success-banner">
<?= $successMessage ?>
</div>
<?php endif; ?>

<div class="auth-card">
<img src="mylogo.png" alt="Smart Harvest Logo" class="auth-logo">

<h3 class="auth-title">Smart Harvest</h3>
<p class="auth-subtitle">Sign in to your dashboard</p>


<?php if (session()->getFlashdata('errors') && ! session()->getFlashdata('showRegister')): ?>
<div class="alert alert-danger auth-alert">
<ul class="auth-error-list">
<?php foreach (session()->getFlashdata('errors') as $error): ?>
<li><?= esc($error) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<form method="post" action="<?= site_url('login') ?>">
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

<div class="auth-or">or</div>
<button type="button" class="btn auth-btn-outline w-100" data-bs-toggle="modal" data-bs-target="#registerModal">Sign Up</button>
<a href="<?= site_url('admin/login') ?>" class="btn btn-link w-100 mt-2">Admin Login</a>
</div>

<!-- REGISTER MODAL -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content auth-modal">
<div class="modal-body p-4">
<div class="auth-modal-head">
<img src="mylogo.png" alt="Smart Harvest Logo" class="auth-logo modal-logo">
<h4 class="auth-title">Create Account</h4>
<p class="auth-subtitle">Start managing your harvests</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger auth-alert">
<ul class="auth-error-list">
<?php foreach (session()->getFlashdata('errors') as $error): ?>
<li><?= esc($error) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<form method="post" action="<?= site_url('register') ?>">
<?= csrf_field() ?>
<div class="mb-3">
<label for="regName" class="form-label auth-label">Full Name</label>
<input type="text" id="regName" name="name" class="form-control auth-input" placeholder="Enter your full name" value="<?= old('name') ?>">
</div>

<div class="mb-3">
<label for="regEmail" class="form-label auth-label">Email</label>
<input type="email" id="regEmail" name="email" class="form-control auth-input" placeholder="Enter your email" value="<?= old('email') ?>">
</div>

<div class="mb-3">
<label for="regUsername" class="form-label auth-label">Username</label>
<input type="text" id="regUsername" name="username" class="form-control auth-input" placeholder="Choose a username" value="<?= old('username') ?>">
</div>

<div class="mb-4">
<label for="regPassword" class="form-label auth-label">Password</label>
<div class="auth-password-wrap">
<input type="password" id="regPassword" name="password" class="form-control auth-input" placeholder="Create a password">
<button type="button" class="auth-eye" data-toggle-target="regPassword" aria-label="Show password">
<span class="material-icons">visibility</span>
</button>
</div>
</div>

<div class="mb-4">
<label for="regConfirmPassword" class="form-label auth-label">Confirm Password</label>
<div class="auth-password-wrap">
<input type="password" id="regConfirmPassword" name="confirm_password" class="form-control auth-input" placeholder="Confirm your password">
<button type="button" class="auth-eye" data-toggle-target="regConfirmPassword" aria-label="Show password">
<span class="material-icons">visibility</span>
</button>
</div>
</div>

<button type="submit" class="btn auth-btn w-100">Create Account</button>
</form>
</div>
</div>
</div>
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

document.querySelectorAll("[data-toggle-target]").forEach(btn=>{
btn.addEventListener("click",function(){
const targetId=btn.getAttribute("data-toggle-target");
const input=document.getElementById(targetId);
const icon=btn.querySelector(".material-icons");
const isPassword=input.type==="password";
input.type=isPassword?"text":"password";
if(icon){
icon.textContent=isPassword?"visibility_off":"visibility";
}
});
});

const showRegister=<?= session()->getFlashdata('showRegister') ? 'true' : 'false' ?>;
if(showRegister){
const modalEl=document.getElementById('registerModal');
const modal=new bootstrap.Modal(modalEl);
modal.show();
}

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
