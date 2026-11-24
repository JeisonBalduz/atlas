import { Solicitud } from '/js/class/Solicitud.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { Modal } from '/js/components/modal/Modal.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

let graficoArchivos = null;

const renderizarGraficoEstadistico = (serieDatos) => {
  const nodoChart = document.querySelector('#chart');
  if (!nodoChart || typeof window.ApexCharts === 'undefined') return;

  let etiquetas = Array.isArray(serieDatos?.etiquetas) ? serieDatos.etiquetas : [];
  let valores = Array.isArray(serieDatos?.valores) ? serieDatos.valores : [];

  if (!etiquetas.length && nodoChart.dataset.serie) {
    try {
      const inicial = JSON.parse(nodoChart.dataset.serie);
      etiquetas = Array.isArray(inicial?.etiquetas) ? inicial.etiquetas : [];
      valores = Array.isArray(inicial?.valores) ? inicial.valores : [];
    } catch {
      etiquetas = [];
      valores = [];
    }
  }

  if (graficoArchivos) {
    graficoArchivos.updateOptions({ xaxis: { categories: etiquetas } });
    graficoArchivos.updateSeries([{ name: 'Documentos cargados', data: valores }]);
    return;
  }

  graficoArchivos = new window.ApexCharts(nodoChart, {
    series: [{ name: 'Documentos cargados', data: valores }],
    chart: {
      type: 'area',
      height: 290,
      fontFamily: 'Inter, sans-serif',
      toolbar: { show: false },
      zoom: { enabled: false },
    },
    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
    dataLabels: { enabled: false },
    colors: ['#052659'],
    stroke: { curve: 'smooth', width: 2.5 },
    fill: {
      type: 'gradient',
      gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.02 },
    },
    xaxis: {
      categories: etiquetas,
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: { style: { colors: '#71717a', fontSize: '11px' } },
    },
    yaxis: {
      min: 0,
      forceNiceScale: true,
      labels: {
        formatter: (v) => String(Math.round(v)),
        style: { colors: '#71717a', fontSize: '11px' },
      },
    },
  });

  graficoArchivos.render();
};

const actualizarMetricasDom = (m) => {
  if (!m) return;
  const asignar = (id, valor) => {
    const el = document.getElementById(id);
    if (el && valor !== undefined) el.textContent = String(valor);
  };

  asignar('metrica-vacaciones', m.vacaciones);
  asignar('metrica-permisos', m.permisos);
  asignar('metrica-reposos', m.reposos);
  asignar('metrica-beneficios', m.beneficios);
  asignar('metrica-expedientes', m.expedientes);
  asignar('metrica-familiares', m.familiares);
  asignar('metrica-nomina-activa', m.nomina_activa);
  asignar('metrica-usuarios-activos', m.usuarios_activos);
  asignar('metrica-asistencia-porcentaje', m.porcentaje_asistencia_hoy);
  asignar('metrica-asistencia-detalle', `${m.presentes_hoy ?? 0} / ${m.nomina_activa ?? 0} presentes`);
  asignar('metrica-ausencias-hoy', `${m.ausencias_hoy ?? 0} ausencias activas hoy`);
};

const obtenerIniciales = (nombre) => {
  const partes = String(nombre || 'U').trim().split(/\s+/).filter(Boolean);
  return partes.slice(0, 2).map((p) => p[0]?.toUpperCase() || '').join('') || 'U';
};

const renderFilaUsuario = (u) => {
  const activo = Boolean(u.activo);
  const badgeHtml = activo
    ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-800 border border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>`
    : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-800 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactivo</span>`;

  return `
    <tr class="transition-colors hover:bg-zinc-50/50">
      <td class="flex items-center gap-3 px-5 sm:px-6 py-3.5 sm:py-4 font-medium text-zinc-900 whitespace-nowrap">
        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-marca-50 text-xs font-bold text-marca border border-marca-100">
          ${obtenerIniciales(u.nombre_mostrar)}
        </span>
        <div>
          <span class="block text-sm font-semibold text-zinc-900">${u.nombre_mostrar || u.nombre_usuario}</span>
          <span class="block text-xs text-zinc-400">@${u.nombre_usuario}</span>
        </div>
      </td>
      <td class="px-5 sm:px-6 py-3.5 sm:py-4 whitespace-nowrap">${crearBadge(u.rol || 'Usuario', 'marca')}</td>
      <td class="px-5 sm:px-6 py-3.5 sm:py-4 whitespace-nowrap text-zinc-600">${u.departamento || 'Sin asignar'}</td>
      <td class="px-5 sm:px-6 py-3.5 sm:py-4 whitespace-nowrap">${badgeHtml}</td>
    </tr>
  `;
};

