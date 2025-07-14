document.addEventListener("DOMContentLoaded", function () {
    $('#tbl').DataTable({
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.10.11/i18n/Spanish.json"
        },
        "order": [
            [0, "desc"]
        ]
    });
    $(".confirmar").submit(function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Estas seguro de eliminar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#199eaf',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        })
    })
    $("#nom_cliente").autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.ajax({
                url: "ajax.php",
                dataType: "json",
                data: {
                    q: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        select: function (event, ui) {
            $("#idcliente").val(ui.item.id);
            $("#nom_cliente").val(ui.item.label);
            $("#tel_cliente").val(ui.item.telefono);
            $("#dir_cliente").val(ui.item.direccion);
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li class='ui-menu-item-wrapper'></li>")
            .append(`
                <div style="padding: 8px; border-bottom: 1px solid #eee;">
                    <strong>${item.label}</strong><br>
                    <small>📞 ${item.telefono} | 📍 ${item.direccion}</small>
                </div>
            `)
            .appendTo(ul);
    };
    /** PRODUCTO AUTOCOMPLETADO */
    $("#producto").autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.ajax({
                url: "ajax.php",
                dataType: "json",
                data: {
                    pro: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        open: function (event, ui) {
            $(".ui-autocomplete").addClass("dropdown-menu show shadow").css({
                "max-height": "300px",
                "overflow-y": "auto",
                "z-index": 9999
            });
        },
        select: function (event, ui) {
            $("#id").val(ui.item.id);
            $("#producto").val(ui.item.value);
            $("#precio").val(ui.item.precio);
            $("#cantidad").focus();

           
            return false;
        }
    })
    .autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li class='dropdown-item'></li>")
            .append(`
                <div style="white-space: normal;">
                    <strong>${item.value}</strong><br>
                    <small>💲 Precio: $${parseFloat(item.precio).toFixed(2)}</small>
                </div>
            `)
            .appendTo(ul);
    };
    $('#cantidad').on('keypress', function(e) {
        if (e.key === 'Enter') {
            const id = $('#id').val();
            const cant = $('#cantidad').val();
            const precio = $('#precio').val();
            if(id && cant && precio) {
                registrarDetalle(null, id, cant, precio);
                e.preventDefault(); 
            } else {
                Swal.fire('Error', 'Completa todos los campos', 'error');
            }
        }
    });


    $('#btn_generar').click(function (e) {
        e.preventDefault();
        var rows = $('#tblDetalle tr').length;
        if (rows > 2) {
            const totalTexto = $('#total-venta').text().replace('$', '');
            const total = parseFloat(totalTexto);

            $('#totalModal').text(total.toFixed(2));
            $('#monto_entregado').val('');
            $('#cambio_valor').text('0.00');
            $('#alert_cambio').hide();
            $('#modalPago').modal('show');
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atención!',
                text: 'No hay productos en la lista de la venta',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });

    $('#monto_entregado').on('input', function () {
        const total = parseFloat($('#totalModal').text());
        const entregado = parseFloat($(this).val()) || 0;
        const cambio = entregado - total;

        $('#cambio_valor').text(cambio.toFixed(2));
        $('#alert_cambio').show();
    });

    $('#confirmarPago').click(function () {
        const metodo = $('#metodo_pago').val();
        const entregado = parseFloat($('#monto_entregado').val()) || 0;
        const total = parseFloat($('#totalModal').text());
        const cambio = entregado - total;

        const detallesVenta = obtenerDetallesVentaConLote();

        // Validar que cada detalle tenga lote seleccionado
        if (detallesVenta.some(d => !d.id_lote)) {
            Swal.fire({
                icon: 'error',
                title: 'Falta seleccionar lote',
                text: 'Por favor selecciona un lote para cada producto.'
            });
            return;
        }

        if (metodo === 'efectivo' && entregado < total) {
            Swal.fire({
                icon: 'error',
                title: 'Pago insuficiente',
                text: 'El monto entregado es menor al total.'
            });
            return;
        }

        $.ajax({
            url: 'ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                procesarVenta: 'procesarVenta',
                id: $('#idcliente').val(),
                metodo_pago: metodo,
                monto_entregado: entregado,
                cambio: cambio,
                detalles: JSON.stringify(detallesVenta)
            },
            success: function (res) {
                if (res.status) {
                    $('#modalPago').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Venta generada correctamente'
                    });
                    setTimeout(() => {
                        generarPDF(res.id_cliente, res.id_venta);
                        location.reload();
                    }, 300);
                } else if (res.vencidos) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Venta bloqueada',
                        html: `<p>${res.mensaje}</p><ul style="text-align:left;">` +
                            res.vencidos.map(p => `<li>${p}</li>`).join('') +
                            `</ul>`,
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.mensaje || 'La venta no pudo ser generada.'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error en AJAX:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la venta.'
                });
            }
        });
    });



    if (document.getElementById("detalle_venta")) {
        listar();
    }
})


