<?php
session_start();
include "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "productos";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");

$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}
if (!empty($_POST)) {
    $alert = "";
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $codigo = trim($_POST['codigo']);
    $producto = $_POST['producto'];
    $precio = $_POST['precio'];
    $costo = $_POST['costo'];
    $utilidad = $_POST['utilidad'];
    $tipo = $_POST['tipo'];
    $presentacion = $_POST['presentacion'];
    $laboratorio = $_POST['laboratorio'];
    $vencimiento = '';
    if (!empty($_POST['accion'])) {
        $vencimiento = $_POST['vencimiento'];
    }
    if (empty($codigo) || empty($producto) || empty($tipo) || empty($presentacion) || empty($laboratorio) || empty($precio) || $precio < 0 || $costo < 0) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Todos los campos son obligatorios'];
    } else {
        if (empty($id)) {
            $query = mysqli_query($conexion, "SELECT * FROM producto WHERE codigo = '$codigo'");
            if (!$query) {
                die("Error en la consulta: " . mysqli_error($conexion));
            }
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                $_SESSION['mensaje'] = ['tipo' => 'danger', 'titulo' => 'Error', 'texto' => 'El codigo ya se encuentra registrado '];
            } else {
                $nombreImagen = null;

                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $directorio = "img/productos/";
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0755, true);
                    }

                    $nombreArchivo = basename($_FILES["imagen"]["name"]);
                    $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
                    $nuevoNombre = uniqid() . '.' . $extension;
                    $rutaFinal = $directorio . $nuevoNombre;

                    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaFinal)) {
                        $nombreImagen = $nuevoNombre;
                    }
                }
                $query_insert = mysqli_query($conexion, "INSERT INTO producto(codigo, descripcion, precio, id_lab, id_presentacion, id_tipo, imagen, costo, utilidad)
                VALUES ('$codigo', '$producto', '$precio', $laboratorio, $presentacion, $tipo, '$nombreImagen' , '$costo' , '$utilidad')");
                if ($query_insert) {
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'texto' => 'El producto fue creado exitosamente'];
                } else {
                    $_SESSION['mensaje'] = ['tipo' => 'danger', 'titulo' => 'Error', 'texto' => 'Error al registrar el producto'];
                }
            }
        } else {

            $query = mysqli_query($conexion, "SELECT * FROM producto WHERE codigo = '$codigo' AND codproducto != $id");
            if (!$query) {
                die("Error en la consulta: " . mysqli_error($conexion));
            }
            $result = mysqli_num_rows($query);

            if ($result > 0) {
                $_SESSION['mensaje'] = ['tipo' => 'danger', 'titulo' => 'Error', 'texto' => 'El código ya está en uso por otro producto'];
            } else {
                $nombreImagen = null;

                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $directorio = "img/productos/";
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0755, true);
                    }

                    $nombreArchivo = basename($_FILES["imagen"]["name"]);
                    $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
                    $nuevoNombre = uniqid() . '.' . $extension;
                    $rutaFinal = $directorio . $nuevoNombre;

                    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaFinal)) {
                        $nombreImagen = $nuevoNombre;
                    }
                }

                $sql_img = $nombreImagen !== null ? ", imagen = '$nombreImagen'" : "";

                $query_update = mysqli_query($conexion, "UPDATE producto SET codigo = '$codigo', descripcion = '$producto', precio= $precio, id_lab = '$laboratorio', id_presentacion = '$presentacion', id_tipo = '$tipo', costo = '$costo', utilidad = '$utilidad' $sql_img WHERE codproducto = $id");

                if ($query_update) {
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'texto' => 'El producto fue modificado exitosamente'];
                } else {
                    $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'El producto no pudo ser modificado'];
                }
            }
        }

    }
}

