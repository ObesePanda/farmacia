<?php
date_default_timezone_set('America/Mexico_City');
if (empty($_SESSION['active'])) {
    header('Location: ../');
}
$id_user = $_SESSION['idUser'];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Panel de Administración</title>




    <!--    NUEVO   -->
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />
    <link id="pagestyle" href="../assets/css/style.css" rel="stylesheet" />    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>



<body class="g-sidenav-show   bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    <aside
        class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href=" index.php "
              >
                <img src="http://localhost/farmacia/assets/img/logos/mlogo.png" class="navbar-brand-img h-100"
                    alt="main_logo">

            </a>
        </div>
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-house-door-fill text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="usuarios.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-people-fill text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Usuarios</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="config.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-gear-fill text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Configuración</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">MENU DE PRODUCTOS</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="tipo.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-prescription2 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Tipo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="presentacion.php">                       
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-capsule text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Presentación</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="laboratorio.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-world-2 text-danger text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Proveedor</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="productos.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-lungs-fill text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Productos</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">MENU DE VENTAS</h6>

                </li>
                <li class="nav-item">
                    <a class="nav-link " href="clientes.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-bounding-box text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Clientes</span>
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link " href="compras.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-basket2-fill text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Nueva Compra</span>
                    </a>
                </li>
               <li class="nav-item">
                    <a class="nav-link" href="ventas.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-cart-plus-fill text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">
                            Nueva Venta
                           
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="historial_caja.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-cash-coin text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Historial Caja</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="lista_ventas.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-journals text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Historial Ventas</span>
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link " href="lista_compras.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-journals text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Historial Compras</span>
                    </a>
                </li>
                 <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">MENU DE CONSULTAS</h6>
                </li>
                 <li class="nav-item">
                    <a class="nav-link " href="registrar_consulta.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-earmark-medical-fill text-primary text-sm opacity-10"></i>                            
                        </div>
                        <span class="nav-link-text ms-1">Consulta</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="pacientes.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-hearts text-secondary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Pacientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="medicos.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-lungs-fill text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Medicos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="lista_consultas.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-hospital-fill text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Historial Consultas</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidenav-footer mx-3 ">
            <div class="card card-plain shadow-none" id="sidenavCard">
                <div class="card-body text-center w-100 pt-0 mt-8">
                    <div class="docs-info">
                    <h6 class="panda_pos"> Desarrollo</h6>
                        <a class="panda_pos" target="_blank">PandaCode - Sistema Farmacia V1.0</a>

                    </div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content position-relative border-radius-lg ps">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
            data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm text-white"><a class="opacity-5 text-white"
                                href="javascript:;">Sistema </a></li>
                    </ol>
                    <h6 class="font-weight-bolder text-white mb-0">Tu Nombre</h6>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        <div class="input-group">

                        </div>
                    </div>
                    <ul class="navbar-nav  justify-content-end">
                        <li class="nav-item d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-white font-weight-bold px-0">
                                <i class="bi bi-sliders2 me-sm-1" aria-hidden="true"></i>
                                <span class="d-sm-inline d-none">Perfil</span>
                            </a>
                        </li>

                        <li class="nav-item px-3 d-flex align-items-center">
                            <a href="salir.php" class="nav-link text-white p-0">
                                <i class="bi bi-box-arrow-right fixed-plugin-button-nav cursor-pointer"
                                    aria-hidden="true"></i>

                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid py-4">




            <!-- End Navbar -->