function obtenerDetallesVentaConLote() {
    const detalles = [];
    $('#detalle_venta tr').each(function () {
        const idDetalle = $(this).find('td').eq(0).text().trim(); // id de detalle_temp
        const cantidad = parseFloat($(this).find('td').eq(2).text().trim());
        const descuento = parseFloat($(this).find('td').eq(3).find('input').val()) || 0;
        const precio = parseFloat($(this).find('td').eq(5).text().trim());
        const idLote = $(this).find('select').val();

        detalles.push({
            id_detalle: idDetalle,
            cantidad,
            descuento,
            precio,
            id_lote: idLote
        });
    });
    return detalles;
}

function calcularPrecio(e) {
    e.preventDefault();
    const cant = $("#cantidad").val();
    const precio = $('#precio').val();
    const total = cant * precio;
    $('#sub_total').val(total);
    if (e.which == 13) {
        if (cant > 0 && cant != '') {
            const id = $('#id').val();
            registrarDetalle(e, id, cant, precio);
            $('#producto').focus();
        } else {
            $('#cantidad').focus();
            return false;
        }
    }
}

function calcularDescuento(e, id) {
    if (e.which == 13) {
        let descuento = 'descuento';
        $.ajax({
            url: "ajax.php",
            type: 'GET',
            dataType: "json",
            data: {
                id: id,
                desc: e.target.value,
                descuento: descuento
            },
            success: function (response) {

                if (response.mensaje == 'descontado') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Descuento aplicado',
                        showConfirmButton: false,
                        timer: 2000
                    })
                    listar();
                } else {}
            }
        });
    }
}

function listar() {
    let html = '';
    let detalle = 'detalle';
    $.ajax({
        url: "ajax.php",
        dataType: "json",
        data: {
            detalle: detalle
        },
        success: function (response) {
            html = "";
            response.forEach(row => {
                html += `<tr>
                    <td class="align-middle text-center"><p class="text-xs font-weight-bold mb-0">${row['id_producto']}</p></td>
                    <td class="align-middle text-center"><p class="text-xs font-weight-bold mb-0">${row['descripcion']}</p></td>
                    <td class="align-middle text-center"><p class="text-xs font-weight-bold mb-0">${row['cantidad']}</p></td>
                    <td width="100">
                        <input class="form-control" placeholder="Desc" type="number" onkeyup="calcularDescuento(event, ${row['id']})">
                    </td>
                    <td class="align-middle text-center"><p class="text-xs font-weight-bold mb-0">${row['descuento']}</p></td>
                    <td class="align-middle text-center"><p class="text-xs font-weight-bold mb-0">${row['precio_venta']}</p></td>
                    <td class="align-middle text-center"><p class="text-xs font-weight-bold mb-0">${row['sub_total']}</p></td>
                    <td class="align-middle text-center">
                        <select id="id_lote_${row['id']}" class="form-control ventas-input"></select>
                    </td>
                    <td class="align-middle text-center">
                        <button class="btn btn-danger" type="button" onclick="deleteDetalle(${row['id']})">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                </tr>`;
            });
            document.querySelector("#detalle_venta").innerHTML = html;
            calcular();

           
            document.querySelectorAll("select[id^='id_lote_']").forEach(select => {
         
                const idDetalle = select.id.split("_")[2];
             
                const producto = response.find(r => r.id == idDetalle);
                if (producto && producto.id_producto) {
                    obtenerLotesProducto(producto.id_producto, select);
                }
            });
        }

    });
    
}

