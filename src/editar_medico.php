<?php
session_start();
require_once "../conexion.php";

// Validar permiso
$id_user = $_SESSION['idUser'] ?? null;
$permiso = "medicos";

$sql = mysqli_query($conexion, "SELECT p.*, d.* 
    FROM permisos p 
    INNER JOIN detalle_permisos d ON p.id = d.id_permiso 
    WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit;
}

// Validar ID recibido
if (empty($_GET['id'])) {
    header("Location: medicos.php");
    exit;
}

$idMedico = intval($_GET['id']);

// Buscar datos del paciente
$queryMedico = mysqli_query($conexion, "SELECT * FROM medicos WHERE id = $idMedico");
$dataMedico = mysqli_fetch_assoc($queryMedico);

if (!$dataMedico) {
    $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Oops!', 'texto' => 'El paciente no existe.'];
    header("Location: medicos.php");
    exit;
}

if (!empty($_POST)) {
    if (empty($_POST['nombre']) || empty($_POST['cedula_profesional']) || empty($_POST['cedula_profesional'])) {
        $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Error!', 'texto' => 'Todos los campos obligatorios.'];
    } else {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $cedula_profesional = mysqli_real_escape_string($conexion, $_POST['cedula_profesional']);
        $especialidad = mysqli_real_escape_string($conexion, $_POST['especialidad']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['email']);
        $firma = mysqli_real_escape_string($conexion, $_POST['firma']);

        $update = mysqli_query($conexion, "UPDATE medicos SET 
            nombre = '$nombre',
            cedula_profesional = '$cedula_profesional',
            especialidad = '$especialidad',
            telefono = '$telefono',
            correo = '$correo',
            firma = '$firma'
            WHERE id = $idMedico");

        if ($update) {
            
            $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Actualizado!', 'texto' => 'Paciente actualizado correctamente.'];
            header("Location: medicos.php");
            exit;
        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error!', 'texto' => 'Error al actualizar paciente.'];
        }
    }
}

include_once "includes/header.php";
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-white text-dark">
            <h5 class="mb-0">Editar Paciente</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre de Medico</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= $dataMedico['nombre'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cedula Profesional</label>
                        <input type="text" name="cedula_profesional" class="form-control" required value="<?= $dataMedico['cedula_profesional'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Especialidad</label>
                        <input type="text" name="especialidad" class="form-control" required value="<?= $dataMedico['especialidad'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= $dataMedico['telefono'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Firma</label>
                        <input type="text" name="firma" class="form-control" value="<?= $dataMedico['firma'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo</label>
                        <input type="email" name="email" class="form-control" value="<?= $dataMedico['correo'] ?>">
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button class="btn btn-primary">Actualizar Medico</button>
                    <a href="pacientes.php" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
