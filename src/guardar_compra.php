<?php
session_start();
include "../conexion.php";

if (!empty($_POST)) {
    $proveedor = intval($_POST['proveedor']);
    $total = floatval($_POST['total']);
    $detalle = json_decode($_POST['detalle'], true);
    $usuario = $_SESSION['idUser'];

    if ($proveedor <= 0 || $total <= 0 || empty($detalle)) {
        echo json_encode([
            'status' => false,
            'mensaje' => 'Datos incompletos para registrar la compra.'
        ]);
        exit;
    }

    // Registrar la compra principal
    $fecha = date('Y-m-d H:i:s');
    $query = mysqli_query($conexion, "INSERT INTO compras (id_proveedor, total, fecha, id_usuario ) VALUES ($proveedor, $total, '$fecha', $usuario)");

    if ($query) {
        $id_compra = mysqli_insert_id($conexion);
        if (empty($detalle)) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'No se recibieron productos en el detalle.'
            ]);
            exit;
        }

        foreach ($detalle as $item) {
            $id_producto = intval($item['id']);
            $cantidad = intval($item['cantidad']);
            $precio = floatval($item['precio']);
            $subtotal = floatval($item['subtotal']);
            $lote = mysqli_real_escape_string($conexion, $item['lote']);
            $vencimiento = mysqli_real_escape_string($conexion, $item['vencimiento']);

            if ($id_producto > 0) {
                // Insertar lote
                mysqli_query($conexion, "
                    INSERT INTO lotes
                    (id_compra, id_producto, lote, fecha_vencimiento, cantidad, costo, subtotal)
                    VALUES
                    ($id_compra, $id_producto, '$lote', '$vencimiento', $cantidad, $precio, $subtotal)
                ");

                // Actualizar stock total del producto
                mysqli_query($conexion, "
                    UPDATE producto
                    SET existencia = existencia + $cantidad
                    WHERE codproducto = $id_producto
                ");
            }
        }

        echo json_encode([
            'status' => true,
            'mensaje' => 'Compra registrada correctamente.',
             'id' => $id_compra
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'mensaje' => 'Error al registrar la compra.'
        ]);
    }
    exit;
}
