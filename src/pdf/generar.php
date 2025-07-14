<?php
ob_start();
require_once '../../conexion.php';
date_default_timezone_set('America/Mexico_City');
require_once 'fpdf/fpdf.php';

if(!$conexion) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$pdf = new FPDF('P', 'mm', array(80, 200));
$pdf->AddPage();
$pdf->SetMargins(5, 5, 5);
$pdf->SetTitle("Ticket de Venta");
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetAutoPageBreak(true, 10);
$id = $_GET['v'] ?? '';
$idcliente = $_GET['cl'] ?? '';

// Colores personalizados
$headerColor = array(102, 102, 102); 
$lightColor = array(241, 243, 245); // Gris claro para fondos
$darkColor = array(51, 51, 51);     // Texto oscuro

// Consultas SQL con verificación de errores
$config = mysqli_query($conexion, "SELECT * FROM configuracion");
if(!$config) die('Error en consulta de configuración: ' . mysqli_error($conexion));

$datos = mysqli_fetch_assoc($config);

$clientes = mysqli_query($conexion, "SELECT * FROM cliente WHERE idcliente = $idcliente");
if(!$clientes) die('Error en consulta de cliente: ' . mysqli_error($conexion));

$datosC = mysqli_fetch_assoc($clientes);

$ventas = mysqli_query($conexion, "SELECT d.*, p.codproducto, p.descripcion FROM detalle_venta d INNER JOIN producto p ON d.id_producto = p.codproducto WHERE d.id_venta = $id");
if(!$ventas) die('Error en consulta de ventas: ' . mysqli_error($conexion));

$ventaInfo = mysqli_query($conexion, "SELECT metodo_pago, monto_entregado, cambio FROM ventas WHERE id = $id LIMIT 1");
$datosVenta = mysqli_fetch_assoc($ventaInfo);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor($headerColor[0], $headerColor[1], $headerColor[2]);
$pdf->Cell(70, 6, utf8_decode($datos['nombre']), 0, 1, 'C');

$pdf->Image("../../assets/img/logos/mlogo.png", 18, 16, 50, 15, 'PNG');

// Información de la empresa en caja con fondo
$pdf->SetY(35); // Posición después del logo
$pdf->SetFillColor($lightColor[0], $lightColor[1], $lightColor[2]);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(70, 5, 'INFORMACION DE LA EMPRESA', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor($darkColor[0], $darkColor[1], $darkColor[2]);

// Función para mostrar datos en dos columnas
function addInfoRow($pdf, $label, $value, $width = 70) {
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(25, 5, $label, 0, 0);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell($width-25, 5, $value, 0, 1);
}

addInfoRow($pdf, utf8_decode("Teléfono:"), $datos['telefono']);
addInfoRow($pdf, utf8_decode("Dirección:"), utf8_decode($datos['direccion']));
addInfoRow($pdf, "Correo:", utf8_decode($datos['email']));
$pdf->Ln(5);


// --- DATOS DEL CLIENTE --- //
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
$pdf->Cell(70, 6, "DATOS DEL CLIENTE", 1, 1, 'C', true);

$pdf->SetTextColor($darkColor[0], $darkColor[1], $darkColor[2]);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(35, 5, 'Nombre', 0, 0);
$pdf->Cell(35, 5, 'Telefono', 0, 1);
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(35, 5, utf8_decode($datosC['nombre']), 0, 0);
$pdf->Cell(35, 5, utf8_decode($datosC['telefono']), 0, 1);
$pdf->SetFont('Arial', 'B', 7);
//$pdf->Cell(35, 5, 'Direccion', 0, 1);
//$pdf->SetFont('Arial', '', 7);
//$pdf->MultiCell(70, 5, utf8_decode($datosC['direccion']), 0, 'L');
$pdf->Ln(3);

// --- DETALLE DE PRODUCTOS --- //
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
$pdf->Cell(70, 6, "DETALLE DE PRODUCTOS", 1, 1, 'C', true);

// Encabezados de la tabla
$pdf->SetTextColor($darkColor[0], $darkColor[1], $darkColor[2]);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(30, 6, 'Descripcion', 0, 0, 'L');
$pdf->Cell(10, 6, 'Cant.', 0, 0, 'C');
$pdf->Cell(15, 6, 'Precio', 0, 0, 'R');
$pdf->Cell(15, 6, 'Subtotal', 0, 1, 'R');

// Línea divisoria
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(2);

// Detalle de productos
$pdf->SetFont('Arial', '', 7);
$total = 0.00;
$desc = 0.00;
while ($row = mysqli_fetch_assoc($ventas)) {
    $pdf->Cell(30, 5, utf8_decode(substr($row['descripcion'], 0, 20)), 0, 0, 'L');
    $pdf->Cell(10, 5, $row['cantidad'], 0, 0, 'C');
    $pdf->Cell(15, 5, '$'.number_format($row['precio'], 2, '.', ','), 0, 0, 'R');
    $sub_total = $row['total'];
    $total = $total + $sub_total;
    $iva = $total - ($total / 1.16);
    $baseImponible = $total / 1.16;
    $desc = $desc + $row['descuento'];
    $pdf->Cell(15, 5, '$'.number_format($sub_total, 2, '.', ','), 0, 1, 'R');
}


// --- TOTALES --- //
$pdf->Ln(5);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(3);

// Función para mostrar totales
function addTotalRow($pdf, $label, $value, $bold = false, $width = 70) {
    $pdf->SetFont('Arial', $bold ? 'B' : '', 8);
    $pdf->Cell($width-20, 6, $label, 0, 0, 'R');
    $pdf->Cell(20, 6, '$'.number_format($value, 2, '.', ','), 0, 1, 'R');
}

addTotalRow($pdf, 'Descuento Total:', $desc);
addTotalRow($pdf, 'Subtotal:', $baseImponible);
addTotalRow($pdf, 'IVA (16%):', $iva);
$pdf->Ln(2);

// Línea divisoria antes del total
$pdf->SetDrawColor(100, 100, 100);
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(3);

// Total a pagar destacado
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor($headerColor[0], $headerColor[1], $headerColor[2]);
$pdf->Cell(50, 8, 'TOTAL A PAGAR:', 0, 0, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 8, '$'.number_format($total, 2, '.', ','), 0, 1, 'R');
$pdf->Ln(5);


// Método de pago 
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor($darkColor[0], $darkColor[1], $darkColor[2]);
$pdf->Cell(50, 6, 'Metodo de pago:', 0, 0, 'R');
$pdf->Cell(20, 6, ucfirst($datosVenta['metodo_pago']), 0, 1, 'R');

// Solo mostrar entregado y cambio si es efectivo
if ($datosVenta['metodo_pago'] === 'efectivo') {
    $pdf->Cell(50, 6, 'Monto entregado:', 0, 0, 'R');
    $pdf->Cell(20, 6, '$'.number_format($datosVenta['monto_entregado'], 2), 0, 1, 'R');

    $pdf->Cell(50, 6, 'Cambio:', 0, 0, 'R');
    $pdf->Cell(20, 6, '$'.number_format($datosVenta['cambio'], 2), 0, 1, 'R');
}

$pdf->Ln(4);


// Pie de página
$pdf->SetFont('Arial', 'I', 6);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(70, 4, utf8_decode('Gracias por su compra!'), 0, 1, 'C');
$pdf->Cell(70, 4, date('d/m/Y H:i:s'), 0, 1, 'C');
$pdf->Cell(70, 4, utf8_decode('Este ticket es su comprobante de pago'), 0, 1, 'C');


ob_end_clean();

$pdf->Output("ventas.pdf", "I");

?>