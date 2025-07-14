<?php
session_start();
require_once "../conexion.php";

$id_user = $_SESSION['idUser'] ?? null;
$permiso = "medicos";

$sql = mysqli_query($conexion, "SELECT p.*, d.* 
    FROM permisos p 
    INNER JOIN detalle_permisos d ON p.id = d.id_permiso 
    WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit;
}

if (empty($_GET['id'])) {
    header("Location: medicos.php");
    exit;
}

$idMedico = intval($_GET['id']);

$query = mysqli_query($conexion, "SELECT * FROM medicos WHERE id = $idMedico");
$medico = mysqli_fetch_assoc($query);

if (!$medico) {
    $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Oops!', 'texto' => 'El medico no existe.'];
    header("Location: medicos.php");
    exit;
}
$queryDelete = mysqli_query($conexion, "DELETE FROM medicos WHERE id = $idMedico");

if ($queryDelete) {
    $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Eliminado!', 'texto' => 'Medico eliminado correctamente.'];
} else {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error!', 'texto' => 'No se pudo eliminar el medico.'];
}

header("Location: medicos.php");
exit;
