<?php
ob_start();
session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
  <div class="card shadow" style="width:100%; max-width:380px;">
    <div class="card-body p-4">
      <div class="text-center mb-3">
        <img src="images/inasar_logo.png" style="max-height:60px;" alt="Logo">
        <h4 class="mt-2 mb-0">Iniciar Sesión</h4>
      </div>
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($_GET['error']); ?></div>
      <?php endif; ?>
      <form action="class/checkLoginEmpresa.php" method="post">
        <div class="mb-3">
          <label class="form-label">Usuario</label>
          <input type="text" class="form-control" name="user" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Clave</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Ingresar</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
