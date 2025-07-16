<?php
session_start();
require("../conexion.php");

$id_user = $_SESSION['idUser'];
$permiso = "productos";

$sql = mysqli_query($conexion, "SELECT p.*, d.* 
    FROM permisos p 
    INNER JOIN detalle_permisos d ON p.id = d.id_permiso 
    WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");

$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header("Location: permisos.php");
    exit;
}

if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);


    $queryLotes = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM lotes WHERE id_producto = $id");
    $res = mysqli_fetch_assoc($queryLotes);

    if ($res['total'] > 0) {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'titulo' => 'Error',
            'texto' => 'No se puede eliminar el producto porque tiene lotes registrados.'
        ];
        mysqli_close($conexion);
        header("Location: productos.php");
        exit;
    }

    $query_delete = mysqli_query($conexion, "DELETE FROM producto WHERE codproducto = $id");
    if ($query_delete) {
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Éxito',
            'texto' => 'Producto eliminado correctamente.'
        ];
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'titulo' => 'Error',
            'texto' => 'Error al intentar eliminar el producto.'
        ];
    }
    mysqli_close($conexion);
    header("Location: productos.php");
    exit;
}
