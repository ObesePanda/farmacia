<?php
session_start();
require("../conexion.php");
date_default_timezone_set('America/Mexico_City');

include_once "includes/header.php";
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h4 style="color: #fff; font-weight: 700;">Historial de Cajas</h4>
        </div>

        <div class="card-body">
            <?php $where = [];

            if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
                $inicio = $_GET['fecha_inicio'];
                $fin = $_GET['fecha_fin'];
                $where[] = "DATE(c.fecha_apertura) BETWEEN '$inicio' AND '$fin'";
            }

            if (!empty($_GET['estado'])) {
                $estado = $_GET['estado'];
                $where[] = "c.estado = '$estado'";
            }

            if (!empty($_GET['usuario'])) {
                $usuario = intval($_GET['usuario']);
                $where[] = "c.usuario_id = $usuario";
            }

            $filtros = (count($where) > 0) ? 'WHERE ' . implode(' AND ', $where) : ''; ?>
            <form method="GET" class="mb-4">
                <div class="row">
                    <!-- Fecha inicio -->
                    <div class="col-md-3">
                        <label for="fecha_inicio">Desde:</label>
                        <input type="date" class="form-control" name="fecha_inicio"
                            value="<?= $_GET['fecha_inicio'] ?? '' ?>">
                    </div>

                    <!-- Fecha fin -->
                    <div class="col-md-3">
                        <label for="fecha_fin">Hasta:</label>
                        <input type="date" class="form-control" name="fecha_fin"
                            value="<?= $_GET['fecha_fin'] ?? '' ?>">
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label for="estado">Estado:</label>
                        <select class="form-control" name="estado">
                            <option value="">Todos</option>
                            <option value="abierta" <?= (($_GET['estado'] ?? '') == 'abierta') ? 'selected' : '' ?>>Abierta
                            </option>
                            <option value="cerrada" <?= (($_GET['estado'] ?? '') == 'cerrada') ? 'selected' : '' ?>>Cerrada
                            </option>
                        </select>
                    </div>

                    <!-- Usuario -->
                    <div class="col-md-3">
                        <label for="usuario">Usuario:</label>
                        <select class="form-control" name="usuario">
                            <option value="">Todos</option>
                            <?php
                            $usuarios = mysqli_query($conexion, "SELECT idusuario, nombre FROM usuario ORDER BY nombre");
                            while ($u = mysqli_fetch_assoc($usuarios)) {
                                $selected = (($_GET['usuario'] ?? '') == $u['id']) ? 'selected' : '';
                                echo "<option value='{$u['id']}' $selected>{$u['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Botón -->
                    <div class="col-md-1 d-flex align-items-end mt-4 ">
                        <button class="btn btn-primary ">Filtrar</button>
                    </div>
                </div>
            </form>


            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Usuario</th>
                            <th>Fecha Apertura</th>
                            <th>Fecha Cierre</th>
                            <th>Monto Inicial</th>
                            <th>Monto Final</th>
                            <th>Total Ventas</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $sql = mysqli_query($conexion, "SELECT c.*, u.nombre AS usuario 
                            FROM caja c 
                            INNER JOIN usuario u ON u.idusuario = c.usuario_id 
                            $filtros 
                            ORDER BY c.fecha_apertura DESC
                        ");
                        while ($row = mysqli_fetch_assoc($sql)) {
                            $ventas = 0;
                            $diferencia = 0;

                            if ($row['estado'] == 'cerrada' && $row['fecha_cierre']) {
                                $f1 = $row['fecha_apertura'];
                                $f2 = $row['fecha_cierre'];
                                $idUsuario = $row['usuario_id'];

                                $qVentas = mysqli_query($conexion, "SELECT SUM(total) AS total FROM ventas WHERE id_usuario = $idUsuario AND fecha BETWEEN '$f1' AND '$f2'");
                                $rVentas = mysqli_fetch_assoc($qVentas);
                                $ventas = $rVentas['total'] ?? 0;

                                $esperado = $row['monto_inicial'] + $ventas;
                                $diferencia = $row['monto_final'] - $esperado;
                            }
                            ?>
                            <tr>
                                <td><?= $row['usuario'] ?></td>
                                <td><?= $row['fecha_apertura'] ?></td>
                                <td><?= $row['fecha_cierre'] ?: '<em>---</em>' ?></td>
                                <td>$<?= number_format($row['monto_inicial'], 2) ?></td>
                                <td>
                                    <?= is_null($row['monto_final']) ? '<em>---</em>' : '$' . number_format($row['monto_final'], 2) ?>
                                </td>
                                <td>$<?= number_format($ventas, 2) ?></td>
                                <td>
                                    <?php if ($row['estado'] == 'cerrada'): ?>
                                        <?php if ($diferencia < 0): ?>
                                            <span class="text-danger">-$<?= number_format(abs($diferencia), 2) ?></span>
                                        <?php elseif ($diferencia > 0): ?>
                                            <span class="text-success">+$<?= number_format($diferencia, 2) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted"> $0.00</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <em>---</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['estado'] == 'abierta'): ?>
                                        🟢 Abierta
                                    <?php else: ?>
                                        🔴 Cerrada
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['estado'] == 'cerrada'): ?>
                                        <a href="pdf_cierre_caja.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-secondary">
                                            🧾 PDF
                                        </a>
                                    <?php endif; ?>
                                </td>                                
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>