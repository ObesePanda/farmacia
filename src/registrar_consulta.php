<?php
session_start();
require_once "../conexion.php";

$id_user = $_SESSION['idUser'];
$permiso = "consultas";

$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p 
    INNER JOIN detalle_permisos d ON p.id = d.id_permiso 
    WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit;
}

// Traer pacientes
$pacientes = [];
$q_pac = mysqli_query($conexion, "SELECT * FROM pacientes ORDER BY nombre");
while ($row = mysqli_fetch_assoc($q_pac)) {
    $pacientes[] = $row;
}

// Traer médicos
$medicos = [];
$q_med = mysqli_query($conexion, "SELECT * FROM medicos ORDER BY nombre");
while ($row = mysqli_fetch_assoc($q_med)) {
    $medicos[] = $row;
}


$productos = [];
$q_prod = mysqli_query($conexion, "SELECT codproducto, descripcion, existencia, precio FROM producto ORDER BY descripcion");
while ($row = mysqli_fetch_assoc($q_prod)) {
    $productos[] = $row;
}
include_once "includes/header.php";
?>



<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark ">
            <h5 class="mb-0 text-white">Registrar Consulta Médica</h5>
        </div>
        <div class="card-body">
            <form action="guardar_consulta.php" method="POST">
                <div class="row g-3">
                    <!-- Paciente -->
                    <div class="col-md-6">
                        <label class="form-label">Paciente</label>
                        <div class="input-group">
                            <select name="id_paciente" id="id_paciente" class="form-select" required>
                                <option value="">Seleccione un paciente</option>
                                <?php foreach ($pacientes as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= $p['nombre'] ?>     <?= $p['apellido'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoPaciente">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Médico -->
                    <div class="col-md-6">
                        <label class="form-label">Médico</label>
                        <select name="id_medico" class="form-select" required>
                            <option value="">Seleccione un médico</option>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    Dr(a). <?= $m['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fecha de consulta -->
                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha_consulta" class="form-control" value="<?= date('Y-m-d') ?>"
                            required>
                    </div>

                    <!-- Hora -->
                    <div class="col-md-4">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora" class="form-control" required>
                    </div>

                    <!-- Motivo -->
                    <div class="col-md-12">
                        <label class="form-label">Motivo de la consulta</label>
                        <textarea name="motivo" class="form-control" rows="3" required></textarea>
                    </div>

                    <!-- Sintomas -->
                    <div class="col-md-12">
                        <label class="form-label">Sintomas</label>
                        <textarea name="sintomas" class="form-control" rows="3"></textarea>
                    </div>

                    <!-- Diagnóstico -->
                    <div class="col-md-12">
                        <label class="form-label">Diagnóstico</label>
                        <textarea name="diagnostico" class="form-control" rows="3"></textarea>
                    </div>
                    <!-- Observacion -->
                    <div class="col-md-12">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"></textarea>
                    </div>

                    <!-- Tratamiento / Receta -->
                    <div class="col-md-12 mt-4">
                        <h5>Receta Médica</h5>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Producto</label>
                                <select id="producto" class="form-select" aria-label="Seleccionar producto">
                                    <option></option>
                                    <?php foreach ($productos as $prod): ?>
                                        <option value="<?= $prod['codproducto'] ?>" data-stock="<?= $prod['existencia'] ?>"
                                            data-precio="<?= $prod['precio'] ?>">
                                            <?= $prod['descripcion'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Dosis</label>
                                <input type="text" id="dosis" class="form-control" placeholder="Ej.: 1 cada 8 hrs">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Frecuencia</label>
                                <input type="text" id="frecuencia" class="form-control" min="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Duracion</label>
                                <input type="text" id="duracion" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-success" id="btnAgregarProducto">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="tablaProductos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Dosis</th>
                                        <th>Frecuencia</th>
                                        <th>Duracion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>


                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Guardar Consulta</button>
                        <input type="hidden" name="productos_json" id="productos_json">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modalNuevoPaciente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nuevo Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoPaciente">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre_paciente" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre_paciente" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido_paciente" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido_paciente" name="apellido" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_nac_paciente" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nac_paciente" name="fecha_nacimiento"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="genero_paciente" class="form-label">Género</label>
                                <select name="genero" id="genero_paciente" class="form-select" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono_paciente" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_paciente" name="telefono">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="direccion_paciente" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion_paciente" name="direccion">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-select {
        display: block;
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.4rem;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #d2d6da;
        appearance: none;
        border-radius: 0.5rem;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
</style>

<?php include_once "includes/footer.php"; ?>
<script src="../assets/js/funciones_consulta.js"></script>