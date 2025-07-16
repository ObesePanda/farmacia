<?php
include "../conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = mysqli_query($conexion, "
   SELECT id_lote, lote, fecha_vencimiento, cantidad, costo
    FROM lotes
    WHERE id_producto = $id
    AND estado = 'activo'
");

$lotes = [];

while ($row = mysqli_fetch_assoc($query)) {
    $lotes[] = [
        'lote' => $row['lote'],
        'fecha_vencimiento' => $row['fecha_vencimiento'] ? date('d/m/Y', strtotime($row['fecha_vencimiento'])) : '',
        'cantidad' => $row['cantidad'],
        'costo' => $row['costo'],
        'id_lote' => $row['id_lote'],
    ];
}

echo json_encode($lotes);
