<?php
session_start();
include "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "tipos";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}

if (!empty($_POST)) {
    $alert = "";
    if (empty($_POST['nombre'])) {
          $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Todos los campos son obligatorios'];
    } else {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $result = 0;
        if (empty($id)) {
            $query = mysqli_query($conexion, "SELECT * FROM tipos WHERE tipo = '$nombre'");
            $result = mysqli_fetch_array($query);
            if ($result > 0) {
                 $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'El tipo de medicamento ya existe.'];
            } else {
                $query_insert = mysqli_query($conexion, "INSERT INTO tipos(tipo) values ('$nombre')");
                if ($query_insert) {
                      $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Exito', 'texto' => 'Tipo de medicamento registrado correctamente'];
                } else {
                      $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Error al crear el tipo de medicamento'];
                }
            }
        } else {
            $sql_update = mysqli_query($conexion, "UPDATE tipos SET tipo = '$nombre' WHERE id = $id");
            if ($sql_update) {
                 $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Exito', 'texto' => 'Tipo de medicamento modificado correctamente'];
            } else {
               $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Error al modificar el tipo de medicamento'];
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
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="nombre" class="text-dark font-weight-bold">💊 Nombre</label>
                                <input type="text" placeholder="Ingrese Nombre" name="nombre" id="nombre" class="form-control">
                                <input type="hidden" name="id" id="id">
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                            <input type="button" value="Limpiar" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tbl">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "../conexion.php";

                            $query = mysqli_query($conexion, "SELECT * FROM tipos");
                            $result = mysqli_num_rows($query);
                            if ($result > 0) {
                                while ($data = mysqli_fetch_assoc($query)) { ?>
                                    <tr>
                                        <td class="align-middle text-center" ><p class="text-xs text-secondary mb-0"><?php echo $data['id']; ?></p></td>
                                        <td class="align-middle text-center" ><p class="text-xs text-secondary mb-0"><?php echo $data['tipo']; ?></p></td>
                                        <td class="align-middle text-center" style="width: 200px;">
                                            <a href="#" onclick="editarTipo(<?php echo $data['id']; ?>)" class="btn btn-primary"><i class="bi bi-pencil-square"></i></a>
                                            <form action="eliminar_tipo.php?id=<?php echo $data['id']; ?>" method="post" class="confirmar d-inline">
                                                <button class="btn btn-danger" type="submit"><i class="bi bi-trash3"></i> </button>
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