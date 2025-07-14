<?php
require_once __DIR__ . '/../vendor/autoload.php'; 
include "../conexion.php";


$queryProductos = mysqli_query($conexion, "SELECT * FROM producto ORDER BY descripcion ASC");


$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
    h2 { color: #1E9478; text-align: center; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th { background-color: #1E9478; color: white; padding: 8px; border: 1px solid #ddd; }
    td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
    .lote-info { font-size: 11px; margin: 0; }
    .vencido { color: red; font-weight: bold; }
    .header { text-align: center; margin-bottom: 30px; }
    .footer { font-size: 10px; color: #999; text-align: center; margin-top: 30px; }
</style>

<div class="header">
    <img src="../img/logo.png" alt="Logo" style="height: 60px; margin-bottom: 10px;">
    <h1>Reporte de Inventario</h1>
    <p>Farmacia S.A. de C.V.</p>
</div>

<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Precio</th>
            <th>Inventario Total</th>
            <th>Lotes (Cantidad - Vencimiento)</th>
        </tr>
    </thead>
    <tbody>
';

while ($producto = mysqli_fetch_assoc($queryProductos)) {
 
    $queryLotes = mysqli_query($conexion, "SELECT cantidad, fecha_vencimiento FROM lotes WHERE id_producto = {$producto['codproducto']} ORDER BY fecha_vencimiento ASC");
    
    $totalInventario = 0;
    $lotesHtml = '';
    $hoy = date('Y-m-d');
    
    while ($lote = mysqli_fetch_assoc($queryLotes)) {
        $totalInventario += $lote['cantidad'];
        $fechaVenc = $lote['fecha_vencimiento'];
        $vencido = (!empty($fechaVenc) && $fechaVenc < $hoy);
        
        $fechaFormateada = !empty($fechaVenc) ? date('d/m/Y', strtotime($fechaVenc)) : 'Sin fecha';
        
        $lotesHtml .= '<p class="lote-info'.($vencido ? ' vencido' : '').'">'
                    . $lote['cantidad'] . ' unidades - ' . $fechaFormateada;
        if ($vencido) {
            $lotesHtml .= ' <strong>(Vencido)</strong>';
        }
        $lotesHtml .= '</p>';
    }
    
    // Si no tiene lotes
    if (empty($lotesHtml)) {
        $lotesHtml = '<p class="lote-info text-muted">Sin lotes registrados</p>';
    }
    
    $html .= '<tr>
        <td align="center">'.htmlspecialchars($producto['codigo']).'</td>
        <td>'.htmlspecialchars($producto['descripcion']).'</td>
        <td align="right">$'.number_format($producto['precio'], 2).'</td>
        <td align="center">'.$totalInventario.'</td>
        <td>'.$lotesHtml.'</td>
    </tr>';
}

$html .= '
    </tbody>
</table>

<div class="footer">
    <p>Reporte generado el '.date('d/m/Y H:i').'</p>
</div>
';

// Generar PDF con mPDF
$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 25,
    'margin_bottom' => 25,
    'margin_left' => 15,
    'margin_right' => 15,
    'default_font_size' => 10,
    'default_font' => 'Arial'
]);

$mpdf->SetTitle('Reporte Inventario');
$mpdf->WriteHTML($html);
$mpdf->Output('reporte_inventario_' . date('Ymd_His') . '.pdf', 'I');
