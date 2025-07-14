<?php
session_start();
include "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "compras";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
  header('Location: permisos.php');
}
include_once "includes/header.php";
?>

<div class="card shadow-lg">
  <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0 text-white"><i class="bi bi-bag-plus me-2"></i> Nueva Compra</h4>
  </div>
  <div class="card-body">
    <form id="formCompra">

      <!-- Datos de proveedor y pago -->
      <div class="row mb-3">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold"><i class="bi bi-truck"></i> Proveedor</label>
          <select name="proveedor" id="proveedor" class="form-select" required>
            <option value="">Seleccione un proveedor</option>
            <?php
            $prov = mysqli_query($conexion, "SELECT * FROM laboratorios");
            while ($row = mysqli_fetch_assoc($prov)) {
              echo "<option value='{$row['id']}'>{$row['laboratorio']}</option>";
            }
            ?>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold"><i class="bi bi-credit-card-2-front"></i> Tipo de Pago</label>
          <select name="tipo_pago" id="tipo_pago" class="form-select">
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>Lote</label>
          <input type="text" id="lote" class="form-control">
        </div>
        <div class="col-md-6">
          <label>Vencimiento</label>
          <input type="date" id="vencimiento_lote" class="form-control">
        </div>
      </div>

      <hr class="my-4">

      <!-- Productos -->
      <div class="row align-items-end">
        <div class="col-md-4 mb-3">
          <label class="form-label fw-bold"><i class="bi bi-box-seam"></i> Producto</label>
          <input type="text" id="codigo" placeholder="Código o nombre" class="form-control">
          <input type="hidden" id="id_producto">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label fw-bold"><i class="bi bi-hash"></i> Cantidad</label>
          <input type="number" id="cantidad" class="form-control" value="1" min="1">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label fw-bold"><i class="bi bi-currency-dollar"></i> Costo</label>
          <input type="number" id="precio" class="form-control" step="0.01" min="0">
        </div>
        <div class="col-md-2 ">
          <button type="button" id="addProducto" class="btn btn-primary btn-md">
            <i class="bi bi-plus-circle"></i> Agregar
          </button>
        </div>
      </div>

      <div class="table-responsive mt-4">
        <table class="table table-hover align-middle" id="tblDetalle">
          <thead class="table-dark">
            <tr>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Costo</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="text-end mt-3">
        <h4 class="fw-bold text-primary">Total: $<span id="totalCompra">0.00</span></h4>
      </div>

      <div class="mb-3 mt-4">
        <label class="form-label fw-bold"><i class="bi bi-chat-text"></i> Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="3"
          placeholder="Agregue algún comentario adicional..."></textarea>
      </div>

      <div class="d-flex justify-content-end mt-3">
        <button type="button" id="btnGuardarCompra" class="btn btn-primary btn-lg">
          <i class="bi bi-save me-2"></i> Registrar Compra
        </button>
      </div>

    </form>
  </div>
</div>

<?php include_once "includes/footer.php"; ?>