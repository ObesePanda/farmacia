<?php
include("../conexion.php");
if ($_POST['action'] == 'sales') {
    $arreglo = array();
    $query = mysqli_query($conexion, "
        SELECT 
            p.codproducto,
            p.descripcion,
            IFNULL(SUM(l.cantidad), 0) AS existencia,
            p.minimo,
            p.maximo
        FROM producto p
        LEFT JOIN lotes l ON p.codproducto = l.id_producto
        GROUP BY p.codproducto, p.descripcion, p.minimo, p.maximo
        HAVING existencia <= p.minimo
        ORDER BY existencia ASC
        LIMIT 10
    ");
    while ($data = mysqli_fetch_array($query)) {
        $arreglo[] = $data;
    }
    echo json_encode($arreglo);
    die();
}
if ($_POST['action'] == 'polarChart') {
    $arreglo = array();
    $query = mysqli_query($conexion, "SELECT p.codproducto, p.descripcion, d.id_producto, d.cantidad, SUM(d.cantidad) as total FROM producto p INNER JOIN detalle_venta d WHERE p.codproducto = d.id_producto group by d.id_producto ORDER BY d.cantidad DESC LIMIT 5");
    while ($data = mysqli_fetch_array($query)) {
        $arreglo[] = $data;
    }
    echo json_encode($arreglo);
    die();
}
//
?>