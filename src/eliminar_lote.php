<?php
session_start();
include "../conexion.php";

if (!empty($_POST['id_lote'])) {
    $id_lote = intval($_POST['id_lote']);
    error_log("ID LOTE RECIBIDO: $id_lote");


    $query = mysqli_query($conexion, "SELECT * FROM lotes WHERE id_lote = $id_lote");
    if (mysqli_num_rows($query) > 0) {
        $update = mysqli_query($conexion, "UPDATE lotes SET estado = 'inactivo' WHERE id_lote = $id_lote");

        if ($update) {
            $_SESSION['mensaje'] = [
                'tipo' => 'success',
                'titulo' => 'Éxito',
                'texto' => 'Lote eliminado correctamente.'
            ];
        } else {
            $_SESSION['mensaje'] = [
                'tipo' => 'danger',
                'titulo' => 'Error',
                'texto' => 'No se pudo eliminar el lote.'
            ];
        }
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'danger',
            'titulo' => 'Error',
            'texto' => 'Lote no encontrado.'
        ];
    }
}

header("Location: productos.php");
exit;
?>
