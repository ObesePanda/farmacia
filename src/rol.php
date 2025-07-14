<?php
session_start();
require_once "../conexion.php";
$id = $_GET['id'];
$sqlpermisos = mysqli_query($conexion, "SELECT * FROM permisos");
$usuarios = mysqli_query($conexion, "SELECT * FROM usuario WHERE idusuario = $id");
$resultUsuario = mysqli_num_rows($usuarios);

if (empty($resultUsuario)) {
    header("Location: usuarios.php");
}

if (isset($_POST['permisos'])) {
    $id_user = $_GET['id'];
    $permisos = $_POST['permisos'];
    mysqli_query($conexion, "DELETE FROM detalle_permisos WHERE id_usuario = $id_user");
    if (!empty($permisos)) {
        foreach ($permisos as $permiso) {
            mysqli_query($conexion, "INSERT INTO detalle_permisos(id_usuario, id_permiso) VALUES ($id_user,$permiso)");
        }
    }
    $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'texto' => 'Permisos modificados correctamente.'];
}

$consulta = mysqli_query($conexion, "SELECT * FROM detalle_permisos WHERE id_usuario = $id");
$datos = array();
foreach ($consulta as $asignado) {
    $datos[$asignado['id_permiso']] = true;
}

include_once "includes/header.php";
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow border-0">
            <div class="card-header bg-gray-100 text-white text-center">
                <h4 class="mb-0 text-dark">Asignar Permisos al Usuario</h4>
            </div>
            <div class="card-body bg-light">
                <form method="post" action="">
                    <div class="row">
                        <?php while ($row = mysqli_fetch_assoc($sqlpermisos)) { ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="permiso_<?php echo $row['id']; ?>" name="permisos[]" value="<?php echo $row['id']; ?>" <?php if (isset($datos[$row['id']])) echo "checked"; ?>>
                                    <label class="form-check-label text-dark fw-bold text-uppercase" for="permiso_<?php echo $row['id']; ?>">
                                        <?php echo $row['nombre']; ?>
                                    </label>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <button class="btn btn-success w-100 mt-4" type="submit">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>