const renderItemActividad = (item) => `
  <div class="flex items-start gap-4">
    <div class="w-10 h-10 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5 text-zinc-700">
      <i data-lucide="${item.icono || 'activity'}" class="w-4 h-4"></i>
    </div>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-bold text-zinc-900">${item.titulo}</p>
      <p class="text-xs text-zinc-500 font-medium mt-0.5">${item.descripcion}</p>
      <span class="text-[11px] font-semibold text-zinc-400 mt-1 block">${item.fecha ? formatearFecha(item.fecha) : 'Reciente'}</span>
    </div>
  </div>
`;

document.addEventListener('DOMContentLoaded', async () => {
  renderizarGraficoEstadistico(null);

  const cuerpoUsuarios = document.getElementById('inicio-tabla-usuarios');
  const listaActividad = document.getElementById('inicio-lista-actividad');
  const btnBitacora = document.getElementById('btn-ver-bitacora');
  const btnTodosUsuarios = document.getElementById('btn-ver-usuarios');
  const nodoDialogo = document.getElementById('modal-inicio-detalle');
  const modalInicio = nodoDialogo ? new Modal(nodoDialogo) : null;

  const res = await Solicitud.get('/api/inicio/datos');
  if (!res.ok || !res.data?.ok) return;

  const metricas = res.data.data?.metricas;
  const serieArchivos = res.data.data?.serie_archivos;
  const usuarios = Array.isArray(res.data.data?.usuarios) ? res.data.data.usuarios : [];
  const actividades = Array.isArray(res.data.data?.actividades) ? res.data.data.actividades : [];

  actualizarMetricasDom(metricas);
  if (serieArchivos) renderizarGraficoEstadistico(serieArchivos);

  if (cuerpoUsuarios) {
    cuerpoUsuarios.innerHTML = usuarios.length
      ? usuarios.slice(0, 6).map(renderFilaUsuario).join('')
      : '<tr><td colspan="4" class="px-6 py-8 text-center text-sm text-zinc-400">No hay usuarios registrados en la base de datos.</td></tr>';
  }

  if (listaActividad) {
    listaActividad.innerHTML = actividades.length
      ? actividades.slice(0, 4).map(renderItemActividad).join('')
      : '<p class="text-sm text-zinc-400">No se registran movimientos recientes en la base de datos.</p>';
    window.lucide?.createIcons();
  }

  btnBitacora?.addEventListener('click', () => {
    if (!modalInicio) return;
    modalInicio.abrir({
      titulo: 'Bitácora reciente del sistema',
      subtitulo: 'Últimos movimientos registrados en las tablas operativas y de auditoría.',
      contenidoHtml: `<div class="space-y-4">${actividades.length ? actividades.map(renderItemActividad).join('') : '<p class="text-sm text-zinc-500">Sin registros recientes.</p>'}</div>`,
    });
  });

  btnTodosUsuarios?.addEventListener('click', () => {
    if (!modalInicio) return;
    modalInicio.abrir({
      titulo: 'Usuarios registrados en Atlas',
      subtitulo: `${usuarios.length} cuentas consultadas desde public.vw_usuarios.`,
      contenidoHtml: `
        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
              <tr>
                <th class="px-5 py-3">Usuario</th>
                <th class="px-5 py-3">Rol</th>
                <th class="px-5 py-3">Departamento</th>
                <th class="px-5 py-3">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">${usuarios.map(renderFilaUsuario).join('')}</tbody>
          </table>
        </div>
      `,
    });
  });
});