function obtenerLotesProducto(idProducto, selectElement) {
    
        $.ajax({
        url: "ajax.php",
        type: "POST",
        dataType: "json",
        data: {
            accion: 'lotes_producto',   
            id_producto: idProducto
        },
        success: function (response) {
            let options = "<option value=''>Seleccionar lote</option>";
            response.forEach(lote => {
                const vencido = (lote.vencimiento && new Date(lote.vencimiento) < new Date()) 
                    ? " (VENCIDO)" 
                    : "";
                options += `<option value="${lote.id}" 
                    ${lote.vencimiento && new Date(lote.vencimiento) < new Date() ? 'disabled' : ''}>
                    ${lote.numero_lote} - ${lote.cantidad} unds - vence ${lote.vencimiento || 'N/A'}${vencido}
                </option>`;
            });
            $(selectElement).html(options);
        }
    });

}

function registrarDetalle(e, id, cant, precio) {
    if (document.getElementById('producto').value != '') {
        if (id != null) {
            let action = 'regDetalle';
            $.ajax({
                url: "ajax.php",
                type: 'POST',
                dataType: "json",
                data: {
                    id: id,
                    cant: cant,
                    regDetalle: action,
                    precio: precio
                },
                success: function (response) {

                    if (response == 'registrado') {
                        $('#cantidad').val('');
                        $('#precio').val('');
                        $("#producto").val('');
                        $("#sub_total").val('');
                        $("#producto").focus();
                        listar();
                        Swal.fire({
                            
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Producto ingresado correctamente',
                            showConfirmButton: false,
                            timer: 2000
                        })
                    } else if (response == 'actualizado') {
                        $('#cantidad').val('');
                        $('#precio').val('');
                        $("#producto").val('');
                        $("#producto").focus();
                        listar();
                        Swal.fire({
                            
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Cambios realizados correctamente',
                            showConfirmButton: false,
                            timer: 2000
                        })
                    } else {
                        $('#id').val('');
                        $('#cantidad').val('');
                        $('#precio').val('');
                        $("#producto").val('');
                        $("#producto").focus();
                        Swal.fire({
                            
                            icon: 'error',
                            title: response,
                            showConfirmButton: false,
                            timer: 2000
                        })
                    }
                }
            });
        }
    }
}

function deleteDetalle(id) {
    let detalle = 'Eliminar'
    $.ajax({
        url: "ajax.php",
        data: {
            id: id,
            delete_detalle: detalle
        },
        success: function (response) {

            if (response == 'restado') {
                Swal.fire({
                   
                    icon: 'success',
                    title: 'Producto Descontado',
                    showConfirmButton: false,
                    timer: 2000
                })
                document.querySelector("#producto").value = '';
                document.querySelector("#producto").focus();
                listar();
            } else if (response == 'ok') {
                Swal.fire({
                   
                    icon: 'success',
                    title: 'Eliminado',
                    text: 'Producto eliminado correctamente',
                    showConfirmButton: false,
                    timer: 2000
                })
                document.querySelector("#producto").value = '';
                document.querySelector("#producto").focus();
                listar();
            } else {
                Swal.fire({
                   
                    icon: 'error',
                    title: 'Error al eliminar el producto',
                    showConfirmButton: false,
                    timer: 2000
                })
            }
        }
    });
}

function calcular() {
    // obtenemos todas las filas del tbody
    var filas = document.querySelectorAll("#tblDetalle tbody tr");

    var total = 0;

    // recorremos cada una de las filas
    filas.forEach(function (e) {

        // obtenemos las columnas de cada fila
        var columnas = e.querySelectorAll("td");

        // obtenemos los valores de la cantidad y importe
        var importe = parseFloat(columnas[6].textContent);

        
        total += importe;
        iva = total - (total / 1.16);
        baseImponible = total / 1.16;
    });

    // mostramos la suma total
    var filas = document.querySelectorAll("#tblDetalle tfoot tr td");
    filas[7].textContent = total.toFixed(2);
    filas[4].textContent = iva.toFixed(2);
    filas[1].textContent = baseImponible.toFixed(2);
}





