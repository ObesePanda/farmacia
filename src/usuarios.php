<?php
session_start();
$permiso = 'usuarios';
$id_user = $_SESSION['idUser'];
include "../conexion.php";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}
if (!empty($_POST)) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $email = $_POST['correo'];
    $user = $_POST['usuario'];
    $alert = "";
    if (empty($nombre) || empty($email) || empty($user)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Todos los campos son obligatorios'];
    } else {
        if (empty($id)) {
            $clave = $_POST['clave'];
            if (empty($clave)) {
                 $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Atención! Contraseña requerida'];
            } else {
                $clave = md5($_POST['clave']);
                $query = mysqli_query($conexion, "SELECT * FROM usuario where correo = '$email'");
                $result = mysqli_fetch_array($query);
                if ($result > 0) {
                     $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Atención! El correo se ya se encuentra registrado'];
                } else {
                    $query_insert = mysqli_query($conexion, "INSERT INTO usuario(nombre,correo,usuario,clave) values ('$nombre', '$email', '$user', '$clave')");
                    if ($query_insert) {
                        $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'texto' => 'Usuario registrado correctamente.'];
                    } else {
                         $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Error en el registro del usuario.'];
                    }
                }
            }
        } else {
            $sql_update = mysqli_query($conexion, "UPDATE usuario SET nombre = '$nombre', correo = '$email' , usuario = '$user' WHERE idusuario = $id");
            if ($sql_update) {
                 $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'texto' => 'Usuario modificado correctamente.'];
            } else {
                 $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Error al modificar el usuario.'];
            }
        }
    }
}
include "includes/header.php";
?>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6> 🧑‍⚕️ Control de Usuarios</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-25">
                    <table class="table align-items-center mb-0">
                        <form action="" method="post" autocomplete="off" id="formulario">
                            <?php echo isset($alert) ? $alert : ''; ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                       <i class="bi bi-activity"></i><label for="nombre">Nombre</label>
                                        <input type="text" class="form-control" placeholder="Ingrese Nombre"
                                            name="nombre" id="nombre">
                                        <input type="hidden" id="id" name="id">
                                    </div>

                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <i class="bi bi-envelope-fill"></i><label for="correo">Correo</label>
                                        <input type="email" class="form-control"
                                            placeholder="Ingrese Correo Electrónico" name="correo" id="correo">
                                    </div>

                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <i class="bi bi-person-circle"></i><label for="usuario">Usuario</label>
                                        <input type="text" class="form-control" placeholder="Ingrese Usuario"
                                            name="usuario" id="usuario">
                                    </div>

                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                       <i class="bi bi-lock-fill"></i><label for="clave">Contraseña</label>
                                        <input type="password" class="form-control" placeholder="Ingrese Contraseña"
                                            name="clave" id="clave">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                                <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo"
                                    onclick="limpiar()">
                            </div>
                        </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                Nombre</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                Correo</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                Usuario</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($conexion, "SELECT * FROM usuario");
                        $result = mysqli_num_rows($query);
                        if ($result > 0) {
                            while ($data = mysqli_fetch_assoc($query)) { ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <p class="text-xs text-secondary mb-0"><?php echo $data['idusuario']; ?></p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <p class="text-xs font-weight-bold mb-0"><?php echo $data['nombre']; ?></p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <p class="text-secondary text-xs font-weight-bold"><?php echo $data['correo']; ?></p>
                                    </td>
                                    <td class=" align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-success"><?php echo $data['usuario']; ?></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="rol.php?id=<?php echo $data['idusuario']; ?>" class="btn btn-warning"><i class="bi bi-key"></i></a>
                                        <a href="#" onclick="editarUsuario(<?php echo $data['idusuario']; ?>)"
                                            class="btn btn-success"><i class="bi bi-pencil-square"></i></a>
                                        <form action="eliminar_usuario.php?id=<?php echo $data['idusuario']; ?>" method="post"
                                            class="confirmar d-inline">
                                            <button class="btn btn-danger" type="submit"><i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php include_once "includes/footer.php"; ?>