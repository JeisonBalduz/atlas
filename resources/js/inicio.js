import { Solicitud } from '/js/class/Solicitud.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { Modal } from '/js/components/modal/Modal.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const inicializarGraficoEstadistico = () => {
  const nodoChart = document.querySelector('#chart');
  if (!nodoChart || typeof window.ApexCharts === 'undefined') return;

  const series = {
    prices: [
      142.5, 144.1, 143.8, 146.2, 148.0, 147.3, 149.9, 152.4, 151.0, 153.75,
      155.2, 154.9, 157.8, 160.1, 159.4, 162.0, 164.5, 163.2, 166.8, 168.9,
    ],
    dates: [
      '13 Nov 2025', '14 Nov 2025', '15 Nov 2025', '16 Nov 2025', '17 Nov 2025',
      '20 Nov 2025', '21 Nov 2025', '22 Nov 2025', '23 Nov 2025', '24 Nov 2025',
      '27 Nov 2025', '28 Nov 2025', '29 Nov 2025', '30 Nov 2025', '01 Dec 2025',
      '04 Dec 2025', '05 Dec 2025', '06 Dec 2025', '07 Dec 2025', '08 Dec 2025',
    ],
  };

  const chart = new window.ApexCharts(nodoChart, {
    series: [{ name: 'Archivos', data: series.prices }],
    chart: { type: 'area', height: 350, fontFamily: 'Inter, sans-serif', zoom: { enabled: false } },
    grid: { show: false },
    dataLabels: { enabled: false },
    colors: ['#052659'],
    stroke: { curve: 'straight' },
    title: { text: '', align: 'left' },
    labels: series.dates,
    xaxis: {
      type: 'datetime',
      labels: { show: false },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: { labels: { show: false } },
  });

  chart.render();
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
  inicializarGraficoEstadistico();

  const cuerpoUsuarios = document.getElementById('inicio-tabla-usuarios');
  const listaActividad = document.getElementById('inicio-lista-actividad');
  const btnBitacora = document.getElementById('btn-ver-bitacora');
  const btnTodosUsuarios = document.getElementById('btn-ver-usuarios');
  const nodoDialogo = document.getElementById('modal-inicio-detalle');
  const modalInicio = nodoDialogo ? new Modal(nodoDialogo) : null;

  const res = await Solicitud.get('/api/inicio/datos');
  if (!res.ok || !res.data?.ok) return;

  const usuarios = Array.isArray(res.data.data?.usuarios) ? res.data.data.usuarios : [];
  const actividades = Array.isArray(res.data.data?.actividades) ? res.data.data.actividades : [];

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
