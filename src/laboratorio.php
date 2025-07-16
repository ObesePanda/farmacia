<?php
session_start();
include "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "laboratorios";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}

if (!empty($_POST)) {
    $alert = "";
    if (empty($_POST['laboratorio']) || empty($_POST['direccion']) || empty($_POST['telefono'] || empty($_POST['correo']))) {
          $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Atención', 'texto' => 'Todos los campos son obligatorios.'];
    } else {
        $id = $_POST['id'];
        $laboratorio = $_POST['laboratorio'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['correo'];
        $result = 0;
        if (empty($id)) {
            $query = mysqli_query($conexion, "SELECT * FROM laboratorios WHERE laboratorio = '$laboratorio'");
            $result = mysqli_fetch_array($query);
            if ($result > 0) {
                  $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'El proveedor ya se encuentra registrado.'];
            } else {
                $query_insert = mysqli_query($conexion, "INSERT INTO laboratorios(laboratorio, direccion,telefono,correo) values ('$laboratorio', '$direccion','$telefono','$correo')");
                if ($query_insert) {
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Exito', 'texto' => 'El proveedor fue creado exitosamente.'];
                } else {
                     $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Error en el registro del proveedor.'];
                }
            }
        } else {
            $sql_update = mysqli_query($conexion, "UPDATE laboratorios SET laboratorio = '$laboratorio', direccion = '$direccion', telefono = '$telefono' , correo = '$correo' WHERE id = $id");
            if ($sql_update) {
                $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Exito', 'texto' => 'El proveedor fue modificado exitosamente.'];
            } else {
                $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'El proveedor no pudo ser modificado.'];
            }
        }
    }
    mysqli_close($conexion);
}
include_once "includes/header.php";
?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <?php echo (isset($alert)) ? $alert : ''; ?>
                <form action="" method="post" autocomplete="off" id="formulario">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="laboratorio" class="text-dark font-weight-bold">🏥 Proveedor</label>
                                <input type="text" placeholder="Ingrese el proveedor" name="laboratorio"
                                    id="laboratorio" class="form-control">
                                <input type="hidden" name="id" id="id">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="direccion" class="text-dark font-weight-bold">Dirección</label>
                                <input type="text" placeholder="Ingrese Dirección" name="direccion" id="direccion"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="correo" class="text-dark font-weight-bold">Correo</label>
                                <input type="email" placeholder="Ingrese Correo Electrónico" name="correo" id="correo"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="telefono" class="text-dark font-weight-bold">Telefono</label>
                                <input type="text" placeholder="Ingrese Telefono" name="telefono" id="telefono"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mt-4">
                            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo"
                                onclick="limpiar()">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Proveedor</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Dirección</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Telefono</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Correo</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "../conexion.php";

                            $query = mysqli_query($conexion, "SELECT * FROM laboratorios");
                            $result = mysqli_num_rows($query);
                            if ($result > 0) {
                                while ($data = mysqli_fetch_assoc($query)) { ?>
                                    <tr>
                                        <td class="align-middle text-center">
                                            <p class="text-xs text-secondary mb-0"><?php echo $data['id']; ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?php echo $data['laboratorio']; ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?php echo $data['direccion']; ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?php echo $data['telefono']; ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?php echo $data['correo']; ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center" style="width: 200px;">
                                            <a href="#" onclick="editarLab(<?php echo $data['id']; ?>)"
                                                class="btn btn-primary"><i class="bi bi-pencil-square"></i></a>
                                            <form action="eliminar_lab.php?id=<?php echo $data['id']; ?>" method="post"
                                                class="confirmar d-inline">
                                                <button class="btn btn-danger" type="submit"><i class="bi bi-trash3-fill"></i>
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