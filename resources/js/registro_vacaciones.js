/**
 * registro_vacaciones.js
 * Formulario de solicitud de vacaciones con cálculo automático de días,
 * tabla de solicitudes con badges semánticos y modal de períodos e historial.
 * Guía estructural: SolicitudVacacionesModel, PeriodoVacacionesModel, DetalleVacacionesModel, HistorialVacacionesModel.
 */

import { TablaInteractiva } from "/js/class/TablaInteractiva.js";
import { ControladorModal } from "/js/class/ControladorModal.js";
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from "/js/components/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
const empleadosSaldoPrueba = [
  { empleado_id: 1, cedula: "V-21456789", nombre: "Carlos Eduardo Mendoza Ríos", dias_pendientes: 30, periodos: "2024–2025 (2 períodos)" },
  { empleado_id: 2, cedula: "V-19876543", nombre: "María Fernanda López Silva", dias_pendientes: 15, periodos: "2025 (1 período)" },
  { empleado_id: 3, cedula: "V-17654321", nombre: "José Gregorio Ramírez Pérez", dias_pendientes: 45, periodos: "2023–2025 (3 períodos)" },
  { empleado_id: 4, cedula: "V-24112233", nombre: "Ana Gabriela Torres Morales", dias_pendientes: 18, periodos: "2025 (1 período)" },
];

let solicitudesPrueba = [
  {
    solicitud_vacaciones_id: 1,
    empleado_id: 1,
    empleado: "Carlos Eduardo Mendoza Ríos",
    cedula: "V-21456789",
    dias_solicitado: 15,
    dias_afectados: 0,
    fecha_inicio: "2026-12-01",
    fecha_final: "2026-12-15",
    gerencia: false,
    estatus_texto: "Aprobada por Talento Humano",
    periodos_descontados: [{ periodo: 2024, dia_descontados: 15 }],
    historial: [
      { fecha_registro: "2026-11-10 09:15", estatus: "Pendiente", observacion: "Solicitud cargada por el trabajador." },
      { fecha_registro: "2026-11-12 14:30", estatus: "Aprobada por Talento Humano", observacion: "Saldo verificado en período 2024." },
    ],
  },
  {
    solicitud_vacaciones_id: 2,
    empleado_id: 2,
    empleado: "María Fernanda López Silva",
    cedula: "V-19876543",
    dias_solicitado: 10,
    dias_afectados: 0,
    fecha_inicio: "2026-11-24",
    fecha_final: "2026-12-03",
    gerencia: true,
    estatus_texto: "Pendiente",
    periodos_descontados: [{ periodo: 2025, dia_descontados: 10 }],
    historial: [
      { fecha_registro: "2026-11-18 10:00", estatus: "Pendiente", observacion: "En espera de revisión por Talento Humano." },
    ],
  },
  {
    solicitud_vacaciones_id: 3,
    empleado_id: 3,
    empleado: "José Gregorio Ramírez Pérez",
    cedula: "V-17654321",
    dias_solicitado: 20,
    dias_afectados: 4,
    fecha_inicio: "2026-10-05",
    fecha_final: "2026-10-24",
    gerencia: false,
    estatus_texto: "Reposo",
    periodos_descontados: [
      { periodo: 2023, dia_descontados: 15 },
      { periodo: 2024, dia_descontados: 5 },
    ],
    historial: [
      { fecha_registro: "2026-09-20 08:40", estatus: "Aprobada por Gerencia", observacion: "Aprobada con firma de gerencia." },
      { fecha_registro: "2026-10-14 11:20", estatus: "Reposo", observacion: "Interrumpida por reposo médico (4 días afectados)." },
    ],
  },
  {
    solicitud_vacaciones_id: 4,
    empleado_id: 4,
    empleado: "Ana Gabriela Torres Morales",
    cedula: "V-24112233",
    dias_solicitado: 12,
    dias_afectados: 0,
    fecha_inicio: "2026-08-01",
    fecha_final: "2026-08-12",
    gerencia: false,
    estatus_texto: "Terminada",
    periodos_descontados: [{ periodo: 2025, dia_descontados: 12 }],
    historial: [
      { fecha_registro: "2026-07-15 09:00", estatus: "Aprobada por Talento Humano", observacion: "Aprobada sin observaciones." },
      { fecha_registro: "2026-08-13 08:00", estatus: "Terminada", observacion: "Trabajadora reincorporada." },
    ],
  },
];

