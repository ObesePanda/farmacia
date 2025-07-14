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

$query = mysqli_query($conexion, "SELECT c.*, p.id AS paciente_id, CONCAT(p.nombre, ' ', p.apellido) AS nombre_paciente, m.nombre
    FROM consultas c
    INNER JOIN pacientes p ON c.id_paciente = p.id
    INNER JOIN medicos m ON c.id_paciente = p.id");

include_once "includes/header.php";
?>

<div class="card shadow-sm border-0">
   
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0" style="color: #fff; font-weight: 700;" ><i class="bi bi-book"></i> Historial de Consultas</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle" id="tbl">
                <thead class="thead-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Paciente</th>
                        <th>Medico</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td class="text-left"><?php echo htmlspecialchars($row['nombre_paciente']); ?></td>
                             <td class="text-left"><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_consulta'])); ?></td>
                            <td>
                                <a href="pdf_receta.php?id=<?php echo $row['id'] ?>&v=<?php echo $row['id'] ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-danger" 
                                   title="Ver Ticket PDF">
                                    <i class="bi bi-filetype-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
