/**
 * registro_beneficios.js
 * Asignación y seguimiento de beneficios sobre datos ficticios en memoria.
 * Guía estructural: DetalleBeneficioModel, TipoBeneficiosModel, PersonasModel.
 */

import { TablaInteractiva } from "/js/class/TablaInteractiva.js";
import { ControladorModal } from "/js/class/ControladorModal.js";
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from "/js/components/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
const personasPrueba = [
  { persona_id: 101, cedula: "V-21456789", nombre: "Carlos Eduardo Mendoza Ríos", rol: "Empleado Titular" },
  { persona_id: 201, cedula: "V-31245678", nombre: "Sofía Valentina Mendoza Herrera", rol: "Hija (Carga Familiar)" },
  { persona_id: 102, cedula: "V-19876543", nombre: "María Fernanda López Silva", rol: "Empleada Titular" },
  { persona_id: 203, cedula: "V-6123456", nombre: "Carmen Teresa Silva de López", rol: "Madre (Carga Familiar)" },
];

const tiposBeneficiosPrueba = {
  1: "Beca Escolar",
  2: "Prima por Matrimonio",
  3: "Ayuda de Medicamentos",
  4: "Lentes Correctivos",
  5: "Juguetes Navideños",
};

let beneficiosPrueba = [
  {
    dtbeneficio_id: 1,
    persona_id: 201,
    beneficio_id: 1,
    cedula: "V-31245678",
    beneficiario: "Sofía Valentina Mendoza Herrera",
    rol_parentesco: "Hija (Carga Familiar)",
    tipo_beneficio: "Beca Escolar",
    fecha_solicitud: "2026-09-10",
    fecha_aprobada: "2026-09-18",
    estatus: "Aprobado",
  },
  {
    dtbeneficio_id: 2,
    persona_id: 102,
    beneficio_id: 4,
    cedula: "V-19876543",
    beneficiario: "María Fernanda López Silva",
    rol_parentesco: "Empleada Titular",
    tipo_beneficio: "Lentes Correctivos",
    fecha_solicitud: "2026-11-12",
    fecha_aprobada: "",
    estatus: "Pendiente",
  },
  {
    dtbeneficio_id: 3,
    persona_id: 203,
    beneficio_id: 3,
    cedula: "V-6123456",
    beneficiario: "Carmen Teresa Silva de López",
    rol_parentesco: "Madre (Carga Familiar)",
    tipo_beneficio: "Ayuda de Medicamentos",
    fecha_solicitud: "2026-11-05",
    fecha_aprobada: "2026-11-09",
    estatus: "Aprobado",
  },
];

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal("#modal-detalle-beneficio");

  actualizarOpcionesSelect(
    "#persona_id",
    personasPrueba.map((p) => ({ valor: p.persona_id, etiqueta: p.nombre, subetiqueta: p.rol }))
  );
  actualizarOpcionesSelect("#beneficio_id", tiposBeneficiosPrueba);

  const tabla = new TablaInteractiva({
    contenedor: "#tabla-beneficios",
    datos: beneficiosPrueba,
    claveId: "dtbeneficio_id",
    porPagina: 10,
    columnas: [
      { clave: "cedula", etiqueta: "Cédula", ordenable: true },
      { clave: "beneficiario", etiqueta: "Beneficiario", ordenable: true },
      { clave: "rol_parentesco", etiqueta: "Rol / Parentesco", ordenable: true },
      { clave: "tipo_beneficio", etiqueta: "Beneficio", ordenable: true },
      { clave: "fecha_solicitud", etiqueta: "Solicitud", ordenable: true },
      {
        clave: "fecha_aprobada",
        etiqueta: "Aprobación",
        ordenable: true,
        renderizar: (fila) => TablaInteractiva.escaparHtml(fila.fecha_aprobada || "Por aprobar"),
      },
      {
        clave: "estatus",
        etiqueta: "Estatus",
        ordenable: true,
        alineacion: "center",
        renderizar: (fila) =>
          TablaInteractiva.crearBadgeEstado(fila.estatus, fila.estatus === "Aprobado" ? "green" : "yellow"),
      },
      {
        clave: "acciones",
        etiqueta: "Acciones",
        alineacion: "right",
        renderizar: (fila) =>
          TablaInteractiva.crearBotonesAccion(fila.dtbeneficio_id, {
            editar: fila.estatus === "Pendiente",
            eliminar: false,
          }),
      },
    ],
    alAccionar: (accion, _id, registro) => {
      if (!registro) return;

      if (accion === "mostrar" || accion === "editar") {
        const esPendiente = registro.estatus === "Pendiente";
        const botonConfirmar = document.querySelector("#modal-detalle-beneficio [data-modal-confirmar]");
        botonConfirmar?.classList.toggle("hidden", !esPendiente);

        modalDetalle.abrir({
          titulo: registro.tipo_beneficio,
          subtitulo: `${registro.beneficiario} · ${registro.rol_parentesco}`,
          contenidoHtml: `
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Cédula</dt>
                <dd class="mt-1 font-semibold text-zinc-900">${TablaInteractiva.escaparHtml(registro.cedula)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Estatus actual</dt>
                <dd class="mt-1">${TablaInteractiva.crearBadgeEstado(registro.estatus, esPendiente ? "yellow" : "green")}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Fecha de solicitud</dt>
                <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.fecha_solicitud)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Fecha de aprobación</dt>
                <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.fecha_aprobada || "Pendiente")}</dd>
              </div>
            </dl>
          `,
          textoConfirmar: "Aprobar hoy",
          alConfirmar: esPendiente
            ? () => {
                // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
                const hoy = new Date().toISOString().slice(0, 10);
                beneficiosPrueba = beneficiosPrueba.map((item) =>
                  item.dtbeneficio_id === registro.dtbeneficio_id
                    ? { ...item, fecha_aprobada: hoy, estatus: "Aprobado" }
                    : item
                );
                tabla.actualizarDatos(beneficiosPrueba);
                mostrarToast("success", `Beneficio "${registro.tipo_beneficio}" aprobado para ${registro.beneficiario}.`);
              }
            : null,
        });
      }
    },
  });

  document.getElementById("filtro-estatus-beneficio")?.addEventListener("change", (evento) => {
    tabla.establecerFiltro("estatus", evento.target.value);
  });

  // Simulación de registro de beneficio
  const formulario = document.getElementById("formulario-beneficios");
  formulario?.addEventListener("submit", (evento) => {
    evento.preventDefault();

    const personaId = document.getElementById("persona_id")?.value;
    const beneficioId = document.getElementById("beneficio_id")?.value;
    const fechaSolicitud = document.getElementById("fecha_solicitud")?.value;
    const fechaAprobada = document.getElementById("fecha_aprobada")?.value || "";

    const persona = personasPrueba.find((p) => String(p.persona_id) === String(personaId));
    if (!persona || !beneficioId || !fechaSolicitud) {
      mostrarToast("error", "Selecciona beneficiario, tipo de beneficio y fecha de solicitud.");
      return;
    }

    // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
    const nuevo = {
      dtbeneficio_id: beneficiosPrueba.length + 1,
      persona_id: persona.persona_id,
      beneficio_id: Number(beneficioId),
      cedula: persona.cedula,
      beneficiario: persona.nombre,
      rol_parentesco: persona.rol,
      tipo_beneficio: tiposBeneficiosPrueba[beneficioId] || "Beneficio",
      fecha_solicitud: fechaSolicitud,
      fecha_aprobada: fechaAprobada,
      estatus: fechaAprobada ? "Aprobado" : "Pendiente",
    };

    beneficiosPrueba = [nuevo, ...beneficiosPrueba];
    tabla.actualizarDatos(beneficiosPrueba);
    formulario.reset();
    mostrarToast("success", `Beneficio "${nuevo.tipo_beneficio}" asignado a ${persona.nombre}.`);
  });
});
