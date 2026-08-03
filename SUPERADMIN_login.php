<?php
ob_start();
session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso SUPERADMIN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center" style="min-height:100vh;">
  <div class="card shadow" style="width:100%; max-width:380px;">
    <div class="card-body p-4">
      <h4 class="text-center mb-3">Acceso SUPERADMIN</h4>
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
        <div class="d-grid">
          <button type="submit" class="btn btn-danger">Ingresar</button>
        </div>
      </form>
      <div class="text-center mt-3">
        <a href="index.php" class="small text-muted">Volver al login normal</a>
      </div>
    </div>
  </div>
</body>
</html>