function generarPDF(cliente, id_venta) {
    url = 'pdf/generar.php?cl=' + cliente + '&v=' + id_venta;
    window.open(url, '_blank');
}
if (document.getElementById("stockMinimo")) {
    const action = "sales";
    $.ajax({
        url: 'chart.php',
        type: 'POST',
        data: {
            action
        },
        async: true,
        success: function (response) {
            if (response != 0) {
                var data = JSON.parse(response);
                var nombre = [];
                var cantidad = [];
                for (var i = 0; i < data.length; i++) {
                    nombre.push(data[i]['descripcion']);
                    cantidad.push(data[i]['existencia']);
                }
                var ctx = document.getElementById("stockMinimo");
                var myPieChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: nombre,
                        datasets: [{
                            data: cantidad,
                            backgroundColor: ['#024A86', '#E7D40A', '#581845', '#C82A54', '#EF280F', '#8C4966', '#FF689D', '#E36B2C', '#69C36D', '#23BAC4'],
                        }],
                    },
                });
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
}
if (document.getElementById("ProductosVendidos")) {
    const action = "polarChart";
    $.ajax({
        url: 'chart.php',
        type: 'POST',
        async: true,
        data: {
            action
        },
        success: function (response) {
            if (response != 0) {
                var data = JSON.parse(response);
                var nombre = [];
                var cantidad = [];
                for (var i = 0; i < data.length; i++) {
                    nombre.push(data[i]['descripcion']);
                    cantidad.push(data[i]['cantidad']);
                }
                var ctx = document.getElementById("ProductosVendidos");
                var myPieChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: nombre,
                        datasets: [{
                            data: cantidad,
                            backgroundColor: ['#C82A54', '#EF280F', '#23BAC4', '#8C4966', '#FF689D', '#E7D40A', '#E36B2C', '#69C36D', '#581845', '#024A86'],
                        }],
                    },
                });
            }
        },
        error: function (error) {
            console.log(error);

        }
    });
}

function btnCambiar(e) {
    e.preventDefault();
    const actual = document.getElementById('actual').value;
    const nueva = document.getElementById('nueva').value;
    if (actual == "" || nueva == "") {
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Los campos estan vacios',
            showConfirmButton: false,
            timer: 2000
        })
    } else {
        const cambio = 'pass';
        $.ajax({
            url: "ajax.php",
            type: 'POST',
            data: {
                actual: actual,
                nueva: nueva,
                cambio: cambio
            },
            success: function (response) {
                if (response == 'ok') {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Contraseña modificado',
                        showConfirmButton: false,
                        timer: 2000
                    })
                    document.querySelector('#frmPass').reset();
                    $("#nuevo_pass").modal("hide");
                } else if (response == 'dif') {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'La contraseña actual incorrecta',
                        showConfirmButton: false,
                        timer: 2000
                    })
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error al modificar la contraseña',
                        showConfirmButton: false,
                        timer: 2000
                    })
                }
            }
        });
    }
}

function editarCliente(id) {
    const action = "editarCliente";
    $.ajax({
        url: 'ajax.php',
        type: 'GET',
        async: true,
        data: {
            editarCliente: action,
            id: id
        },
        success: function (response) {
            const datos = JSON.parse(response);
            $('#nombre').val(datos.nombre);
            $('#telefono').val(datos.telefono);
            $('#direccion').val(datos.direccion);
            $('#id').val(datos.idcliente);
            $('#btnAccion').val('Modificar');
        },
        error: function (error) {
            console.log(error);

        }
    });
}

function editarUsuario(id) {
    const action = "editarUsuario";
    $.ajax({
        url: 'ajax.php',
        type: 'GET',
        async: true,
        data: {
            editarUsuario: action,
            id: id
        },
        success: function (response) {
            const datos = JSON.parse(response);
            $('#nombre').val(datos.nombre);
            $('#usuario').val(datos.usuario);
            $('#correo').val(datos.correo);
            $('#id').val(datos.idusuario);
            $('#btnAccion').val('Modificar');
        },
        error: function (error) {
            console.log(error);

        }
    });
}

function editarProducto(id) {
    const action = "editarProducto";
    $.ajax({
        url: 'ajax.php',
        type: 'GET',
        async: true,
        data: {
            editarProducto: action,
            id: id
        },
        success: function (response) {
            const datos = JSON.parse(response);
            $('#codigo').val(datos.codigo);
            $('#producto').val(datos.descripcion);
            $('#precio').val(datos.precio);
            $('#id').val(datos.codproducto);
            $('#costo').val(datos.costo);
            $('#tipo').val(datos.id_tipo);
            $('#presentacion').val(datos.id_presentacion);
            $('#laboratorio').val(datos.id_lab);
            $('#vencimiento').val(datos.vencimiento);
            $('#cantidad').val(datos.existencia);
            if (datos.vencimiento != '0000-00-00') {
                $("#accion").prop("checked", true);
            }else{
                $("#accion").prop("checked", false);
            }
            $('#btnAccion').val('Modificar');
            calcularUtilidad();
        },
        error: function (error) {
            console.log(error);

        }
        
    });
}

