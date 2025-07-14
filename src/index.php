<?php
require "../conexion.php";
$usuarios = mysqli_query($conexion, "SELECT * FROM usuario");
$total['usuarios'] = mysqli_num_rows($usuarios);
$clientes = mysqli_query($conexion, "SELECT * FROM cliente");
$total['clientes'] = mysqli_num_rows($clientes);
$productos = mysqli_query($conexion, "SELECT * FROM producto");
$total['productos'] = mysqli_num_rows($productos);
$ventas = mysqli_query($conexion, "SELECT * FROM ventas WHERE fecha > CURDATE()");
$total['ventas'] = mysqli_num_rows($ventas);
session_start();
include_once "includes/header.php";
?>
<!-- Content Row -->
<div class="row">

    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total</p>
                            <a href="lista_ventas.php" class="font-weight-bolder">
                                Ventas Hoy
                            </a>
                            <p class="mb-0">
                                <span
                                    class="text-success text-sm font-weight-bolder"><?php echo $total['ventas']; ?></span>

                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                            <i class="bi bi-wallet2 text-lg opacity-10" aria-hidden="true""></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total</p>
                            <a href="usuarios.php" class="font-weight-bolder">
                                Usuarios
                            </a>
                            <p class="mb-0">
                                <span
                                    class="text-danger text-sm font-weight-bolder"><?php echo $total['usuarios']; ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                            <i class="bi bi-people-fill text-lg opacity-10" aria-hidden="true""></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body p-3">
            <div class="row">
                <div class="col-8">
                <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total</p>
                    <a href="clientes.php" class="font-weight-bolder">
                    Clientes
                    </a>
                    <p class="mb-0">
                    <span class="text-success text-sm font-weight-bolder"><?php echo $total['clientes']; ?></span> 
                    </p>
                </div>
                </div>
                <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                <i class="bi bi-person-lines-fill text-lg opacity-10" aria-hidden="true""></i>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body p-3">
            <div class="row">
                <div class="col-8">
                <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total</p>
                    <a href="productos.php" class="font-weight-bolder">
                    Productos
                    </a>
                    <p class="mb-0">
                    <span class="text-success text-sm font-weight-bolder"><?php echo $total['productos']; ?></span> 
                    </p>
                </div>
                </div>
                <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                    <i class="bi bi-bandaid-fill text-lg opacity-10" aria-hidden="true"></i>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    

<div class="col-lg-12 mt-4 mb-4">
    <div class="card col-xl mb-lg-0 mb-4">
        <div class="card-header">
      
        <h6 class="text-capitalize">Medicamento Vencido</h6>
              <p class="text-sm mb-0">
              <i class="bi bi-radioactive"></i>
                <span class="font-weight-bold">Productos vencido</span> superaron la fecha de vencimiento registrada.
              </p>
        </div>
        <div class="col-md-12 p-3">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mt-2 dataTable no-footer" id="tbl">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Imagen</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Presentación</th>
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

                            if (!$vencidoLote) {
                                continue;
                            }
                            
                        ?>
                        <tr class="<?= $vencidoLote ? 'table-danger' : ''; ?>">
                            <td class="text-center fw-bold"><?= $data['codproducto']; ?></td>
                            <td class="text-center">
                                <?php if ($data['imagen']) { ?>
                                    <img src="img/productos/<?= $data['imagen']; ?>" 
                                        alt="Producto" 
                                        class="rounded img-thumbnail" 
                                        style="width: 60px; height: 60px; object-fit: cover;">
                                <?php } else { ?>
                                    <span class="badge bg-secondary">Sin imagen</span>
                                <?php } ?>
                            </td>
                            <td class="text-xs font-weight-bold mb-0"><?= $data['codigo']; ?></td>
                            <td class="text-xs font-weight-bold mb-0"><?= $data['descripcion']; ?></td>
                            <td class="text-center"><span class="badge bg-info"><?= $data['tipo']; ?></span></td>
                            <td class="text-center"><span class="badge bg-success"><?= $data['nombre']; ?></span></td>
                            <td class="text-center">
                                <?php if (!empty($vencimientoLote)): ?>
                                    <span class="<?= $vencidoLote ? 'text-danger fw-bold' : 'text-success'; ?>">
                                        <?= date('d/m/Y', strtotime($vencimientoLote)); ?>
                                        <?php if ($vencidoLote): ?>
                                            <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Producto vencido"></i>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sin fecha</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                  
                                   
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
<div class="col-lg-6">
        <div class="card">
            <div class="card-header card-header-primary">
            <h6 class="text-capitalize">Productos más vendidos</h6>          
            </div>
            <div class="card-body">
                <canvas id="ProductosVendidos"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header card-header-primary">
            <h6 class="text-capitalize">Productos con stock mínimo</h6>
                
            </div>
            <div class="card-body">
                <canvas id="stockMinimo"></canvas>
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