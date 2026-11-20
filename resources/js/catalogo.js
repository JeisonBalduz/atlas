/**
 * catalogo.js
 * Gestor unificado para los 6 catálogos base sin recargar la página.
 * Guía estructural: EstatusModel, DependenciasModel, DepartamentosModel, CargoModel, NivelAcademicosModel, CarreraModel.
 */

import { TablaInteractiva } from "/js/class/TablaInteractiva.js";
import { ControladorModal } from "/js/class/ControladorModal.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
const estadosPrueba = {
  1: "Distrito Capital",
  2: "Miranda",
  3: "Aragua",
  4: "Carabobo",
  5: "Lara",
  6: "Zulia",
};

const catalogosPrueba = {
  estatus: {
    titulo: "Catálogo de Estatus",
    claveId: "estatus_id",
    campos: [
      { nombre: "estatus", etiqueta: "Nombre del estatus", tipo: "text", requerido: true, maximo: 50 },
      { nombre: "activo", etiqueta: "Registro activo", tipo: "checkbox", requerido: false },
    ],
    datos: [
      { estatus_id: 1, estatus: "Activo", activo: true },
      { estatus_id: 2, estatus: "Vacaciones", activo: true },
      { estatus_id: 3, estatus: "Reposo Médico", activo: true },
      { estatus_id: 4, estatus: "Jubilado", activo: false },
    ],
  },
  dependencias: {
    titulo: "Catálogo de Dependencias",
    claveId: "dependencia_id",
    campos: [
      { nombre: "dependencia", etiqueta: "Nombre de la dependencia", tipo: "text", requerido: true, maximo: 100 },
      { nombre: "codigo", etiqueta: "Código", tipo: "text", requerido: true, maximo: 20 },
      { nombre: "estado_id", etiqueta: "Estado", tipo: "select", opciones: estadosPrueba, requerido: true },
      { nombre: "activo", etiqueta: "Registro activo", tipo: "checkbox", requerido: false },
    ],
    datos: [
      { dependencia_id: 1, dependencia: "Sede Central", codigo: "DEP-001", estado_id: 1, activo: true },
      { dependencia_id: 2, dependencia: "CFS La Romana", codigo: "DEP-002", estado_id: 3, activo: true },
      { dependencia_id: 3, dependencia: "CFS La Victoria", codigo: "DEP-003", estado_id: 3, activo: true },
    ],
  },
  departamentos: {
    titulo: "Catálogo de Departamentos",
    claveId: "departamento_id",
    campos: [
      { nombre: "departamento", etiqueta: "Nombre del departamento", tipo: "text", requerido: true, maximo: 100 },
      { nombre: "activo", etiqueta: "Registro activo", tipo: "checkbox", requerido: false },
    ],
    datos: [
      { departamento_id: 1, departamento: "Talento Humano", activo: true },
      { departamento_id: 2, departamento: "Tecnología", activo: true },
      { departamento_id: 3, departamento: "Administración", activo: true },
      { departamento_id: 4, departamento: "Formación", activo: true },
    ],
  },
  cargo: {
    titulo: "Catálogo de Cargos",
    claveId: "cargo_id",
    campos: [
      { nombre: "cargo", etiqueta: "Nombre del cargo", tipo: "text", requerido: true, maximo: 50 },
      { nombre: "activo", etiqueta: "Registro activo", tipo: "checkbox", requerido: false },
    ],
    datos: [
      { cargo_id: 1, cargo: "Analista de Talento Humano", activo: true },
      { cargo_id: 2, cargo: "Coordinador", activo: true },
      { cargo_id: 3, cargo: "Facilitador Técnico", activo: true },
      { cargo_id: 4, cargo: "Jefe de División", activo: true },
    ],
  },
  nivel_academicos: {
    titulo: "Catálogo de Niveles Académicos",
    claveId: "nivel_academico_id",
    campos: [
      { nombre: "nivel_academico", etiqueta: "Nivel académico", tipo: "text", requerido: true, maximo: 100 },
      { nombre: "jerarquia", etiqueta: "Jerarquía (número entero)", tipo: "number", requerido: true },
    ],
    datos: [
      { nivel_academico_id: 1, nivel_academico: "Bachiller", jerarquia: 1 },
      { nivel_academico_id: 2, nivel_academico: "TSU", jerarquia: 2 },
      { nivel_academico_id: 3, nivel_academico: "Universitario", jerarquia: 3 },
      { nivel_academico_id: 4, nivel_academico: "Especialización", jerarquia: 4 },
      { nivel_academico_id: 5, nivel_academico: "Maestría", jerarquia: 5 },
    ],
  },
  carrera: {
    titulo: "Catálogo de Carreras",
    claveId: "carrera_id",
    campos: [
      { nombre: "carrera", etiqueta: "Nombre de la carrera", tipo: "text", requerido: true, maximo: 150 },
    ],
    datos: [
      { carrera_id: 1, carrera: "Relaciones Industriales" },
      { carrera_id: 2, carrera: "Ingeniería en Informática" },
      { carrera_id: 3, carrera: "Administración de Empresas" },
      { carrera_id: 4, carrera: "Electromecánica" },
    ],
  },
};

