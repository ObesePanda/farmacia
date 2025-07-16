<?php
session_start();
include "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "clientes";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}

if (!empty($_POST)) {
    $alert = "";
    if (empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
        $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Error!', 'texto' => 'Todos los campos son obligatorios'];

    } else {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $result = 0;
        if (empty($id)) {
            $query = mysqli_query($conexion, "SELECT * FROM cliente WHERE nombre = '$nombre'");
            $result = mysqli_fetch_array($query);
            if ($result > 0) {
                $_SESSION['mensaje'] = ['tipo' => 'warning', 'titulo' => 'Alerta', 'texto' => 'El cliente ya existe'];

            } else {
                $query_insert = mysqli_query($conexion, "INSERT INTO cliente(nombre,telefono,direccion) values ('$nombre', '$telefono', '$direccion')");
                if ($query_insert) {
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Registro!', 'texto' => 'El cliente fue registrado exitosamente'];

                } else {
                    $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error!', 'texto' => 'Error al registrar'];
                }
            }
        } else {
            $sql_update = mysqli_query($conexion, "UPDATE cliente SET nombre = '$nombre' , telefono = '$telefono', direccion = '$direccion' WHERE idcliente = $id");
            if ($sql_update) {
                $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Exito', 'texto' => 'El cliente fue modificado'];
            } else {
                $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error!', 'texto' => 'Error al modificar'];
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nombre" class="text-dark font-weight-bold">😷 Nombre</label>
                                <input type="text" placeholder="Ingrese Nombre" name="nombre" id="nombre"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="telefono" class="text-dark font-weight-bold">Teléfono</label>
                                <input type="number" placeholder="Ingrese Teléfono" name="telefono" id="telefono"
                                    class="form-control">
                                <input type="hidden" name="id" id="id">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="direccion" class="text-dark font-weight-bold">Dirección</label>
                                <input type="text" placeholder="Ingrese Direccion" name="direccion" id="direccion"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo"
                                onclick="limpiar()">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tbl">
                        <thead class="thead-dark">
                            <tr>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    #</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Nombre</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Teléfono</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Dirección</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "../conexion.php";

                            $query = mysqli_query($conexion, "SELECT * FROM cliente");
                            $result = mysqli_num_rows($query);
                            if ($result > 0) {
                                while ($data = mysqli_fetch_assoc($query)) { ?>
                                    <tr>
                                        <td class="align-middle text-center">
                                            <p class="text-xs text-secondary mb-0"><?php echo $data['idcliente']; ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0"><?php echo $data['nombre']; ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0"><?php echo $data['telefono']; ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-xs font-weight-bold mb-0"><?php echo $data['direccion']; ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="#" onclick="editarCliente(<?php echo $data['idcliente']; ?>)"
                                                class="btn btn-primary"><i class="bi bi-pencil-square"></i></a>
                                            <form action="eliminar_cliente.php?id=<?php echo $data['idcliente']; ?>"
                                                method="post" class="confirmar d-inline">
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