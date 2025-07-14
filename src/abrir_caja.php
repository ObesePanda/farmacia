<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require("../conexion.php");

$id_user = $_SESSION['idUser'];

// Si ya tiene caja abierta, lo mandamos a ventas
$checkCaja = mysqli_query($conexion, "SELECT * FROM caja WHERE usuario_id = $id_user AND estado = 'abierta'");
if (mysqli_num_rows($checkCaja) > 0) {
    header("Location: ventas.php");
    exit;
}

// Procesar apertura
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto_inicial = floatval($_POST['monto_inicial']);
    $fecha = date("Y-m-d H:i:s");

    $query = "INSERT INTO caja (usuario_id, fecha_apertura, monto_inicial, estado) VALUES ($id_user, '$fecha', $monto_inicial, 'abierta')";
    $insert = mysqli_query($conexion, $query);

    if ($insert) {
        header("Location: ventas.php");
        exit;
    } else {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Error al abrir caja. Intenta nuevamente.'];      
    }
}

include_once "includes/header.php";
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h4 style="color: #fff; font-weight: 700;">Abrir Caja</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error)) { ?>
                <?php $_SESSION['mensaje'] ?>
            <?php } ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="monto_inicial">Monto inicial en caja:</label>
                    <input type="number" step="0.01" name="monto_inicial" id="monto_inicial" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-success mt-3">Abrir Caja</button>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
