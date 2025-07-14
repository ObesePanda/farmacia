<?php
require_once "../conexion.php";
session_start();
if (isset($_GET['q'])) {
    $datos = array();
    $nombre = $_GET['q'];
    $cliente = mysqli_query($conexion, "SELECT * FROM cliente WHERE nombre LIKE '%$nombre%'");
    while ($row = mysqli_fetch_assoc($cliente)) {
        $data['id'] = $row['idcliente'];
        $data['label'] = $row['nombre'];
        $data['direccion'] = $row['direccion'];
        $data['telefono'] = $row['telefono'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
}else if (isset($_GET['pro'])) {
    $datos = array();
    $nombre = $_GET['pro'];
    $hoy = date('Y-m-d');
    $producto = mysqli_query($conexion, "SELECT * FROM producto WHERE codigo LIKE '%" . $nombre . "%' OR descripcion LIKE '%" . $nombre . "%' AND vencimiento > '$hoy' OR vencimiento = '0000-00-00'");
    while ($row = mysqli_fetch_assoc($producto)) {
        $data['id'] = $row['codproducto'];
        $data['label'] = $row['codigo'] . ' - ' .$row['descripcion'];
        $data['value'] = $row['descripcion'];
        $data['precio'] = $row['precio'];
        $data['existencia'] = $row['existencia'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
}else if (isset($_GET['detalle'])) {
    $id = $_SESSION['idUser'];
    $datos = array();
    $detalle = mysqli_query($conexion, "SELECT d.*, p.codproducto, p.descripcion FROM detalle_temp d INNER JOIN producto p ON d.id_producto = p.codproducto WHERE d.id_usuario = $id");
    while ($row = mysqli_fetch_assoc($detalle)) {
        $data['id'] = $row['id'];
        $data['id_producto'] = $row['codproducto'];
        $data['descripcion'] = $row['descripcion'];
        $data['cantidad'] = $row['cantidad'];
        $data['descuento'] = $row['descuento'];
        $data['precio_venta'] = $row['precio_venta'];
        $data['sub_total'] = $row['total'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
} else if (isset($_GET['delete_detalle'])) {
    $id_detalle = $_GET['id'];
    $query = mysqli_query($conexion, "DELETE FROM detalle_temp WHERE id = $id_detalle");
    if ($query) {
        $msg = "ok";
    } else {
        $msg = "Error";
    }
    echo $msg;
    die();
}else if (isset($_POST['procesarVenta'])) {
    
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['status' => false, 'mensaje' => 'Cliente no seleccionado.']);
        exit;
    }

    $id_cliente = intval($_POST['id']);
    $id_user = $_SESSION['idUser'];

    $metodo = $_POST['metodo_pago'] ?? 'efectivo';
    $entregado = floatval($_POST['monto_entregado'] ?? 0);
    $cambio = floatval($_POST['cambio'] ?? 0);

    if (!isset($_POST['detalles'])) {
        echo json_encode(['status' => false, 'mensaje' => 'No se enviaron detalles de venta.']);
        exit;
    }
    $detalles = json_decode($_POST['detalles'], true);

    if (!$detalles || count($detalles) == 0) {
        echo json_encode(['status' => false, 'mensaje' => 'Detalles de venta vacíos.']);
        exit;
    }

    $total = 0;
    foreach ($detalles as $d) {
        $total += ($d['precio'] - $d['descuento']) * $d['cantidad'];
    }

    $productosVencidos = [];
    $hoy = date('Y-m-d');
    foreach ($detalles as $d) {
        $id_lote = intval($d['id_lote']);
        $resLote = mysqli_query($conexion, "SELECT fecha_vencimiento FROM lotes WHERE id_lote = $id_lote");
        if ($resLote) {
            $rowLote = mysqli_fetch_assoc($resLote);
            if (!empty($rowLote['fecha_vencimiento']) && $rowLote['fecha_vencimiento'] < $hoy) {
                // Obtener nombre producto
                $resProd = mysqli_query($conexion, "SELECT p.descripcion FROM producto p INNER JOIN detalle_temp dt ON p.codproducto = dt.id_producto WHERE dt.id = " . intval($d['id_detalle']));
                $nombreProd = '';
                if ($resProd) {
                    $rowProd = mysqli_fetch_assoc($resProd);
                    $nombreProd = $rowProd['descripcion'] ?? '';
                }
                $productosVencidos[] = "$nombreProd (Vence: " . date("d/m/Y", strtotime($rowLote['fecha_vencimiento'])) . ")";
            }
        }
    }

    if (!empty($productosVencidos)) {
        echo json_encode([
            'status' => false,
            'mensaje' => 'No se puede procesar la venta. Los siguientes productos están vencidos:',
            'vencidos' => $productosVencidos
        ]);
        exit;
    }

    $insertar = mysqli_query($conexion, "INSERT INTO ventas (id_cliente, total, id_usuario, metodo_pago, monto_entregado, cambio) VALUES ($id_cliente, '$total', $id_user, '$metodo', '$entregado', '$cambio')");

    if (!$insertar) {
        echo json_encode(['status' => false, 'mensaje' => 'No se pudo insertar la venta']);
        exit;
    }

    $id_venta = mysqli_insert_id($conexion);

    $errorEnDetalle = false;

    foreach ($detalles as $d) {
        $id_producto = intval($d['id_detalle']); // o cambia según campo correcto para id_producto
        $cantidad = floatval($d['cantidad']);
        $desc = floatval($d['descuento']);
        $precio = floatval($d['precio']);
        $id_lote = intval($d['id_lote']);
        $total_detalle = ($precio - $desc) * $cantidad;

        $insertarDet = mysqli_query($conexion, "INSERT INTO detalle_venta (id_producto, id_venta, cantidad, precio, descuento, total, id_lote) VALUES ($id_producto, $id_venta, $cantidad, '$precio', '$desc', '$total_detalle', $id_lote)");

        if (!$insertarDet) {
            $errorEnDetalle = true;
            break;
        }

        // Actualizar stock producto
        $stockActual = mysqli_query($conexion, "SELECT existencia FROM producto WHERE codproducto = $id_producto");
        $stockNuevo = mysqli_fetch_assoc($stockActual);
        $stockTotal = $stockNuevo['existencia'] - $cantidad;
        mysqli_query($conexion, "UPDATE producto SET existencia = $stockTotal WHERE codproducto = $id_producto");

        // Actualizar stock lote
        $stockLoteActual = mysqli_query($conexion, "SELECT cantidad FROM lotes WHERE id_lote = $id_lote");
        $stockLoteNuevo = mysqli_fetch_assoc($stockLoteActual);
        $stockLoteTotal = $stockLoteNuevo['cantidad'] - $cantidad;
        mysqli_query($conexion, "UPDATE lotes SET cantidad = $stockLoteTotal WHERE id_lote = $id_lote");
    }

    if ($errorEnDetalle) {
        echo json_encode(['status' => false, 'mensaje' => 'No se pudo insertar el detalle de la venta']);
        exit;
    }

    mysqli_query($conexion, "DELETE FROM detalle_temp WHERE id_usuario = $id_user");

    echo json_encode([
        'status' => true,
        'id_cliente' => $id_cliente,
        'id_venta' => $id_venta
    ]);
    exit;
}

else if (isset($_GET['descuento'])) {
    $id = $_GET['id'];
    $desc = $_GET['desc'];
    $consulta = mysqli_query($conexion, "SELECT * FROM detalle_temp WHERE id = $id");
    $result = mysqli_fetch_assoc($consulta);
    $total_desc = $desc + $result['descuento'];
    $total = $result['total'] - $desc;
    $insertar = mysqli_query($conexion, "UPDATE detalle_temp SET descuento = $total_desc, total = '$total'  WHERE id = $id");
    if ($insertar) {
        $msg = array('mensaje' => 'descontado');
    }else{
        $msg = array('mensaje' => 'error');
    }
    echo json_encode($msg);
    die();
}else if(isset($_GET['editarCliente'])){
    $idcliente = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM cliente WHERE idcliente = $idcliente");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarUsuario'])) {
    $idusuario = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM usuario WHERE idusuario = $idusuario");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarProducto'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM producto WHERE codproducto = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarTipo'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM tipos WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarPresent'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM presentacion WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarLab'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM laboratorios WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
}
if (isset($_POST['regDetalle'])) {
    $id = $_POST['id'];
    $cant = $_POST['cant'];
    $precio = $_POST['precio'];
    $id_user = $_SESSION['idUser'];
    $total = $precio * $cant;
    $verificar = mysqli_query($conexion, "SELECT * FROM detalle_temp WHERE id_producto = $id AND id_usuario = $id_user");
    $result = mysqli_num_rows($verificar);
    $datos = mysqli_fetch_assoc($verificar);
    if ($result > 0) {
        $cantidad = $datos['cantidad'] + $cant;
        $total_precio = ($cantidad * $total);
        $query = mysqli_query($conexion, "UPDATE detalle_temp SET cantidad = $cantidad, total = '$total_precio' WHERE id_producto = $id AND id_usuario = $id_user");
        if ($query) {
            $msg = "actualizado";
        } else {
            $msg = "Error al ingresar";
        }
    }else{
        $query = mysqli_query($conexion, "INSERT INTO detalle_temp(id_usuario, id_producto, cantidad ,precio_venta, total) VALUES ($id_user, $id, $cant,'$precio', '$total')");
        if ($query) {
            $msg = "registrado";
        }else{
            $msg = "Error al ingresar";
        }
    }
    echo json_encode($msg);
    die();
}else if (isset($_POST['cambio'])) {
    if (empty($_POST['actual']) || empty($_POST['nueva'])) {
        $msg = 'Los campos estan vacios';
    } else {
        $id = $_SESSION['idUser'];
        $actual = md5($_POST['actual']);
        $nueva = md5($_POST['nueva']);
        $consulta = mysqli_query($conexion, "SELECT * FROM usuario WHERE clave = '$actual' AND idusuario = $id");
        $result = mysqli_num_rows($consulta);
        if ($result == 1) {
            $query = mysqli_query($conexion, "UPDATE usuario SET clave = '$nueva' WHERE idusuario = $id");
            if ($query) {
                $msg = 'ok';
            }else{
                $msg = 'error';
            }
        } else {
            $msg = 'dif';
        }
        
    }
    echo $msg;
    die();
    
}
if (isset($_GET['pro_compra'])) {
    $term = $conexion->real_escape_string($_GET['pro_compra']);
    $query = mysqli_query($conexion, "
        SELECT 
            codproducto AS id,
            descripcion AS value,
            precio
        FROM producto
        WHERE codigo LIKE '%$term%' 
           OR descripcion LIKE '%$term%'
        LIMIT 10
    ");

    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = [
            "id" => $row['id'],
            "value" => $row['value'],
            "precio" => $row['precio']
        ];
    }
    echo json_encode($data);
    exit;
}
if (isset($_POST['accion']) && $_POST['accion'] == 'lotes_producto') {
  
    $id_producto = intval($_POST['id_producto']);

    $query = mysqli_query($conexion, "
        SELECT id_lote, lote, fecha_vencimiento, cantidad, costo
        FROM lotes
        WHERE id_producto = $id_producto
        ORDER BY fecha_vencimiento ASC
    ");

    $lotes = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $lotes[] = [
            'id' => $row['id_lote'],
            'numero_lote' => $row['lote'],
            'vencimiento' => $row['fecha_vencimiento'] ? date('Y-m-d', strtotime($row['fecha_vencimiento'])) : '',
            'cantidad' => $row['cantidad'],
            'costo' => $row['costo'],
        ];
    }

    echo json_encode($lotes);
    exit;
}
if (isset($_POST['accion']) && $_POST['accion'] == 'lotes_producto') {
    var_dump($_POST); exit;
}