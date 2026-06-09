<?php
ob_start();
    session_start();
    if(isset($_SESSION["loggedin"])){
		header("Location: Home.php");
	}
?>
<!doctype html>
<html lang="en" class="light-theme">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- loader-->
  <link href="assets/css/pace.min.css" rel="stylesheet" />
  <script src="assets/js/pace.min.js"></script>

  <!--plugins-->
  <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />

  <!-- CSS Files -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

  <title>InaControl - Ayuda Tecnologica</title>
</head>

<body class="bg-white">

  <!--start wrapper-->
  <div class="wrapper">
    <div class="">
      <div class="row g-0 m-0">
        <div class="col-xl-6 col-lg-12">
          <div class="login-cover-wrapper">
            <div class="card shadow-none">
              <div class="card-body">
                <div class="text-center">
                    <div class="fadeIn first">
              <img src="images/inasar_logo.png" id="icon" alt="User Icon" />
              <h1>Ina - Control</h1>
            </div>
                  <h4>Iniciar Sesión</h4>
                  <!--<p>Ingresa tus credenciales</p>-->
                </div>
                <form class="form-body row g-3" action="class/checkLogin.php" method="post">
                  <div class="col-12">
                    <label for="inputEmail" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="user" name="user">
                  </div>
                  <div class="col-12">
                    <label for="inputPassword" class="form-label">Clave</label>
                    <input type="password" class="form-control" id="password" name="password">
                  </div>
                  <div class="col-12 col-lg-6">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckRemember">
                      <label class="form-check-label" for="flexSwitchCheckRemember">Remember Me</label>
                    </div>
                  </div>
                  <div class="col-12 col-lg-6 text-end">
                    <a href="authentication-reset-password-cover.html">Forgot Password?</a>
                  </div>
                  <div class="col-12 col-lg-12">
                    <div class="d-grid">
                        <input type="submit" class="fadeIn fourth" value="Ingresar">
                      <!--<button type="button" class="btn btn-primary">Sign In</button>-->
                    </div>
                  </div>
                  <div class="col-12 col-lg-12">
                    <div class="position-relative border-bottom my-3">
                      <div class="position-absolute seperator translate-middle-y">or continue with</div>
                    </div>
                  </div>
                  <div class="col-12 col-lg-12">
                    <!--<div class="social-login d-flex flex-row align-items-center justify-content-center gap-2 my-2">-->
                    <!--  <a href="javascript:;" class=""><img src="assets/images/icons/facebook.png" alt=""></a>-->
                    <!--  <a href="javascript:;" class=""><img src="assets/images/icons/apple-black-logo.png" alt=""></a>-->
                    <!--  <a href="javascript:;" class=""><img src="assets/images/icons/google.png" alt=""></a>-->
                    <!--</div>-->
                  </div>
                  <div class="col-12 col-lg-12 text-center">
                    <!--<p class="mb-0">Don't have an account? <a href="authentication-sign-up-cover.html">Sign up</a></p>-->
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-6 col-lg-12">
          <!--<div class="position-fixed top-0 h-100 d-xl-block d-none login-cover-img">-->
          </div>
        </div>
      </div>
      <!--end row-->
    </div>
  </div>
  <!--end wrapper-->


</body>

</html>
<?php
ob_end_flush();
?>