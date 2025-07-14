<?php
require_once __DIR__ . '/../vendor/autoload.php';
include "../conexion.php";

if (!isset($_GET['id'])) {
    die('Compra no especificada.');
}

$idCompra = intval($_GET['id']);

// Traer datos de la compra
$query = mysqli_query($conexion, "
    SELECT c.*, l.laboratorio, u.nombre AS usuario
    FROM compras c
    INNER JOIN laboratorios l ON c.id_proveedor = l.id
    INNER JOIN usuario u ON c.id_usuario = u.idusuario
    WHERE c.id = $idCompra
");

$compra = mysqli_fetch_assoc($query);

if (!$compra) {
    die('Compra no encontrada.');
}

// Traer detalle de lotes
$queryDetalle = mysqli_query($conexion, "
    SELECT l.*, p.descripcion, p.codigo
    FROM lotes l
    INNER JOIN producto p ON l.id_producto = p.codproducto
    WHERE l.id_compra = $idCompra
");

$detalleHTML = "";
$total = 0;

while ($row = mysqli_fetch_assoc($queryDetalle)) {
    $subtotal = $row['cantidad'] * $row['costo'];
    $total += $subtotal;
    $detalleHTML .= '
        <tr>
            <td>'.$row['codigo'].'</td>
            <td>'.$row['descripcion'].'</td>
            <td>'.$row['lote'].'</td>
            <td>'.date('d/m/Y', strtotime($row['fecha_vencimiento'])).'</td>
            <td align="center">'.$row['cantidad'].'</td>
            <td align="right">$'.number_format($row['costo'], 2).'</td>
            <td align="right">$'.number_format($subtotal, 2).'</td>
        </tr>
    ';
}

// Plantilla HTML
$html = '
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        color: #333;
    }
    .logo {
        text-align: center;
        margin-bottom: 10px;
    }
    .logo img {
        max-height: 80px;
    }
    h2 {
        color: #1E9478;
        text-align: center;
        margin-top: 10px;
    }
    .info-table {
        width: 100%;
        margin-top: 10px;
        margin-bottom: 20px;
    }
    .info-table td {
        padding: 5px;
        font-size: 11px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th {
        background-color: #1E9478;
        color: #fff;
        padding: 6px;
        font-size: 11px;
        border: 1px solid #ddd;
    }
    td {
        padding: 6px;
        border: 1px solid #ddd;
        font-size: 10px;
    }
    .total-row td {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .firmas {
        margin-top: 50px;
        width: 100%;
        text-align: center;
        font-size: 11px;
    }
    .firmas td {
        padding-top: 40px;
    }
    .footer {
        text-align: center;
        font-size: 9px;
        color: #999;
        margin-top: 20px;
    }
</style>

<div class="logo">
    <img src="../img/logo.png" alt="Logo Farmacia">
</div>

<h2>Reporte de Compra</h2>

<table class="info-table">
    <tr>
        <td><strong>Folio:</strong> '.$compra['id'].'</td>
        <td><strong>Fecha:</strong> '.$compra['fecha'].'</td>
    </tr>
    <tr>
        <td><strong>Proveedor:</strong> '.$compra['laboratorio'].'</td>
        <td><strong>Usuario:</strong> '.$compra['usuario'].'</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Lote</th>
            <th>Vencimiento</th>
            <th>Cantidad</th>
            <th>Costo Unitario</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        '.$detalleHTML.'
        <tr class="total-row">
            <td colspan="6" align="right">Total:</td>
            <td>$'.number_format($total, 2).'</td>
        </tr>
    </tbody>
</table>

<table class="firmas">
    <tr>
        <td>___________________________<br>Firma Proveedor</td>
        <td>___________________________<br>Firma Usuario</td>
    </tr>
</table>

<div class="footer">
    Reporte generado el '.date('d/m/Y H:i').'
</div>
';

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_left' => 10,
    'margin_right' => 10,
    'default_font_size' => 10,
    'default_font' => 'Arial'
]);

$mpdf->WriteHTML($html);
$mpdf->Output('compra_'.$idCompra.'.pdf', 'I');