const construirColumnasCatalogo = (claveCatalogo) => {
  const configuracion = catalogosPrueba[claveCatalogo];
  const columnas = configuracion.campos.map((campo) => {
    if (campo.tipo === "checkbox") {
      return {
        clave: campo.nombre,
        etiqueta: "Estado",
        ordenable: true,
        alineacion: "center",
        renderizar: (fila) =>
          TablaInteractiva.crearBadgeEstado(fila[campo.nombre] ? "Activo" : "Inactivo", fila[campo.nombre] ? "green" : "red"),
      };
    }

    if (campo.nombre === "estado_id") {
      return {
        clave: "estado_id",
        etiqueta: "Estado",
        ordenable: true,
        renderizar: (fila) => TablaInteractiva.escaparHtml(estadosPrueba[fila.estado_id] || "Sin asignar"),
      };
    }

    return {
      clave: campo.nombre,
      etiqueta: campo.etiqueta,
      ordenable: true,
    };
  });

  columnas.push({
    clave: "acciones",
    etiqueta: "Acciones",
    alineacion: "right",
    renderizar: (fila) =>
      TablaInteractiva.crearBotonesAccion(fila[configuracion.claveId], {
        mostrar: false,
      }),
  });

  return columnas;
};

const construirFormularioHtml = (claveCatalogo, registro = null) => {
  const { campos } = catalogosPrueba[claveCatalogo];

  const bloques = campos
    .map((campo) => {
      const valorActual = registro ? registro[campo.nombre] : campo.tipo === "checkbox" ? true : "";

      if (campo.tipo === "checkbox") {
        return `
          <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
            <span class="text-sm font-medium text-zinc-900">${TablaInteractiva.escaparHtml(campo.etiqueta)}</span>
            <input type="checkbox" name="${campo.nombre}" ${valorActual ? "checked" : ""}
              class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-2 focus:ring-marca-500" />
          </label>
        `;
      }

      if (campo.tipo === "select") {
        const opcionesHtml = Object.entries(campo.opciones || {})
          .map(
            ([id, nombre]) =>
              `<option value="${id}" ${String(valorActual) === String(id) ? "selected" : ""}>${TablaInteractiva.escaparHtml(nombre)}</option>`
          )
          .join("");

        return `
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-zinc-900">${TablaInteractiva.escaparHtml(campo.etiqueta)}</label>
            <select name="${campo.nombre}" ${campo.requerido ? "required" : ""}
              class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
              <option value="">Seleccionar...</option>
              ${opcionesHtml}
            </select>
          </div>
        `;
      }

      return `
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-zinc-900">${TablaInteractiva.escaparHtml(campo.etiqueta)}</label>
          <input type="${campo.tipo}" name="${campo.nombre}" value="${TablaInteractiva.escaparHtml(valorActual)}"
            ${campo.maximo ? `maxlength="${campo.maximo}"` : ""} ${campo.requerido ? "required" : ""}
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2" />
        </div>
      `;
    })
    .join("");

  return `<form id="form-modal-catalogo" class="space-y-4" novalidate>${bloques}</form>`;
};

