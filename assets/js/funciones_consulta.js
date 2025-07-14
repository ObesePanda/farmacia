let listaProductos = [];
$(document).ready(function() {
  $('#producto').select2({
    placeholder: "Buscar producto...",
    allowClear: true,
    width: '100%',
    templateResult: formatProducto,
    templateSelection: formatProductoSelection
  });
});

function formatProducto(state) {
  if (!state.id) {
    return state.text;
  }

  let precio = $(state.element).data('precio') || 0;
  

  return $(`
    <div style="display: flex; justify-content: space-between;">
      <span>${state.text}</span>
      <span style="font-size: 0.9em; color: #888;">
        $${precio} 
    </div>
  `);
}

function formatProductoSelection(state) {
  return state.text || state.id;
}

document.getElementById("btnAgregarProducto").addEventListener("click", () => {
  let select = document.getElementById("producto");
  let codproducto = select.value;
  let texto = select.selectedOptions[0]?.text || "";
  let dosis = document.getElementById("dosis").value;
  let frecuencia = document.getElementById("frecuencia").value;
  let duracion = document.getElementById("duracion").value;

  if (!codproducto || !dosis || !frecuencia || !duracion) {
    Swal.fire("Error", "Debe completar todos los campos.", "warning");
    return;
  }

  listaProductos.push({
    codproducto,
    nombre: texto,
    dosis,
    frecuencia,
    duracion,
  });

  renderTabla();
  limpiarCampos();
});

function renderTabla() {
  let tbody = document.querySelector("#tablaProductos tbody");
  tbody.innerHTML = "";
  listaProductos.forEach((item, i) => {
    tbody.innerHTML += `
            <tr>
                <td>${item.codproducto}</td>
                <td>${item.nombre}</td>
                <td>${item.dosis}</td>
                <td>${item.frecuencia}</td>
                <td>${item.duracion}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${i})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
  });

  // Actualiza campo oculto
  document.getElementById("productos_json").value =
    JSON.stringify(listaProductos);
}

function eliminarProducto(index) {
  listaProductos.splice(index, 1);
  renderTabla();
}

function limpiarCampos() {
  document.getElementById("producto").value = "";
  document.getElementById("dosis").value = "";
  document.getElementById("frecuencia").value = "";
  document.getElementById("duracion").value = "";
}


