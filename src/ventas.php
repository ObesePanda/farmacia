<?php
session_start();
require("../conexion.php");
$id_user = $_SESSION['idUser'];
$checkCaja = mysqli_query($conexion, "SELECT * FROM caja WHERE usuario_id = $id_user AND estado = 'abierta'");
if (mysqli_num_rows($checkCaja) == 0) {
    header("Location: abrir_caja.php");
    exit;
}
$permiso = "nueva_venta";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}
include_once "includes/header.php";
?>
<style>
        .ui-autocomplete {
        max-height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 1050 !important;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    .ui-menu-item-wrapper {
        cursor: pointer;
    }
    /* Estilos personalizados para el módulo de ventas */
    .ventas-container {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .ventas-card {
        border: none;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .ventas-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    }
    
    .ventas-card-header {
        background: linear-gradient(135deg, #1a6975 0%, #06c3c9 100%);
        color: white;
        border-radius: 8px 8px 0 0 !important;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .ventas-input-group {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .ventas-input {
        border: 1px solid #e0e0e0;
        border-radius: 6px !important;
        padding: 12px 15px;
        font-size: 0.95rem;
        transition: all 0.3s;
        background-color: #fff;
    }
    
    .ventas-input:focus {
        border-color: #1a6975;
        box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.15);
    }
    
    .ventas-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
        font-size: 0.9rem;
    }
    
    .ventas-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    .ventas-table thead th {
        background-color: #4aa2dc;
        color: white;
        padding: 12px 15px;
        font-weight: 500;
        border: none;
    }
    
    .ventas-table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .ventas-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .ventas-table tbody tr:hover {
        background-color: #f8faff;
    }
    
    .ventas-btn {
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.3s;
        border: none;
    }
    
    .ventas-btn-primary {
        background: linear-gradient(135deg, #4aa2dc 0%, #2575fc 100%);
        color: white;
    }
    
    .ventas-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 117, 252, 0.25);
    }
    
    .ventas-btn-danger {
        background: linear-gradient(135deg, #f53d2d 0%, #f53d6d 100%);
        color: white;
    }
    
    .ventas-btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 61, 45, 0.25);
    }
    
    .ventas-total-row {
        background-color: #f8f9fa !important;
        font-weight: 600;
    }
    
    .ventas-grand-total {
        background-color: #e8f0fe !important;
        color: #1a56a7;
        font-size: 1.05rem;
    }
    
    .ventas-icon {
        margin-right: 8px;
    }
    
    /* Efecto para inputs deshabilitados */
    .ventas-input:disabled {
        background-color: #f9f9f9;
        color: #777;
    }
    .ventas-loading {
    position: relative;
    overflow: hidden;
    }

    .ventas-loading::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        z-index: 1;
    }

    .ventas-loading::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #2575fc;
        border-radius: 50%;
        animation: ventas-spin 1s linear infinite;
        z-index: 2;
    }

    @keyframes ventas-spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    .ventas-table-container {
    max-height: 400px;
    overflow-y: auto;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    }

    .ventas-table {
        margin-bottom: 0;
    }

    /* Personalizar scrollbar */
    .ventas-table-container::-webkit-scrollbar {
        width: 8px;
    }

    .ventas-table-container::-webkit-scrollbar-thumb {
        background: #2575fc;
        border-radius: 4px;
    }

    .ventas-table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
</style>