//*VISTA ///
include_once "includes/header.php";
?>
<div class="card shadow-lg">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white">💊 Control de Productos</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post" enctype="multipart/form-data" autocomplete="off" id="formulario">
                            <?php echo isset($alert) ? $alert : ''; ?>
                            <div class="row g-3">
                                <!-- Columna Izquierda -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="codigo" class="text-dark font-weight-bold">
                                            <i class="bi bi-upc"></i> Código de Barras
                                        </label>
                                        <input type="text" placeholder="Ingrese código de barras" name="codigo"
                                            id="codigo" class="form-control">
                                        <input type="hidden" id="id" name="id">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="producto" class="text-dark font-weight-bold">
                                            Producto
                                        </label>
                                        <input type="text" placeholder="Ingrese nombre del producto" name="producto"
                                            id="producto" class="form-control">
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="precio" class="text-dark font-weight-bold">Precio de
                                                Venta</label>
                                            <input type="number" step="0.01" placeholder="Ingrese precio"
                                                class="form-control" name="precio" id="precio">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="costo" class="text-dark font-weight-bold">Costo</label>
                                            <input type="number" step="0.01" placeholder="Ingrese costo"
                                                class="form-control" name="costo" id="costo">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="utilidad" class="text-dark font-weight-bold">Utilidad
                                                (%)</label>
                                            <input type="number" step="0.01" placeholder="Utilidad" class="form-control"
                                                name="utilidad" id="utilidad">
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna Derecha -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tipo" class="text-dark font-weight-bold">Tipo</label>
                                        <select id="tipo" class="form-control" name="tipo" required>
                                            <?php
                                            $query_tipo = mysqli_query($conexion, "SELECT * FROM tipos");
                                            while ($datos = mysqli_fetch_assoc($query_tipo)) { ?>
                                                <option value="<?php echo $datos['id'] ?>"><?php echo $datos['tipo'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="presentacion"
                                            class="text-dark font-weight-bold">Presentación</label>
                                        <select id="presentacion" class="form-control" name="presentacion" required>
                                            <?php
                                            $query_pre = mysqli_query($conexion, "SELECT * FROM presentacion");
                                            while ($datos = mysqli_fetch_assoc($query_pre)) { ?>
                                                <option value="<?php echo $datos['id'] ?>"><?php echo $datos['nombre'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="laboratorio" class="text-dark font-weight-bold">Laboratorio</label>
                                        <select id="laboratorio" class="form-control" name="laboratorio" required>
                                            <?php
                                            $query_lab = mysqli_query($conexion, "SELECT * FROM laboratorios");
                                            while ($datos = mysqli_fetch_assoc($query_lab)) { ?>
                                                <option value="<?php echo $datos['id'] ?>">
                                                    <?php echo $datos['laboratorio'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="imagen" class="text-dark font-weight-bold">Imagen del
                                            producto</label>
                                        <input type="file" name="imagen" id="imagen" class="form-control"
                                            accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <button type="submit" class="btn btn-primary" id="btnAccion">
                                    <i class="bi bi-save me-2"></i> Registrar
                                </button>
                                <button type="button" onclick="limpiar()" class="btn btn-success" id="btnNuevo">
                                    Limpiar
                                </button>
                                <a href="reporte_inventario.php" target="_blank" class="btn btn-warning mb-3">
                                    <i class="bi bi-file-earmark-pdf"></i> Reporte Inventario PDF
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tbl">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Imagen</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Presentación</th>
                            <th>Precio</th>
                            <th>Inventario</th>
                            <th>Vencimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include "../conexion.php";

                        $query = mysqli_query($conexion, "
                            SELECT p.*, t.id, t.tipo, pr.id, pr.nombre 
                            FROM producto p 
                            INNER JOIN tipos t ON p.id_tipo = t.id 
                            INNER JOIN presentacion pr ON p.id_presentacion = pr.id
                        ");
                        $result = mysqli_num_rows($query);

                        if ($result > 0) {
                            while ($data = mysqli_fetch_assoc($query)) {


                                $consultaLotes = mysqli_query($conexion, "
                                    SELECT SUM(cantidad) AS total 
                                    FROM lotes 
                                    WHERE id_producto = {$data['codproducto']}
                                    AND estado = 'activo'
                                ");
                                $totalLotes = mysqli_fetch_assoc($consultaLotes);
                                $existencia = $totalLotes['total'] ?? 0;


                                $queryLoteVencimiento = mysqli_query($conexion, "
                                    SELECT fecha_vencimiento 
                                    FROM lotes 
                                    WHERE id_producto = {$data['codproducto']} 
                                    AND fecha_vencimiento IS NOT NULL
                                ");

                                $vencidoLote = false;
                                $vencimientoLote = null;

                                while ($rowLote = mysqli_fetch_assoc($queryLoteVencimiento)) {
                                    $fechaVencimiento = trim($rowLote['fecha_vencimiento']);
                                    if (!empty($fechaVencimiento)) {
                                        if (is_null($vencimientoLote) || $fechaVencimiento < $vencimientoLote) {
                                            $vencimientoLote = $fechaVencimiento;
                                        }
                                    }
                                }

                                if (!empty($vencimientoLote)) {
                                    if (strtotime($vencimientoLote) < strtotime(date('Y-m-d'))) {
                                        $vencidoLote = true;
                                    }
                                }
                                ?>
                                <tr class="<?= $vencidoLote ? 'table-danger' : ''; ?>">
                                    <td class="text-center fw-bold"><?= $data['codproducto']; ?></td>
                                    <td class="text-center">
                                        <?php if ($data['imagen']) { ?>
                                            <img src="img/productos/<?= $data['imagen']; ?>" alt="Producto"
                                                class="rounded img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php } else { ?>
                                            <span class="badge bg-secondary">Sin imagen</span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-xs font-weight-bold mb-0"><?= $data['codigo']; ?></td>
                                    <td class="text-xs font-weight-bold mb-0"><?= $data['descripcion']; ?></td>
                                    <td class="text-center"><span class="badge bg-info"><?= $data['tipo']; ?></span></td>
                                    <td class="text-center"><span class="badge bg-success"><?= $data['nombre']; ?></span></td>
                                    <td class="text-center text-primary fw-bold">$<?= number_format($data['precio'], 2); ?></td>
                                    <td class="text-center">
                                        <?= $existencia; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($vencimientoLote)): ?>
                                            <span class="<?= $vencidoLote ? 'text-danger fw-bold' : 'text-success'; ?>">
                                                <?= date('d/m/Y', strtotime($vencimientoLote)); ?>
                                                <?php if ($vencidoLote): ?>
                                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-1"
                                                        title="Producto vencido"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin fecha</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="#" onclick="editarProducto(<?= $data['codproducto']; ?>)"
                                                class="btn btn-primary btn-sm" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="eliminar_producto.php?id=<?= $data['codproducto']; ?>" method="post"
                                                class="confirmar d-inline">
                                                <button class="btn btn-danger btn-sm" type="submit" title="Eliminar">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                            <a href="#" class="btn btn-sm btn-secondary"
                                                onclick="verLotes(<?= $data['codproducto']; ?>)">
                                                <i class="bi bi-box-seam"></i> Lotes
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>


<!-- Modal Lotes -->
<div class="modal fade" id="modalLotes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Lotes del Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Lote</th>
                            <th>Fecha Vencimiento</th>
                            <th>Cantidad</th>
                            <th>Costo Unitario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="lotesBody">
                        <!-- Aquí se llenan dinámicamente los lotes -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>