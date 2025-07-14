<?php
session_start();
require_once "../conexion.php";

$id_user = $_SESSION['idUser'] ?? null;
$permiso = "pacientes";

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
    header("Location: pacientes.php");
    exit;
}

$idPaciente = intval($_GET['id']);

$query = mysqli_query($conexion, "SELECT * FROM pacientes WHERE id = $idPaciente");
$paciente = mysqli_fetch_assoc($query);

if (!$paciente) {
    $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Oops!', 'texto' => 'El paciente no existe.'];
    header("Location: pacientes.php");
    exit;
}
$queryDelete = mysqli_query($conexion, "DELETE FROM pacientes WHERE id = $idPaciente");

if ($queryDelete) {
    $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Eliminado!', 'texto' => 'Paciente eliminado correctamente.'];
} else {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error!', 'texto' => 'No se pudo eliminar el paciente.'];
}

header("Location: pacientes.php");
exit;