<div class="row ventas-container">
    <div class="col-lg-12">
        <!-- Datos del Cliente -->
        <div class="card ventas-card">
            <div class="card-header ventas-card-header">
                <i class="fas fa-user ventas-icon"></i>Datos del Cliente
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group ventas-input-group">
                                <input type="hidden" id="idcliente" value="1" name="idcliente" required>
                                <label class="ventas-label">Nombre</label>
                                <input type="text" name="nom_cliente" id="nom_cliente" class="form-control ventas-input" placeholder="Ingrese nombre del cliente" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group ventas-input-group">
                                <label class="ventas-label">Teléfono</label>
                                <input type="number" name="tel_cliente" id="tel_cliente" class="form-control ventas-input" disabled required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group ventas-input-group">
                                <label class="ventas-label">Dirección</label>
                                <input type="text" name="dir_cliente" id="dir_cliente" class="form-control ventas-input" disabled required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Buscar Productos -->
        <div class="card ventas-card">
            <div class="card-header ventas-card-header">
                <i class="fas fa-search ventas-icon"></i>Buscar Productos
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="form-group ventas-input-group">
                            <label class="ventas-label" for="producto">Código o Nombre</label>
                            <input id="producto" class="form-control ventas-input" type="text" name="producto" placeholder="Ingresa el código o nombre">
                            <input id="id" type="hidden" name="id">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group ventas-input-group">
                            <label class="ventas-label" for="cantidad">Cantidad</label>
                            <input id="cantidad" class="form-control ventas-input" type="text" name="cantidad" placeholder="Cantidad" onkeyup="calcularPrecio(event)">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group ventas-input-group">
                            <label class="ventas-label" for="precio">Precio</label>
                            <input id="precio" class="form-control ventas-input" type="text" name="precio" placeholder="Precio" disabled>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group ventas-input-group">
                            <label class="ventas-label" for="sub_total">Sub Total</label>
                            <input id="sub_total" class="form-control ventas-input" type="text" name="sub_total" placeholder="Sub Total" disabled>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Tabla de Productos -->
        <div class="table-responsive">
            <table class="table ventas-table" id="tblDetalle">
                <thead>
                    <tr>
                        <th width="5%">Id</th>
                        <th width="30%">Descripción</th>
                        <th width="10%">Cantidad</th>
                        <th width="10%">Aplicar</th>
                        <th width="10%">Desc.</th>
                        <th width="15%">Precio</th>
                        <th width="5%">Precio Total</th>
                        <th width="10%">Lote</th>
                        <th width="5%">Acción</th>
                    </tr>
                </thead>
                <tbody id="detalle_venta">
                    <!-- Filas dinámicas irán aquí -->
                </tbody>
                <tfoot>
                    <tr class="ventas-total-row">
                        <td colspan="5" class="text-right">Subtotal:</td>
                        <td colspan="2" id="subtotal-venta">$0.00</td>
                        <td></td>
                    </tr>
                    <tr class="ventas-total-row">
                        <td colspan="5" class="text-right">IVA (16%):</td>
                        <td colspan="2" id="iva-venta">$0.00</td>
                        <td></td>
                    </tr>
                    <tr class="ventas-grand-total">
                        <td colspan="5" class="text-right font-weight-bold">Total a Pagar:</td>
                        <td colspan="2" id="total-venta">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Botones de Acción -->
        <div class="row mt-4">
             <div class="col-md-4 mb-3">
                <a href="cerrar_caja.php" class="btn btn-warning w-100">
                    <i class="fas fa-lock"></i> Cerrar Caja
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn ventas-btn ventas-btn-danger w-100" id="btn_cancelar">
                    <i class="fas fa-times ventas-icon"></i>Cancelar Venta
                </button>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn ventas-btn ventas-btn-primary w-100" id="btn_generar">
                    <i class="fas fa-save ventas-icon"></i>Generar Venta
                </button>
            </div>
           
        </div>
    </div>
</div>
<!-- Modal de Pago -->
<div class="modal fade" id="modalPago" tabindex="-1" role="dialog" aria-labelledby="modalPagoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" style="color: #fff;"><i class="bi bi-coin"></i> Confirmar Pago</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p><strong>Total:</strong> $<span id="totalModal">0.00</span></p>

        <div class="form-group">
          <label for="metodo_pago">Método de pago</label>
          <select id="metodo_pago" class="form-control">
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="transferencia">Transferencia</option>
          </select>
        </div>

        <div class="form-group" id="grupo_entregado">
          <label for="monto_entregado">Monto entregado</label>
          <input type="number" class="form-control" id="monto_entregado" min="0" step="0.01">
        </div>

        <div class="alert alert-info" id="alert_cambio" style="display:none; color: #fff">
          Cambio: $<span id="cambio_valor">0.00</span>
        </div>
      </div>
      <div class="modal-footer">
        <button id="confirmarPago" class="btn btn-success">Confirmar y Generar Venta</button>
      </div>
    </div>
  </div>
</div>


<?php include_once "includes/footer.php"; ?>