<?php
session_start();
require_once "../conexion.php";

$id_user = $_SESSION['idUser'];
$permiso = "compras";

$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p 
    INNER JOIN detalle_permisos d ON p.id = d.id_permiso 
    WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit;
}

$query = mysqli_query($conexion, "SELECT c.*, l.id AS id_proveedor, l.laboratorio
    FROM compras c
    INNER JOIN laboratorios l ON c.id_proveedor = l.id");

include_once "includes/header.php";
?>

<div class="card shadow-sm border-0">
   
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0" style="color: #fff; font-weight: 700;" ><i class="bi bi-book"></i> Historial de Ventas</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle" id="tbl">
                <thead class="thead-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Laboratorio</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td class="text-left"><?php echo htmlspecialchars($row['laboratorio']); ?></td>
                            <td><span >$<?php echo number_format($row['total'], 2); ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                            <td>
                                <a href="generar_pdf_compra.php?id=<?php echo $row['id'] ?>&v=<?php echo $row['id'] ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-danger" 
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
