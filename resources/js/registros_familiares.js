/**
 * registros_familiares.js
 * Listado interactivo de carga familiar con datos de prueba en memoria.
 * Guía estructural: FamiliaresModel, EmpleadosModel, PersonasModel, TipoParentescoModel.
 */

import { TablaInteractiva } from "/js/class/TablaInteractiva.js";
import { ControladorModal } from "/js/class/ControladorModal.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
let familiaresPrueba = [
  {
    familia_id: 1,
    empleado_id: 1,
    persona_id: 201,
    cedula_titular: "V-21456789",
    empleado_titular: "Carlos Eduardo Mendoza Ríos",
    cedula_familiar: "V-31245678",
    nombre_familiar: "Sofía Valentina Mendoza Herrera",
    parentesco_id: 1,
    parentesco: "Hijo(a)",
    fecha_nacimiento: "2014-06-18",
    edad: 12,
    genero: "Femenino",
    tomo: 4,
    folio: 128,
  },
  {
    familia_id: 2,
    empleado_id: 1,
    persona_id: 202,
    cedula_titular: "V-21456789",
    empleado_titular: "Carlos Eduardo Mendoza Ríos",
    cedula_familiar: "V-22334455",
    nombre_familiar: "Laura Beatriz Herrera Gómez",
    parentesco_id: 2,
    parentesco: "Cónyuge",
    fecha_nacimiento: "1991-11-04",
    edad: 35,
    genero: "Femenino",
    tomo: 2,
    folio: 45,
  },
  {
    familia_id: 3,
    empleado_id: 2,
    persona_id: 203,
    cedula_titular: "V-19876543",
    empleado_titular: "María Fernanda López Silva",
    cedula_familiar: "V-6123456",
    nombre_familiar: "Carmen Teresa Silva de López",
    parentesco_id: 3,
    parentesco: "Madre",
    fecha_nacimiento: "1962-03-22",
    edad: 64,
    genero: "Femenino",
    tomo: 1,
    folio: 19,
  },
  {
    familia_id: 4,
    empleado_id: 3,
    persona_id: 204,
    cedula_titular: "V-17654321",
    empleado_titular: "José Gregorio Ramírez Pérez",
    cedula_familiar: "V-33445566",
    nombre_familiar: "Diego Alejandro Ramírez Soto",
    parentesco_id: 1,
    parentesco: "Hijo(a)",
    fecha_nacimiento: "2018-09-10",
    edad: 8,
    genero: "Masculino",
    tomo: 7,
    folio: 210,
  },
];

document.addEventListener("DOMContentLoaded", () => {
  const modalDetalle = new ControladorModal("#modal-detalle-familiar");
  const modalEliminar = new ControladorModal("#modal-eliminar-familiar");

  const tabla = new TablaInteractiva({
    contenedor: "#tabla-familiares",
    datos: familiaresPrueba,
    claveId: "familia_id",
    porPagina: 10,
    columnas: [
      { clave: "cedula_titular", etiqueta: "Cédula Titular", ordenable: true },
      { clave: "empleado_titular", etiqueta: "Empleado Titular", ordenable: true },
      { clave: "cedula_familiar", etiqueta: "Cédula Familiar", ordenable: true },
      { clave: "nombre_familiar", etiqueta: "Nombre del Familiar", ordenable: true },
      {
        clave: "parentesco",
        etiqueta: "Parentesco",
        ordenable: true,
        renderizar: (fila) => TablaInteractiva.crearBadgeEstado(fila.parentesco, "sky"),
      },
      {
        clave: "edad",
        etiqueta: "Edad",
        ordenable: true,
        alineacion: "center",
        renderizar: (fila) => `${TablaInteractiva.escaparHtml(fila.edad)} años`,
      },
      { clave: "genero", etiqueta: "Género", ordenable: true, alineacion: "center" },
      {
        clave: "tomo_folio",
        etiqueta: "Tomo / Folio",
        alineacion: "center",
        renderizar: (fila) =>
          `<span class="font-medium text-zinc-900">T-${TablaInteractiva.escaparHtml(fila.tomo)} / F-${TablaInteractiva.escaparHtml(fila.folio)}</span>`,
      },
      {
        clave: "acciones",
        etiqueta: "Acciones",
        alineacion: "right",
        renderizar: (fila) =>
          TablaInteractiva.crearBotonesAccion(fila.familia_id, {
            hrefEditar: `/editar_familiar?id=${fila.familia_id}`,
          }),
      },
    ],
    alAccionar: (accion, _id, registro) => {
      if (!registro) return;

      if (accion === "mostrar") {
        modalDetalle.abrir({
          titulo: registro.nombre_familiar,
          subtitulo: `${registro.parentesco} de ${registro.empleado_titular}`,
          contenidoHtml: `
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Empleado titular</dt>
                <dd class="mt-1 font-semibold text-zinc-900">${TablaInteractiva.escaparHtml(registro.empleado_titular)}</dd>
                <dd class="text-xs text-zinc-500">${TablaInteractiva.escaparHtml(registro.cedula_titular)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Cédula del familiar</dt>
                <dd class="mt-1 font-semibold text-zinc-900">${TablaInteractiva.escaparHtml(registro.cedula_familiar)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Fecha de nacimiento y edad</dt>
                <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.fecha_nacimiento)} (${registro.edad} años)</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Datos de acta</dt>
                <dd class="mt-1 font-medium text-zinc-900">Tomo ${registro.tomo} · Folio ${registro.folio}</dd>
              </div>
            </dl>
          `,
        });
      }

      if (accion === "eliminar") {
        modalEliminar.abrir({
          titulo: "Eliminar carga familiar",
          subtitulo: `${registro.parentesco} · ${registro.cedula_familiar}`,
          contenidoHtml: `
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
              ¿Deseas eliminar el registro familiar de <strong>${TablaInteractiva.escaparHtml(registro.nombre_familiar)}</strong>?
            </div>
          `,
          textoConfirmar: "Eliminar",
          alConfirmar: () => {
            // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
            familiaresPrueba = familiaresPrueba.filter((item) => item.familia_id !== registro.familia_id);
            tabla.actualizarDatos(familiaresPrueba);
            mostrarToast("success", "Registro familiar eliminado del listado.");
          },
        });
      }
    },
  });

  document.getElementById("filtro-parentesco")?.addEventListener("change", (evento) => {
    tabla.establecerFiltro("parentesco", evento.target.value);
  });
});