$(document).ready(function () {

  $('#costo').on('input', function () {
    calcularPrecio();
  });


  $('#utilidad').on('input', function () {
    calcularPrecio();
  });

  
  $('#precio').on('input', function () {
    calcularUtilidad();
  });
});

function calcularPrecio() {
  const costo = parseFloat($('#costo').val());
  const utilidad = parseFloat($('#utilidad').val());

  if (!isNaN(costo) && !isNaN(utilidad)) {
    const precio = costo + (costo * (utilidad / 100));
    $('#precio').val(precio.toFixed(2));
  }
}

function calcularUtilidad() {
  const costo = parseFloat($('#costo').val());
  const precio = parseFloat($('#precio').val());

  if (!isNaN(costo) && costo > 0 && !isNaN(precio)) {
    const utilidad = ((precio - costo) / costo) * 100;
    $('#utilidad').val(utilidad.toFixed(2));
  }
}


function limpiar() {
    $('#formulario')[0].reset();
    $('#id').val('');
    $('#btnAccion').val('Registrar');
}
function editarTipo(id) {
    const action = "editarTipo";
    $.ajax({
        url: 'ajax.php',
        type: 'GET',
        async: true,
        data: {
            editarTipo: action,
            id: id
        },
        success: function (response) {
            const datos = JSON.parse(response);
            $('#nombre').val(datos.tipo);
            $('#id').val(datos.id);
            $('#btnAccion').val('Modificar');
        },
        error: function (error) {
            console.log(error);

        }
    });
}
function editarPresent(id) {
    const action = "editarPresent";
    $.ajax({
        url: 'ajax.php',
        type: 'GET',
        async: true,
        data: {
            editarPresent: action,
            id: id
        },
        success: function (response) {
            const datos = JSON.parse(response);
            $('#nombre').val(datos.nombre);
            $('#nombre_corto').val(datos.nombre_corto);
            $('#id').val(datos.id);
            $('#btnAccion').val('Modificar');
        },
        error: function (error) {
            console.log(error);

        }
    });
}
function editarLab(id) {
    const action = "editarLab";
    $.ajax({
        url: 'ajax.php',
        type: 'GET',
        async: true,
        data: {
            editarLab: action,
            id: id
        },
        success: function (response) {
            const datos = JSON.parse(response);
            $('#laboratorio').val(datos.laboratorio);
            $('#direccion').val(datos.direccion);
            $('#correo').val(datos.correo);
            $('#telefono').val(datos.telefono);
            $('#id').val(datos.id);
            $('#btnAccion').val('Modificar');
        },
        error: function (error) {
            console.log(error);

        }
    });
}


// Variables globales para el detalle de la compra
let detalle = [];
let total = 0;


document.addEventListener('DOMContentLoaded', () => {

  if (document.querySelector('#formCompra')) {
   
    const btnAdd = document.getElementById('addProducto');
    btnAdd.addEventListener('click', agregarProductoAlDetalle);
    const btnGuardar = document.getElementById('btnGuardarCompra');
    btnGuardar.addEventListener('click', guardarCompra);
  }
});

/**
 * Agrega un producto al detalle de compra
 */
function agregarProductoAlDetalle() {
    const codigo = document.getElementById('codigo').value;
    const cantidad = parseInt(document.getElementById('cantidad').value);
    const precio = parseFloat(document.getElementById('precio').value);
    const lote = document.getElementById('lote').value;
    const vencimiento = document.getElementById('vencimiento_lote').value;
    const id_producto = document.getElementById('id_producto').value;

    if (!codigo || isNaN(cantidad) || cantidad <= 0 || isNaN(precio) || precio <= 0 || !lote || !vencimiento) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }


  const subtotal = cantidad * precio;

    detalle.push({
        id: id_producto,
        codigo,
        cantidad,
        precio,
        subtotal,
        lote,
        vencimiento
    });

    renderDetalle();
    limpiarCamposProducto();
}

/**
 * Renderiza compra en la tabla
 */
function renderDetalle() {
  const tbody = document.querySelector('#tblDetalle tbody');
  tbody.innerHTML = '';
  total = 0;

  detalle.forEach((item, index) => {
    total += item.subtotal;

    tbody.innerHTML += `
      <tr>
        <td>${item.codigo} <small class="text-muted">(ID: ${item.id})</small></td>
        <td>${item.cantidad}</td>
        <td>$${item.precio.toFixed(2)}</td>
        <td>$${item.subtotal.toFixed(2)}</td>
        <td>
          <button class="btn btn-danger btn-sm" onclick="eliminarItem(${index})">
            <i class="bi bi-x-circle"></i>
          </button>
        </td>
      </tr>
    `;
  });

  document.getElementById('totalCompra').textContent = total.toFixed(2);
}


