<?php
require_once "../conexion.php";
require_once "../vendor/autoload.php";

use Mpdf\Mpdf;

$id_consulta = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_consulta <= 0) {
    die("Consulta inválida.");
}

$query = mysqli_query($conexion, "
    SELECT c.*, 
           p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
           p.fecha_nacimiento, p.sexo,
           m.nombre AS medico_nombre, m.especialidad, m.cedula_profesional AS medico_cedula,
           m.firma
    FROM consultas c
    INNER JOIN pacientes p ON c.id_paciente = p.id
    INNER JOIN medicos m ON c.id_medico = m.id
    WHERE c.id = $id_consulta
");

$data = mysqli_fetch_assoc($query);
if (!$data) {
    die("No se encontró la consulta.");
}

$recetas = [];
$res = mysqli_query($conexion, "
    SELECT * FROM recetas
    WHERE id_consulta = $id_consulta
");

while ($row = mysqli_fetch_assoc($res)) {
    $recetas[] = $row;
}

$html = '
<style>
    body {
        font-family: "Helvetica Neue", Arial, sans-serif;
        font-size: 12pt;
        color: #333;
        line-height: 1.5;
    }
    .header {
        border-bottom: 2px solid #007b5e;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .logo {
        float: left;
        width: 30%;
    }
    .logo img {
        height: 80px;
    }
    .clinic-info {
        float: right;
        width: 65%;
        text-align: right;
        font-size: 10pt;
        color: #383b39;
    }
    .clinic-name {
        font-size: 16pt;
        font-weight: bold;
        color: #282b2b;
        margin-bottom: 5px;
    }
    .clear {
        clear: both;
    }
    .document-title {
        text-align: center;
        font-size: 16pt;
        color: #465252;
        margin: 20px 0;
        font-weight: bold;
        text-transform: uppercase;
    }
    .patient-info {
        width: 100%;
        margin-bottom: 20px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
    .patient-info table {
        width: 100%;
        border-collapse: collapse;
    }
    .patient-info td {
        padding: 5px 10px;
        vertical-align: top;
        font-size: 10px;
    }
    .patient-info .label {
        font-weight: bold;
        color: #282b2b;
        width: 25%;
    }
    .section {
        margin-bottom: 20px;
    }
    .section-title {
        background: #a1b3b5;
        color: #fff;
        padding: 8px 15px;
        margin: 15px 0 10px 0;
        font-weight: bold;
        border-radius: 4px;
        font-size: 10pt;
    }
    .section-content {
        padding: 0 10px;
        font-size: 10px;
        text-align: justify;
    }
    .medicines-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        font-size: 11px;
    }
    .medicines-table th {
        background: #16454a;
        color: white;
        padding: 10px;
        text-align: left;
    }
    .medicines-table td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }
    .medicines-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .signature-area {
       
        margin-top: 50px;
        text-align: center;
    }
    .signature-line {
        border-top: 1px solid #333;
        width: 300px;
        margin-left: 200px;
        margin-top: 50px;
    }
    .signature-text {
        text-align: center;
        width: 300px;
        margin-left: 200px;
        margin-top: 5px;
        font-size: 10pt;
    }
    .footer {
        margin-top: 40px;
        font-size: 9pt;
        text-align: center;
        color: #777;
        border-top: 1px solid #eee;
        padding-top: 10px;
    }
    .qr-code {
        float: right;
        margin: 10px;
        padding: 5px;
        border: 1px solid #eee;
    }
    .no-medicines {
        font-style: italic;
        color: #777;
        padding: 10px;
    }
</style>

<div class="header">
    <div class="logo">
        <img src="../assets/img/logos/mlogo.png">
    </div>
    <div class="clinic-info">
        <div class="clinic-name">CLÍNICA MÉDICA ESPECIALIZADA</div>
        <div>Calle Principal #123, Ciudad</div>
        <div>Teléfono: (555) 123-4567</div>
        <div>Email: contacto@clinica.com</div>
        <div>Horario: Lunes a Viernes 8:00 - 18:00</div>
    </div>
    <div class="clear"></div>
</div>

<div class="document-title">Reporte de Consulta Médica</div>

<div class="patient-info">
    <table>
        <tr>
            <td class="label">Paciente:</td>
            <td>' . $data['paciente_nombre'] . ' ' . $data['paciente_apellido'] . '</td>
       
        </tr>
        <tr>
            <td class="label">Fecha Nacimiento:</td>
            <td>' . $data['fecha_nacimiento'] . '</td>
            <td class="label">Género:</td>
            <td>' . $data['sexo'] . '</td>
        </tr>
        <tr>
            <td class="label">Médico:</td>
            <td>Dr(a). ' . $data['medico_nombre'] . ' (' . ($data['especialidad'] ?? 'General') . ')</td>
            <td class="label">Fecha Consulta:</td>
            <td>' . $data['fecha_consulta'] . '</td>
        </tr>
        <tr>
            <td class="label">Cédula Profesional:</td>
            <td>' . ($data['medico_cedula'] ?? 'N/A') . '</td>
            <td class="label">Folio:</td>
            <td>CON-' . str_pad($id_consulta, 6, '0', STR_PAD_LEFT) . '</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Motivo de la Consulta</div>
    <div class="section-content">' . nl2br($data['motivo']) . '</div>
</div>

<div class="section">
    <div class="section-title">Síntomas Presentados</div>
    <div class="section-content">' . nl2br($data['sintomas']) . '</div>
</div>

<div class="section">
    <div class="section-title">Diagnóstico</div>
    <div class="section-content">' . nl2br($data['diagnostico']) . '</div>
</div>

<div class="section">
    <div class="section-title">Tratamiento y Recomendaciones</div>
    <div class="section-content">' . nl2br($data['observaciones']) . '</div>
</div>';

if (!empty($recetas)) {
    $html .= '
    <div class="section">
        
        <table class="medicines-table">
            <thead>
                <tr>
                    <th width="30%">Medicamento</th>
                    <th width="20%">Dosis</th>
                    <th width="25%">Frecuencia</th>
                    <th width="25%">Duración</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($recetas as $r) {
        $html .= '
                <tr>
                    <td>' . $r['medicamento'] . '</td>
                    <td>' . $r['dosis'] . '</td>
                    <td>' . $r['frecuencia'] . '</td>
                    <td>' . $r['duracion'] . '</td>
                </tr>';
    }
    $html .= '</tbody></table></div>';
} else {
    $html .= '<div class="no-medicines">No se registraron medicamentos en esta consulta.</div>';
}

$html .= '
<div class="signature-area">
    <div class="signature-line"></div>
    <div class="signature-text">Dr(a). ' . $data['medico_nombre'] . '<br>' . ($data['especialidad'] ?? 'Médico General') . '</div>
</div>

<div class="footer">
    Este documento es válido con firma autógrafa o digital del profesional. <br>
    Clínica Médica Especializada © ' . date('Y') . '
</div>';

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 20,
    'margin_bottom' => 20,
    'margin_header' => 10,
    'margin_footer' => 10
]);

$mpdf->SetTitle('Reporte de Consulta - ' . $data['paciente_nombre'] . ' ' . $data['paciente_apellido']);
$mpdf->SetAuthor('Clínica Médica Especializada');
$mpdf->SetCreator('Sistema de Gestión Médica');
$mpdf->SetDisplayMode('fullpage');

$mpdf->WriteHTML($html);
$mpdf->Output("consulta_medica_" . $id_consulta . ".pdf", "I");