const varianteEstatusVacaciones = (estatus) => {
  const mapa = {
    Pendiente: "yellow",
    "Aprobada por Talento Humano": "green",
    "Aprobada por Gerencia": "green",
    "Negada por Talento Humano": "red",
    Reposo: "sky",
    Terminada: "marca",
  };
  return mapa[estatus] || "neutro";
};

const calcularDiasEntreFechas = (inicio, fin) => {
  if (!inicio || !fin) return 0;
  const fInicio = new Date(`${inicio}T00:00:00`);
  const fFin = new Date(`${fin}T00:00:00`);
  if (Number.isNaN(fInicio.getTime()) || Number.isNaN(fFin.getTime()) || fFin < fInicio) {
    return 0;
  }
  const diferencia = Math.round((fFin - fInicio) / (1000 * 60 * 60 * 24)) + 1;
  return Math.max(0, diferencia);
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal("#modal-detalle-vacaciones");

  actualizarOpcionesSelect(
    "#empleado_id",
    empleadosSaldoPrueba.map((emp) => ({
      valor: emp.empleado_id,
      etiqueta: emp.nombre,
      subetiqueta: `${emp.dias_pendientes} días disp.`,
    }))
  );

  // Mostrar saldo acumulado del empleado seleccionado
  const selectEmpleado = document.getElementById("empleado_id");
  const tarjetaSaldo = document.getElementById("tarjeta-saldo-vacaciones");
  selectEmpleado?.addEventListener("change", () => {
    const emp = empleadosSaldoPrueba.find((e) => String(e.empleado_id) === String(selectEmpleado.value));
    if (!emp) {
      tarjetaSaldo?.classList.add("hidden");
      return;
    }
    document.getElementById("saldo-dias-pendientes").textContent = `${emp.dias_pendientes} días`;
    document.getElementById("saldo-periodos-activos").textContent = emp.periodos;
    tarjetaSaldo?.classList.remove("hidden");
  });

  // Cálculo automático de dias_solicitado
  const inputInicio = document.getElementById("fecha_inicio");
  const inputFin = document.getElementById("fecha_final");
  const inputDias = document.getElementById("dias_solicitado");

  const actualizarDias = () => {
    const dias = calcularDiasEntreFechas(inputInicio?.value, inputFin?.value);
    if (inputDias) inputDias.value = dias > 0 ? String(dias) : "";
  };

  inputInicio?.addEventListener("change", actualizarDias);
  inputFin?.addEventListener("change", actualizarDias);

  // Tabla de solicitudes
  const tabla = new TablaInteractiva({
    contenedor: "#tabla-vacaciones",
    datos: solicitudesPrueba,
    claveId: "solicitud_vacaciones_id",
    porPagina: 10,
    columnas: [
      { clave: "empleado", etiqueta: "Empleado", ordenable: true },
      { clave: "cedula", etiqueta: "Cédula", ordenable: true },
      {
        clave: "dias_solicitado",
        etiqueta: "Días",
        ordenable: true,
        alineacion: "center",
        renderizar: (fila) => `<span class="font-semibold text-zinc-900">${fila.dias_solicitado}</span>`,
      },
      { clave: "fecha_inicio", etiqueta: "Inicio", ordenable: true },
      { clave: "fecha_final", etiqueta: "Fin", ordenable: true },
      {
        clave: "estatus_texto",
        etiqueta: "Estatus",
        ordenable: true,
        alineacion: "center",
        renderizar: (fila) =>
          TablaInteractiva.crearBadgeEstado(fila.estatus_texto, varianteEstatusVacaciones(fila.estatus_texto)),
      },
      {
        clave: "acciones",
        etiqueta: "Acciones",
        alineacion: "right",
        renderizar: (fila) =>
          TablaInteractiva.crearBotonesAccion(fila.solicitud_vacaciones_id, {
            editar: false,
            eliminar: false,
          }),
      },
    ],
    alAccionar: (accion, _id, registro) => {
      if (accion !== "mostrar" || !registro) return;

      const filasPeriodos = (registro.periodos_descontados || [])
        .map(
          (p) => `
            <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm">
              <span class="font-medium text-zinc-900">Período ${p.periodo}</span>
              <span class="font-semibold text-marca-700">${p.dia_descontados} días descontados</span>
            </li>
          `
        )
        .join("");

      const filasHistorial = (registro.historial || [])
        .map(
          (h) => `
            <li class="rounded-lg border border-slate-200 bg-white p-3 text-sm">
              <div class="flex flex-wrap items-center justify-between gap-2">
                ${TablaInteractiva.crearBadgeEstado(h.estatus, varianteEstatusVacaciones(h.estatus))}
                <span class="text-xs text-zinc-500">${TablaInteractiva.escaparHtml(h.fecha_registro)}</span>
              </div>
              <p class="mt-1.5 text-xs text-zinc-700">${TablaInteractiva.escaparHtml(h.observacion)}</p>
            </li>
          `
        )
        .join("");

      modalDetalle.abrir({
        titulo: `Solicitud #${registro.solicitud_vacaciones_id} · ${registro.empleado}`,
        subtitulo: `${registro.fecha_inicio} al ${registro.fecha_final} (${registro.dias_solicitado} días solicitados)`,
        contenidoHtml: `
          <div class="space-y-5">
            <section>
              <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Períodos descontados</h3>
              <ul class="space-y-2">${filasPeriodos || '<li class="text-xs text-zinc-500">Sin desglose registrado.</li>'}</ul>
            </section>
            <section>
              <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Historial de movimientos</h3>
              <ul class="space-y-2">${filasHistorial || '<li class="text-xs text-zinc-500">Sin movimientos registrados.</li>'}</ul>
            </section>
          </div>
        `,
      });
    },
  });

  // Filtro por estatus
  document.getElementById("filtro-estatus-vacaciones")?.addEventListener("change", (evento) => {
    tabla.establecerFiltro("estatus_texto", evento.target.value);
  });

  // Envío simulado de solicitud
  const formulario = document.getElementById("formulario-vacaciones");
  formulario?.addEventListener("submit", (evento) => {
    evento.preventDefault();

    const emp = empleadosSaldoPrueba.find((e) => String(e.empleado_id) === String(selectEmpleado?.value));
    const dias = Number(inputDias?.value || 0);

    if (!emp || !inputInicio?.value || !inputFin?.value || dias <= 0) {
      mostrarToast("error", "Selecciona el empleado y un rango de fechas válido.");
      return;
    }

    if (dias > emp.dias_pendientes) {
      mostrarToast("error", `El empleado solo dispone de ${emp.dias_pendientes} días pendientes.`);
      return;
    }

    // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
    const nuevaSolicitud = {
      solicitud_vacaciones_id: solicitudesPrueba.length + 1,
      empleado_id: emp.empleado_id,
      empleado: emp.nombre,
      cedula: emp.cedula,
      dias_solicitado: dias,
      dias_afectados: 0,
      fecha_inicio: inputInicio.value,
      fecha_final: inputFin.value,
      gerencia: Boolean(document.getElementById("gerencia")?.checked),
      estatus_texto: "Pendiente",
      periodos_descontados: [{ periodo: 2025, dia_descontados: dias }],
      historial: [
        {
          fecha_registro: new Date().toISOString().slice(0, 16).replace("T", " "),
          estatus: "Pendiente",
          observacion: "Solicitud registrada desde la interfaz.",
        },
      ],
    };

    solicitudesPrueba = [nuevaSolicitud, ...solicitudesPrueba];
    tabla.actualizarDatos(solicitudesPrueba);
    formulario.reset();
    tarjetaSaldo?.classList.add("hidden");
    mostrarToast("success", `Solicitud de ${dias} días registrada para ${emp.nombre}.`);
  });
});