/**
 * Elimina un producto del detalle
 * @param {*} index 
 */
function eliminarItem(index) {
  detalle.splice(index, 1);
  renderDetalle();
}

function limpiarCamposProducto() {
  document.getElementById('id_producto').value = "";
  document.getElementById('codigo').value = "";
  document.getElementById('precio').value = "";
  document.getElementById('cantidad').value = "1";
  document.getElementById('codigo').focus();
}

function guardarCompra() {
  const proveedor = document.getElementById('proveedor').value;

  if (!proveedor) {
    Swal.fire("Error", "Selecciona un proveedor.", "error");
    return;
  }

  if (detalle.length === 0) {
    Swal.fire("Error", "No hay productos en el detalle.", "error");
    return;
  }

  const datos = new FormData();
  datos.append('proveedor', proveedor);
  datos.append('total', total);
  datos.append('detalle', JSON.stringify(detalle));

  fetch('guardar_compra.php', {
    method: 'POST',
    body: datos
  })
    .then(response => response.json())
    .then(data => {
      if (data.status) {       
          Swal.fire({
                title: "Éxito",
                text: data.mensaje,
                icon: "success"
            }).then(() => {
                window.open('generar_pdf_compra.php?id=' + data.id, '_blank');
                location.reload();
            });
      } else {
        Swal.fire("Error", data.mensaje, "error");
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire("Error", "Ocurrió un error al guardar la compra.", "error");
    });
}
//////////////COMPRAS

$("#codigo").autocomplete({
    minLength: 2,
    source: function (request, response) {
        $.ajax({
            url: "ajax.php",
            dataType: "json",
            data: {
                pro_compra: request.term
            },
            success: function (data) {
                response(data);
            }
        });
    },
    open: function (event, ui) {
      
        $(".ui-autocomplete").addClass("dropdown-menu show shadow").css({
            "max-height": "300px",
            "overflow-y": "auto",
            "z-index": 9999 
        });
    },
    select: function (event, ui) {
        $("#id_producto").val(ui.item.id);
        $("#codigo").val(ui.item.value);
        $("#precio").val("");
        $("#cantidad").focus();
    }
}).autocomplete("instance")._renderItem = function (ul, item) {
    return $("<li class='dropdown-item'></li>")
        .append(`
            <div style="white-space: normal;">
                <strong>${item.value}</strong><br>
                <small>💲 Precio: $${parseFloat(item.precio).toFixed(2)}</small>
            </div>
        `)
        .appendTo(ul);
};


/** LOTES FUNCIONES */
function verLotes(idProducto) {
    fetch(`get_lotes.php?id=${idProducto}`)
        .then(response => response.json())
        .then(data => {
            let html = "";
            const hoy = new Date().toISOString().split("T")[0]; // fecha actual en formato YYYY-MM-DD

            if (data.length > 0) {
                data.forEach(lote => {
                    const vencido = lote.fecha_vencimiento && lote.fecha_vencimiento < hoy;

                    html += `
                        <tr class="${vencido ? 'table-danger' : ''}">
                            <td>${lote.lote}</td>
                            <td class="${vencido ? 'text-danger fw-bold' : 'text-success'}">
                                ${lote.fecha_vencimiento ? lote.fecha_vencimiento : 'Sin fecha'}
                                ${vencido ? '<i class="bi bi-exclamation-triangle-fill ms-1" title="Lote vencido"></i>' : ''}
                            </td>
                            <td>${lote.cantidad}</td>
                            <td>$${parseFloat(lote.costo).toFixed(2)}</td>
                        </tr>
                    `;
                });
            } else {
                html = `<tr><td colspan="4" class="text-center text-muted">No hay lotes registrados.</td></tr>`;
            }

            document.getElementById("lotesBody").innerHTML = html;
            const myModal = new bootstrap.Modal(document.getElementById('modalLotes'));
            myModal.show();
        })
        .catch(err => {
            console.error(err);
            Swal.fire("Error", "Ocurrió un error al obtener los lotes.", "error");
        });
}


