<?php
session_start();
require_once "../conexion.php";

$id_user = $_SESSION['idUser'];
$permiso = "medicos";

$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p 
    INNER JOIN detalle_permisos d ON p.id = d.id_permiso 
    WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit;
}

if (!empty($_POST)) {
    if (empty($_POST['nombre']) || empty($_POST['cedula_profesional']) || empty($_POST['telefono'])) {
        $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Error!', 'texto' => 'Todos los campos son obligatorios'];
    } else {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $cedula_profesional = mysqli_real_escape_string($conexion, $_POST['cedula_profesional']);
        $especialidad = mysqli_real_escape_string($conexion, $_POST['especialidad']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['email']);
        $firma = mysqli_real_escape_string($conexion, $_POST['firma']);

        $query = mysqli_query($conexion, "INSERT INTO medicos (nombre, cedula_profesional, especialidad, telefono, correo, firma)
            VALUES ('$nombre', '$cedula_profesional', '$especialidad', '$telefono', '$correo', '$firma')");

        if ($query) {
            $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Registro!', 'texto' => 'El medico fue registrado exitosamente'];
            header("Location: medicos.php");
            exit;
        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error!', 'texto' => 'Error al registrar medico'];
        }
    }
}

include_once "includes/header.php";
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form action="" method="post" autocomplete="off" id="formulario">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nombre" class="text-dark font-weight-bold">Nombre de Medico <span
                                        class="text-danger">*</span></label>
                                <input type="text" placeholder="Ingrese Nombre" name="nombre" id="nombre"
                                    class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cedula_profesional" class="text-dark font-weight-bold">Cedula Profesional
                                    <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Ingrese cedula profesional" name="cedula_profesional"
                                    id="cedula_profesional" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="especialidad" class="text-dark font-weight-bold">Especialidad <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="especialidad" id="especialidad" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="telefono" class="text-dark font-weight-bold">Teléfono</label>
                                <input type="tel" placeholder="Ingrese Teléfono" name="telefono" id="telefono"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="firma" class="text-dark font-weight-bold">Firma</label>
                                <input type="tel" placeholder="Ingrese tu firma" name="firma" id="firma"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email" class="text-dark font-weight-bold">Correo Electrónico</label>
                                <input type="email" placeholder="Ingrese Correo" name="email" id="email"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mt-4">
                            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-12 mt-4">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tbl">
                        <thead class="thead-dark">
                            <tr>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    #</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre
                                    Completo</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Especialidad</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Teléfono</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Correo Electrónico</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($conexion, "SELECT * FROM medicos ORDER BY id DESC");
                            $result = mysqli_num_rows($query);

                            if ($result > 0) {
                                while ($data = mysqli_fetch_assoc($query)) { ?>
                                    <tr>
                                        <td class="align-middle text-center">
                                            <p class="text-xs text-secondary mb-0"><?= $data['id'] ?></p>
                                        </td>
                                        <td class="align-middle">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?= $data['nombre'] ?>
                                            </p>
                                            <p class="text-xs text-secondary mb-0"><?= $data['cedula_profesional'] ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?= $data['especialidad'] ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?= $data['telefono'] ?: '<span class="text-muted">N/A</span>' ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?= $data['correo'] ?: '<span class="text-muted">N/A</span>' ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="editar_medico.php?id=<?= $data['id'] ?>" class="btn btn-primary btn-sm"
                                                title="Editar">
                                                <i class="bi bi-pencil-square"> Editar</i>
                                            </a>
                                            <form action="editar_medico.php?id=<?= $data['id'] ?>" method="post"
                                                class="confirmar d-inline">
                                                <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                                    data-id="<?= $data['id'] ?>" title="Eliminar">
                                                    <i class="bi bi-trash"> Eliminar</i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay pacientes registrados</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.btnEliminar').forEach(button => {
            button.addEventListener('click', () => {
                const idMedico = button.getAttribute('data-id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡Esta acción no se puede deshacer!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redireccionar al archivo PHP de eliminación
                        window.location.href = `eliminar_medico.php?id=${idMedico}`;
                    }
                });
            });
        });
    });
</script>


<?php include_once "includes/footer.php"; ?>