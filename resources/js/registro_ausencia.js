/**
 * registro_ausencia.js
 * Registro dinámico de Permisos y Reposos Médicos con tabla unificada y filtro rápido.
 * Guía estructural: AusenciaJustificadaModel, DetallePermisoModel, DetalleReposoModel,
 * TipoPermisosModel, TipoRepososModel, TipoInstitucionEmisoraModel.
 */

import { TablaInteractiva } from "/js/class/TablaInteractiva.js";
import { ControladorModal } from "/js/class/ControladorModal.js";
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from "/js/components/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
const empleadosPrueba = [
  { empleado_id: 1, cedula: "V-21456789", nombre: "Carlos Eduardo Mendoza Ríos" },
  { empleado_id: 2, cedula: "V-19876543", nombre: "María Fernanda López Silva" },
  { empleado_id: 3, cedula: "V-17654321", nombre: "José Gregorio Ramírez Pérez" },
  { empleado_id: 4, cedula: "V-24112233", nombre: "Ana Gabriela Torres Morales" },
];

const tiposPermisoPrueba = {
  1: "Diligencia Personal",
  2: "Estudios / Examen Académico",
  3: "Cita Médica",
  4: "Matrimonio / Nacimiento",
};

const tiposReposoPrueba = {
  1: "Enfermedad Común",
  2: "Accidente Laboral",
  3: "Pre / Post Natal",
  4: "Post-Operatorio",
};

const institucionesEmisorasPrueba = {
  1: "IVSS",
  2: "IPASME",
  3: "Clínica Privada (Convalidado)",
  4: "Hospital Público",
};

let ausenciasPrueba = [
  {
    ausencia_id: 1,
    empleado_id: 1,
    cedula: "V-21456789",
    empleado: "Carlos Eduardo Mendoza Ríos",
    tipo_ausencia: 1,
    tipo_texto: "Permiso",
    detalle: "Diligencia Personal · Renovación de pasaporte",
    fecha_inicio: "2026-11-12",
    fecha_fin: "2026-11-12",
    dias_ausencia: 1,
    extra: { tipo_catalogo: "Diligencia Personal", remunerable: true, motivo: "Renovación de pasaporte" },
  },
  {
    ausencia_id: 2,
    empleado_id: 4,
    cedula: "V-24112233",
    empleado: "Ana Gabriela Torres Morales",
    tipo_ausencia: 2,
    tipo_texto: "Reposo",
    detalle: "Enfermedad Común · Síndrome viral agudo",
    fecha_inicio: "2026-11-15",
    fecha_fin: "2026-11-19",
    dias_ausencia: 5,
    extra: {
      tipo_catalogo: "Enfermedad Común",
      institucion: "IVSS",
      doctor: "Dra. Patricia Gómez",
      descripcion: "Síndrome viral agudo con indicación de reposo absoluto.",
    },
  },
  {
    ausencia_id: 3,
    empleado_id: 2,
    cedula: "V-19876543",
    empleado: "María Fernanda López Silva",
    tipo_ausencia: 1,
    tipo_texto: "Permiso",
    detalle: "Estudios / Examen Académico · Defensa de tesis",
    fecha_inicio: "2026-11-20",
    fecha_fin: "2026-11-21",
    dias_ausencia: 2,
    extra: { tipo_catalogo: "Estudios / Examen Académico", remunerable: true, motivo: "Defensa de tesis de maestría" },
  },
];