document.addEventListener("DOMContentLoaded", () => {
  let catalogoActivo = "estatus";
  const modalFormulario = new ControladorModal("#modal-formulario-catalogo");
  const modalEliminar = new ControladorModal("#modal-eliminar-catalogo");
  const tituloTabla = document.querySelector("#tabla-catalogo h2");

  const tabla = new TablaInteractiva({
    contenedor: "#tabla-catalogo",
    datos: catalogosPrueba[catalogoActivo].datos,
    claveId: catalogosPrueba[catalogoActivo].claveId,
    columnas: construirColumnasCatalogo(catalogoActivo),
    alAccionar: (accion, _id, registro) => {
      if (!registro) return;

      if (accion === "editar") {
        abrirModalRegistro(registro);
      }

      if (accion === "eliminar") {
        const config = catalogosPrueba[catalogoActivo];
        modalEliminar.abrir({
          titulo: "Eliminar registro",
          subtitulo: config.titulo,
          contenidoHtml: `
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
              ¿Deseas eliminar el registro seleccionado del catálogo <strong>${TablaInteractiva.escaparHtml(config.titulo)}</strong>?
            </div>
          `,
          alConfirmar: () => {
            // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
            config.datos = config.datos.filter((item) => item[config.claveId] !== registro[config.claveId]);
            tabla.actualizarDatos(config.datos);
            mostrarToast("success", "Registro eliminado del catálogo.");
          },
        });
      }
    },
  });

  const abrirModalRegistro = (registroEditar = null) => {
    const config = catalogosPrueba[catalogoActivo];
    modalFormulario.abrir({
      titulo: registroEditar ? `Editar en ${config.titulo}` : `Nuevo en ${config.titulo}`,
      subtitulo: "Los cambios se reflejan en el arreglo de prueba en memoria.",
      contenidoHtml: construirFormularioHtml(catalogoActivo, registroEditar),
      textoConfirmar: registroEditar ? "Actualizar" : "Guardar",
      alConfirmar: (dialogo) => {
        const formulario = dialogo.querySelector("#form-modal-catalogo");
        if (!formulario) return false;

        const nuevoObjeto = {};
        let valido = true;

        config.campos.forEach((campo) => {
          const control = formulario.elements[campo.nombre];
          if (!control) return;

          if (campo.tipo === "checkbox") {
            nuevoObjeto[campo.nombre] = Boolean(control.checked);
          } else {
            const valorTexto = String(control.value || "").trim();
            if (campo.requerido && !valorTexto) {
              valido = false;
            }
            nuevoObjeto[campo.nombre] =
              campo.tipo === "number" || campo.nombre === "estado_id" ? Number(valorTexto) : valorTexto;
          }
        });

        if (!valido) {
          mostrarToast("error", "Completa todos los campos obligatorios.");
          return false;
        }

        // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
        if (registroEditar) {
          const id = registroEditar[config.claveId];
          config.datos = config.datos.map((item) =>
            item[config.claveId] === id ? { ...item, ...nuevoObjeto } : item
          );
          mostrarToast("success", "Registro actualizado correctamente.");
        } else {
          const nuevoId =
            config.datos.reduce((max, item) => Math.max(max, Number(item[config.claveId]) || 0), 0) + 1;
          config.datos.push({ [config.claveId]: nuevoId, ...nuevoObjeto });
          mostrarToast("success", "Registro agregado al catálogo.");
        }

        tabla.actualizarDatos(config.datos);
        return true;
      },
    });
  };

  // Cambio de pestaña de catálogo
  const botonesPestana = document.querySelectorAll(".pestana-catalogo");
  botonesPestana.forEach((boton) => {
    boton.addEventListener("click", () => {
      catalogoActivo = boton.dataset.catalogo;
      const config = catalogosPrueba[catalogoActivo];

      botonesPestana.forEach((btn) => {
        const activo = btn === boton;
        btn.setAttribute("aria-selected", String(activo));
        btn.classList.toggle("bg-marca", activo);
        btn.classList.toggle("text-white", activo);
        btn.classList.toggle("bg-white", !activo);
        btn.classList.toggle("text-zinc-500", !activo);
      });

      if (tituloTabla) {
        tituloTabla.textContent = config.titulo;
      }

      tabla.establecerColumnas(construirColumnasCatalogo(catalogoActivo), config.claveId);
      tabla.actualizarDatos(config.datos);
    });
  });

  // Inicializar encabezados del primer catálogo
  tabla.establecerColumnas(construirColumnasCatalogo(catalogoActivo), catalogosPrueba[catalogoActivo].claveId);

  document.getElementById("btn-nuevo-catalogo")?.addEventListener("click", () => abrirModalRegistro(null));
});
