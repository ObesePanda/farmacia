<?php
session_start();
require("../conexion.php");
date_default_timezone_set('America/Mexico_City');
$id_user = $_SESSION['idUser'];

$error = $_SESSION['error'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;

// Elimina para que no persistan en otras vistas
unset($_SESSION['error'], $_SESSION['mensaje']);

// Buscar caja abierta
$sqlCaja = mysqli_query($conexion, "SELECT * FROM caja WHERE usuario_id = $id_user AND estado = 'abierta' ORDER BY id DESC LIMIT 1");
$caja = mysqli_fetch_assoc($sqlCaja);

if (!$caja) {
    header("Location: ventas.php");
    exit;
}

$fechaApertura = $caja['fecha_apertura'];
$idCaja = $caja['id'];

// Consultar total de ventas desde que se abrió la caja
$sqlVentas = mysqli_query($conexion, "SELECT SUM(total) AS total_ventas FROM ventas WHERE id_usuario = $id_user AND fecha > '$fechaApertura'");
$dataVentas = mysqli_fetch_assoc($sqlVentas);
$totalVentas = $dataVentas['total_ventas'] ?? 0;

$error = '';
$success = '';
$alerta = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montoReal = floatval($_POST['monto_real']);
    $fechaCierre = date("Y-m-d H:i:s");

    if ($montoReal < 0) {
        $_SESSION['error'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'El monto final no puede ser negativo'];
        header("Location: cerrar_caja.php");
        exit;
    }

    $esperado = $caja['monto_inicial'] + $totalVentas;
    $diferencia = $montoReal - $esperado;

    if ($diferencia < 0) {
        $alerta = "⚠️ Atención: Hay un <strong>faltante de $" . number_format(abs($diferencia), 2) . "</strong> respecto al total esperado.";
    } elseif ($diferencia > 0) {
        $alerta = "ℹ️ Observación: Hay un <strong>sobrante de $" . number_format($diferencia, 2) . "</strong> respecto al total esperado.";
    }

    $update = mysqli_query($conexion, "UPDATE caja SET fecha_cierre = '$fechaCierre', monto_final = $montoReal, estado = 'cerrada' WHERE id = $idCaja");

    if ($update) {
        $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'texto' => 'Caja cerrada correctamente'];
        header("Location: ventas.php");
        exit;
    } else {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Error al cerrar la caja'];
        header("Location: cerrar_caja.php");
        exit;
    }
}


include_once "includes/header.php";
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h4 class="text-white">Cierre de Caja</h4>
        </div>
        <div class="card-body">
           <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong><?= $error['titulo'] ?>:</strong> <?= $error['texto'] ?>
                </div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $mensaje['tipo'] === 'success' ? 'success' : 'danger' ?>">
                    <strong><?= $mensaje['titulo'] ?>:</strong> <?= $mensaje['texto'] ?>
                </div>
            <?php endif; ?>

            <?php if ($alerta): ?>
                <div class="alert alert-warning"><?= $alerta ?></div>
            <?php endif; ?>

            <p><strong>Fecha de Apertura:</strong> <?php echo $fechaApertura; ?></p>
            <p><strong>Monto Inicial:</strong> $<?php echo number_format($caja['monto_inicial'], 2); ?></p>
            <p><strong>Total en Ventas:</strong> $<?php echo number_format($totalVentas, 2); ?></p>
            <hr>
            <form method="POST">
                <div class="form-group">
                    <label for="monto_real">Monto final en caja (efectivo real contado):</label>
                    <input type="number" step="0.01" name="monto_real" id="monto_real" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-danger mt-3">Cerrar Caja</button>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>