<?php
session_start();
require_once "../conexion.php";

// Validar permiso
$id_user = $_SESSION['idUser'] ?? null;
$permiso = "pacientes";

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
    header("Location: pacientes.php");
    exit;
}

$idPaciente = intval($_GET['id']);

// Buscar datos del paciente
$queryPaciente = mysqli_query($conexion, "SELECT * FROM pacientes WHERE id = $idPaciente");
$dataPaciente = mysqli_fetch_assoc($queryPaciente);

if (!$dataPaciente) {
    $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Oops!', 'texto' => 'El paciente no existe.'];
    header("Location: pacientes.php");
    exit;
}

if (!empty($_POST)) {
    if (empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['fecha_nacimiento'])) {
        $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Error!', 'texto' => 'Todos los campos obligatorios.'];
    } else {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
        $fecha_nac = mysqli_real_escape_string($conexion, $_POST['fecha_nacimiento']);
        $genero = mysqli_real_escape_string($conexion, $_POST['genero']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
        $correo = mysqli_real_escape_string($conexion, $_POST['email']);

        $update = mysqli_query($conexion, "UPDATE pacientes SET 
            nombre = '$nombre',
            apellido = '$apellido',
            fecha_nacimiento = '$fecha_nac',
            sexo = '$genero',
            telefono = '$telefono',
            direccion = '$direccion',
            correo = '$correo'
            WHERE id = $idPaciente");

        if ($update) {
            
            $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Actualizado!', 'texto' => 'Paciente actualizado correctamente.'];
            header("Location: pacientes.php");
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
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= $dataPaciente['nombre'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required value="<?= $dataPaciente['apellido'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= $dataPaciente['fecha_nacimiento'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Género</label>
                        <select name="genero" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <option value="Masculino" <?= $dataPaciente['sexo'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                            <option value="Femenino" <?= $dataPaciente['sexo'] == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                            <option value="Otro" <?= $dataPaciente['sexo'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= $dataPaciente['telefono'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?= $dataPaciente['direccion'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo</label>
                        <input type="email" name="email" class="form-control" value="<?= $dataPaciente['correo'] ?>">
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button class="btn btn-primary">Actualizar Paciente</button>
                    <a href="pacientes.php" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
