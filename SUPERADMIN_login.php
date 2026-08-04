<?php
ob_start();
session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kontrol - Acceso SUPERADMIN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #eef1f6; }
    .login-wrap { display:flex; min-height:100vh; align-items:center; justify-content:center; padding:20px; }
    .login-card {
        display:flex; width:100%; max-width:820px; border-radius:16px; overflow:hidden;
        box-shadow: 0 20px 50px rgba(15,52,96,0.15); background:#fff;
    }
    .login-form-side { flex:1; padding:48px 44px; }
    .login-brand-side {
        flex:0.85; background:linear-gradient(160deg,#7a1f2b 0%,#a3283a 100%);
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        color:#fff; padding:40px; text-align:center;
    }
    .brand-icon {
        width:84px; height:84px; border-radius:20px; background:rgba(255,255,255,0.12);
        display:flex; align-items:center; justify-content:center; margin-bottom:18px;
        font-size:2.4rem; border:1px solid rgba(255,255,255,0.25);
    }
    .brand-name { font-weight:700; font-size:1.6rem; letter-spacing:1px; }
    .brand-tagline { font-size:0.8rem; color:#f0c9ce; margin-top:4px; letter-spacing:2px; }
    .brand-sub { margin-top:22px; font-size:0.9rem; color:#f6dde0; line-height:1.6; }
    .form-label { font-weight:500; color:#333; font-size:0.9rem; }
    .form-control { border-radius:8px; padding:10px 14px; }
    .btn-login { background:#7a1f2b; border:none; border-radius:8px; padding:11px; font-weight:600; }
    .btn-login:hover { background:#611824; }
    @media (max-width: 700px) {
        .login-card { flex-direction:column; }
        .login-brand-side { padding:28px; }
    }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-form-side">
        <h4 class="mb-1">Acceso SUPERADMIN</h4>
        <p class="text-muted mb-4" style="font-size:0.9rem;">Panel de administración global</p>
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form action="class/checkLoginSuperAdmin.php" method="post">
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" class="form-control" name="user" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Clave</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-login text-white">Ingresar</button>
          </div>
        </form>
        <div class="text-center mt-3">
          <a href="index.php" class="small text-muted">Volver al login normal</a>
        </div>
      </div>
      <div class="login-brand-side">
        <div class="brand-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="brand-name">KONTROL</div>
        <div class="brand-tagline">SUPERADMIN</div>
        <div class="brand-sub">Panel de administración<br>multi-empresa</div>
      </div>
    </div>
  </div>
</body>
</html>
