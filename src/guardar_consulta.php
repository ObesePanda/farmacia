<?php
session_start();
require_once "../conexion.php";

// Validar permisos (opcional)
$id_user = $_SESSION['idUser'] ?? null;
$permiso = "consultas";

$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p 
    INNER JOIN detalle_permisos d ON p.id = d.id_permiso 
    WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit;
}

// Validar POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitizar datos
    $id_paciente = mysqli_real_escape_string($conexion, $_POST['id_paciente']);
    $id_medico = mysqli_real_escape_string($conexion, $_POST['id_medico']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha_consulta']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);
    $motivo = mysqli_real_escape_string($conexion, $_POST['motivo']);
    $sintomas = mysqli_real_escape_string($conexion, $_POST['sintomas']);
    $diagnostico = mysqli_real_escape_string($conexion, $_POST['diagnostico'] ?? '');
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones'] ?? '');
    $productos_json = $_POST['productos_json'] ?? '';

    // Registrar la consulta
    $query = mysqli_query($conexion, "INSERT INTO consultas (id_paciente, id_medico, fecha_consulta, hora, motivo, sintomas, diagnostico, observaciones)
        VALUES ('$id_paciente', '$id_medico', '$fecha', '$hora', '$motivo', '$sintomas', '$diagnostico','$observaciones')
    ");

    if ($query) {
        $id_consulta = mysqli_insert_id($conexion);

        // Si vienen productos en la receta
        if (!empty($productos_json)) {
            $productos = json_decode($productos_json, true);

            foreach ($productos as $prod) {
                
                $medicamento = mysqli_real_escape_string($conexion, $prod['nombre']);
                $dosis = mysqli_real_escape_string($conexion, $prod['dosis']);
                $frecuencia = mysqli_real_escape_string($conexion, $prod['frecuencia']);
                $duracion = mysqli_real_escape_string($conexion, $prod['duracion']);

                mysqli_query($conexion, "INSERT INTO recetas (id_consulta, medicamento, dosis, frecuencia, duracion)
                VALUES ('$id_consulta', '$medicamento', '$dosis', '$frecuencia', '$duracion')

        ");
            }
        }

        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Consulta guardada',
            'texto' => 'La consulta médica se registró correctamente.',
            'id_consulta' => $id_consulta
        ];

        header("Location: registrar_consulta.php");
        
        exit;

    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'texto' => 'Error al registrar la consulta.'
        ];
        header("Location: registrar_consulta.php");
        
        exit;
    }
} else {
    header("Location: registrar_consulta.php");
    exit;
}


