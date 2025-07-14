<?php
ob_start(); 
require("../conexion.php");
date_default_timezone_set('America/Mexico_City');
require_once  'pdf/fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die("ID de caja no proporcionado");
}

$idCaja = intval($_GET['id']);

// Obtener datos de la caja
$caja = mysqli_query($conexion, "
    SELECT c.*, u.nombre AS usuario 
    FROM caja c 
    INNER JOIN usuario u ON u.idusuario = c.usuario_id 
    WHERE c.id = $idCaja
");

if (mysqli_num_rows($caja) === 0) {
    die("Caja no encontrada");
}

$data = mysqli_fetch_assoc($caja);

// Calcular ventas y diferencia
$ventas = 0;
$f1 = $data['fecha_apertura'];
$f2 = $data['fecha_cierre'];
$idUsuario = $data['usuario_id'];

$qVentas = mysqli_query($conexion, "
    SELECT v.id, v.fecha, v.total 
    FROM ventas v 
    WHERE v.id_usuario = $idUsuario AND v.fecha BETWEEN '$f1' AND '$f2'
");

$detalles = [];
while ($v = mysqli_fetch_assoc($qVentas)) {
    $ventas += $v['total'];
    $idVenta = $v['id'];
    $v['productos'] = [];

    $prod = mysqli_query($conexion, "
        SELECT dv.*, p.descripcion 
        FROM detalle_venta dv 
        INNER JOIN producto p ON p.codproducto = dv.id_producto 
        WHERE dv.id_venta = $idVenta
    ");

    while ($row = mysqli_fetch_assoc($prod)) {
        $v['productos'][] = $row;
    }

    $detalles[] = $v;
}

$esperado = $data['monto_inicial'] + $ventas;
$diferencia = $data['monto_final'] - $esperado;

// 🧾 Generar PDF
$pdf = new FPDF();
$pdf->AddPage();

// Logo (ajusta la ruta y tamaño si es necesario)
$pdf->Image('pdf/logo.png', 10, 8, 35); // X, Y, ancho
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode("Farmacia XYZ"), 0, 1, 'C');
$pdf->Ln(10);

// Encabezado de sección
$pdf->SetFillColor(52, 58, 64); // Gris oscuro (Bootstrap dark)
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode("CIERRE DE CAJA"), 0, 1, 'C', true);
$pdf->Ln(5);

// Restaurar colores normales
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 12);

// Información principal
$pdf->Cell(95, 8, "Usuario: " . utf8_decode($data['usuario']), 0, 0);
$pdf->Cell(95, 8, "Estado: " . ucfirst($data['estado']), 0, 1);

$pdf->Cell(95, 8, "Fecha Apertura: " . $data['fecha_apertura'], 0, 0);
$pdf->Cell(95, 8, "Fecha Cierre: " . ($data['fecha_cierre'] ?? '---'), 0, 1);

$pdf->Cell(95, 8, "Monto Inicial: $" . number_format($data['monto_inicial'], 2), 0, 0);
$pdf->Cell(95, 8, "Monto Final: $" . number_format($data['monto_final'], 2), 0, 1);

$pdf->Cell(95, 8, "Total Ventas: $" . number_format($ventas, 2), 0, 0);
$pdf->Cell(95, 8, "Diferencia: $" . number_format($diferencia, 2), 0, 1);

// Línea divisoria
$pdf->Ln(8);
$pdf->SetDrawColor(52, 58, 64);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// Detalle de ventas
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(108, 117, 125); // Gris medio
$pdf->SetTextColor(255);
$pdf->Cell(0, 8, utf8_decode("Detalle de Ventas"), 0, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->Ln(3);

foreach ($detalles as $venta) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, "Venta #{$venta['id']} - " . $venta['fecha'] . " - $" . number_format($venta['total'], 2), 0, 1);

    $pdf->SetFont('Arial', '', 10);
    foreach ($venta['productos'] as $p) {
        $pdf->Cell(10, 6, $p['cantidad'], 0, 0);
        $pdf->Cell(110, 6, utf8_decode($p['descripcion']), 0, 0);
        $pdf->Cell(30, 6, "$" . number_format($p['precio'], 2), 0, 0);
        $pdf->Cell(40, 6, "$" . number_format($p['total'], 2), 0, 1);
    }
    $pdf->Ln(3);
}

if (ob_get_length()) {
    ob_end_clean(); 
}
$pdf->Output();
exit;