const calcularDias = (inicio, fin) => {
  if (!inicio || !fin) return 0;
  const f1 = new Date(`${inicio}T00:00:00`);
  const f2 = new Date(`${fin}T00:00:00`);
  if (Number.isNaN(f1.getTime()) || Number.isNaN(f2.getTime()) || f2 < f1) return 0;
  return Math.round((f2 - f1) / (1000 * 60 * 60 * 24)) + 1;
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal("#modal-detalle-ausencia");

  actualizarOpcionesSelect(
    "#empleado_id",
    empleadosPrueba.map((emp) => ({ valor: emp.empleado_id, etiqueta: emp.nombre, subetiqueta: emp.cedula }))
  );
  actualizarOpcionesSelect("#tppermiso_id", tiposPermisoPrueba);
  actualizarOpcionesSelect("#tpreposo_id", tiposReposoPrueba);
  actualizarOpcionesSelect("#tipo_institucion_emisora_id", institucionesEmisorasPrueba);

  // Alternar campos Permiso vs Reposo
  const bloquePermiso = document.getElementById("bloque-campos-permiso");
  const bloqueReposo = document.getElementById("bloque-campos-reposo");
  const radiosTipo = document.querySelectorAll('input[name="tipo_ausencia"]');

  const alternarBloques = () => {
    const tipo = document.querySelector('input[name="tipo_ausencia"]:checked')?.value || "1";
    const esPermiso = tipo === "1";
    bloquePermiso?.classList.toggle("hidden", !esPermiso);
    bloqueReposo?.classList.toggle("hidden", esPermiso);
  };
  radiosTipo.forEach((radio) => radio.addEventListener("change", alternarBloques));

  // Cálculo automático de días
  const inputInicio = document.getElementById("fecha_inicio");
  const inputFin = document.getElementById("fecha_fin");
  const inputDias = document.getElementById("dias_ausencia");

  const actualizarDias = () => {
    const dias = calcularDias(inputInicio?.value, inputFin?.value);
    if (inputDias) inputDias.value = dias > 0 ? String(dias) : "";
  };
  inputInicio?.addEventListener("change", actualizarDias);
  inputFin?.addEventListener("change", actualizarDias);

  // Tabla unificada
  const tabla = new TablaInteractiva({
    contenedor: "#tabla-ausencias",
    datos: ausenciasPrueba,
    claveId: "ausencia_id",
    porPagina: 10,
    columnas: [
      { clave: "cedula", etiqueta: "Cédula", ordenable: true },
      { clave: "empleado", etiqueta: "Empleado", ordenable: true },
      {
        clave: "tipo_texto",
        etiqueta: "Tipo",
        ordenable: true,
        alineacion: "center",
        renderizar: (fila) =>
          TablaInteractiva.crearBadgeEstado(fila.tipo_texto, fila.tipo_texto === "Permiso" ? "sky" : "yellow"),
      },
      { clave: "detalle", etiqueta: "Detalle / Motivo" },
      { clave: "fecha_inicio", etiqueta: "Inicio", ordenable: true },
      { clave: "fecha_fin", etiqueta: "Fin", ordenable: true },
      { clave: "dias_ausencia", etiqueta: "Días", ordenable: true, alineacion: "center" },
      {
        clave: "acciones",
        etiqueta: "Acciones",
        alineacion: "right",
        renderizar: (fila) =>
          TablaInteractiva.crearBotonesAccion(fila.ausencia_id, {
            editar: false,
            eliminar: false,
          }),
      },
    ],
    alAccionar: (accion, _id, registro) => {
      if (accion !== "mostrar" || !registro) return;

      const detalleEspecifico =
        registro.tipo_texto === "Permiso"
          ? `
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Tipo de permiso</dt>
              <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.extra.tipo_catalogo)}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Remunerable</dt>
              <dd class="mt-1">${TablaInteractiva.crearBadgeEstado(registro.extra.remunerable ? "Sí" : "No", registro.extra.remunerable ? "green" : "red")}</dd>
            </div>
            <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Motivo</dt>
              <dd class="mt-1 text-zinc-900">${TablaInteractiva.escaparHtml(registro.extra.motivo)}</dd>
            </div>
          `
          : `
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Tipo de reposo</dt>
              <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.extra.tipo_catalogo)}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Institución emisora</dt>
              <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.extra.institucion)}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Médico tratante</dt>
              <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.extra.doctor || "No especificado")}</dd>
            </div>
            <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Descripción</dt>
              <dd class="mt-1 text-zinc-900">${TablaInteractiva.escaparHtml(registro.extra.descripcion)}</dd>
            </div>
          `;

      modalDetalle.abrir({
        titulo: `${registro.tipo_texto} · ${registro.empleado}`,
        subtitulo: `${registro.fecha_inicio} al ${registro.fecha_fin} (${registro.dias_ausencia} días)`,
        contenidoHtml: `<dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">${detalleEspecifico}</dl>`,
      });
    },
  });

  // Botones de filtro rápido
  const botonesFiltro = document.querySelectorAll(".btn-filtro-ausencia");
  botonesFiltro.forEach((boton) => {
    boton.addEventListener("click", () => {
      const filtro = boton.dataset.filtroTipo;
      botonesFiltro.forEach((btn) => {
        const activo = btn === boton;
        btn.classList.toggle("bg-marca", activo);
        btn.classList.toggle("text-white", activo);
        btn.classList.toggle("text-zinc-500", !activo);
      });
      tabla.establecerFiltro("tipo_texto", filtro);
    });
  });

  // Registro simulado
  const formulario = document.getElementById("formulario-ausencia");
  formulario?.addEventListener("submit", (evento) => {
    evento.preventDefault();

    const tipo = Number(document.querySelector('input[name="tipo_ausencia"]:checked')?.value || 1);
    const empleadoId = document.getElementById("empleado_id")?.value;
    const emp = empleadosPrueba.find((e) => String(e.empleado_id) === String(empleadoId));
    const dias = Number(inputDias?.value || 0);

    if (!emp || !inputInicio?.value || !inputFin?.value || dias <= 0) {
      mostrarToast("error", "Completa el empleado y el rango de fechas de la ausencia.");
      return;
    }

    // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
    let nuevoRegistro;
    if (tipo === 1) {
      const tpId = document.getElementById("tppermiso_id")?.value;
      const motivo = document.getElementById("motivo")?.value.trim();
      if (!tpId || !motivo) {
        mostrarToast("error", "Selecciona el tipo de permiso e indica el motivo.");
        return;
      }
      const nombrePermiso = tiposPermisoPrueba[tpId] || "Permiso";
      nuevoRegistro = {
        ausencia_id: ausenciasPrueba.length + 1,
        empleado_id: emp.empleado_id,
        cedula: emp.cedula,
        empleado: emp.nombre,
        tipo_ausencia: 1,
        tipo_texto: "Permiso",
        detalle: `${nombrePermiso} · ${motivo}`,
        fecha_inicio: inputInicio.value,
        fecha_fin: inputFin.value,
        dias_ausencia: dias,
        extra: {
          tipo_catalogo: nombrePermiso,
          remunerable: Boolean(document.getElementById("remunerable")?.checked),
          motivo,
        },
      };
    } else {
      const trId = document.getElementById("tpreposo_id")?.value;
      const instId = document.getElementById("tipo_institucion_emisora_id")?.value;
      const descripcion = document.getElementById("descripcion")?.value.trim();
      const doctor = document.getElementById("doctor")?.value.trim();
      if (!trId || !descripcion) {
        mostrarToast("error", "Selecciona el tipo de reposo e indica la descripción médica.");
        return;
      }
      const nombreReposo = tiposReposoPrueba[trId] || "Reposo";
      nuevoRegistro = {
        ausencia_id: ausenciasPrueba.length + 1,
        empleado_id: emp.empleado_id,
        cedula: emp.cedula,
        empleado: emp.nombre,
        tipo_ausencia: 2,
        tipo_texto: "Reposo",
        detalle: `${nombreReposo} · ${descripcion}`,
        fecha_inicio: inputInicio.value,
        fecha_fin: inputFin.value,
        dias_ausencia: dias,
        extra: {
          tipo_catalogo: nombreReposo,
          institucion: institucionesEmisorasPrueba[instId] || "No especificada",
          doctor: doctor || "No especificado",
          descripcion,
        },
      };
    }

    ausenciasPrueba = [nuevoRegistro, ...ausenciasPrueba];
    tabla.actualizarDatos(ausenciasPrueba);
    formulario.reset();
    alternarBloques();
    mostrarToast("success", `${nuevoRegistro.tipo_texto} registrado para ${emp.nombre}.`);
  });
});
