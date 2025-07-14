<?php
session_start();
if (!empty($_SESSION['active'])) {
    header('location: src/');
} else {
    if (!empty($_POST)) {
        $alert = '';
        if (empty($_POST['usuario']) || empty($_POST['clave'])) {
            $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Alerta', 'texto' => 'Ingrese usuario y contraseña'];
        } else {
            require_once "conexion.php";
            $user = mysqli_real_escape_string($conexion, $_POST['usuario']);
            $clave = md5(mysqli_real_escape_string($conexion, $_POST['clave']));
            $query = mysqli_query($conexion, "SELECT * FROM usuario WHERE usuario = '$user' AND clave = '$clave'");
            mysqli_close($conexion);
            $resultado = mysqli_num_rows($query);
            if ($resultado > 0) {
                $dato = mysqli_fetch_array($query);
                $_SESSION['active'] = true;
                $_SESSION['idUser'] = $dato['idusuario'];
                $_SESSION['nombre'] = $dato['nombre'];
                $_SESSION['user'] = $dato['usuario'];
                header('Location: src/');
            } else {
                $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Alerta', 'texto' => 'Contraseña incorrecta'];
                session_destroy();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Iniciar Sesión</title>
    <!-- plugins:css -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/img/favicon.ico" />
</head>

<body class="">
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                <!-- Navbar -->
                <nav
                    class="navbar navbar-expand-lg blur border-radius-lg top-0 z-index-3 shadow position-absolute mt-4 py-2 start-0 end-0 mx-4">
                    <div class="container-fluid">
                        <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 " href="">
                            <img class="logo-login" src="assets/img/logos/mlogo.png" alt="">
                        </a>
                        <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon mt-2">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </span>
                        </button>
                        <div class="collapse navbar-collapse" id="navigation">
                            <ul class="navbar-nav mx-auto">
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center me-2 active" aria-current="page"
                                        href="../pages/dashboard.html">
                                        <div class="version-sistema">
                                            <i class="fa-solid fa-stethoscope"></i> &nbsp; Sistema Farmacia 2024 - V 1.0
                                        </div>
                                    </a>
                                </li>

                            </ul>
                            <ul class="navbar-nav d-lg-block d-none">
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/product/argon-dashboard"
                                        class="btn btn-sm mb-0 me-1 btn-primary"><i
                                            class="fa-solid fa-handshake-angle fa-fade"></i> Soporte</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                <!-- End Navbar -->
            </div>
        </div>
    </div>
    <main class="main-content  mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <?php echo (isset($alert)) ? $alert : ''; ?>
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <h4 class="font-weight-bolder">Bienvenido!</h4>
                                    <p class="mb-0">Ingresa tu contraseña y usuario para aceeder:</p>
                                </div>
                                <div class="card-body">
                                    <?php echo isset($alert) ? $alert : ''; ?>
                                    <form action="" method="post" class="p-3">
                                        <div class="mb-3">
                                            <i class="fa-regular fa-user"></i> Usuario:
                                            <input type="text" class="form-control form-control-lg"
                                                id="exampleInputEmail1" placeholder="Ingresa tu usuario" name="usuario"
                                                aria-label="Email">
                                        </div>
                                        <div class="mb-3">
                                            <i class="fa-solid fa-lock"></i> Contraseña:
                                            <input type="password" class="form-control form-control-lg "
                                                id="exampleInputPassword1" placeholder="Ingresa tu contraseña"
                                                name="clave">
                                        </div>

                                        <div class="text-mt-3">
                                            <button class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0"
                                                type="submit">Iniciar Sesión</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('https://tecnyfarma.com/wp-content/uploads/2022/03/018_200122_30_0085-HDR-Editar-scaled-1-1.jpg');
          background-size: cover;">
                                <img src="assets/img/logos/mlogo2.png" alt="">
                                <span class="mask bg-gradient-primary opacity-6"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">"Tu bienestar comienza
                                    aquí."</h4>
                                <p class="text-white position-relative">Siempre a tu lado en momentos de necesidad.</p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="https://kit.fontawesome.com/254e7505b4.js" crossorigin="anonymous"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="assets/js/argon-dashboard.min.js?v=2.0.4"></script>
    <?php if (isset($_SESSION['mensaje'])): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $_SESSION['mensaje']['tipo']; ?>',
            title: '<?php echo $_SESSION['mensaje']['titulo']; ?>',
            text: '<?php echo $_SESSION['mensaje']['texto']; ?>',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>
</body>


</html>