/**
 * Mapas de clases compartidos de la paleta institucional Atlas para todos los componentes UI.
 */

export const ESTILOS_BADGE = {
  green: 'bg-green-400/20 text-green-800',
  red: 'bg-red-400/20 text-red-800',
  yellow: 'bg-yellow-400/20 text-yellow-900',
  sky: 'bg-sky-400/20 text-sky-800',
  marca: 'bg-marca-400/20 text-marca-800',
  neutro: 'bg-slate-400/20 text-zinc-900',
};

export const ESTILOS_ALERTA = {
  red: {
    caja: 'border-red-200 bg-red-50 text-red-800',
    icono: 'text-red-700',
  },
  green: {
    caja: 'border-green-200 bg-green-50 text-green-800',
    icono: 'text-green-700',
  },
  yellow: {
    caja: 'border-yellow-200 bg-yellow-50 text-yellow-900',
    icono: 'text-yellow-800',
  },
  sky: {
    caja: 'border-sky-200 bg-sky-50 text-sky-800',
    icono: 'text-sky-700',
  },
};

export const VARIANTES_BOTON_MODAL = {
  marca: ['bg-marca', 'hover:bg-marca-700', 'focus:ring-marca-500'],
  red: ['bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500'],
  green: ['bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500'],
};

export const VARIANTES_BOTON_ACCION = {
  zinc: 'bg-zinc-900 hover:bg-zinc-800 text-white focus-visible:outline-zinc-950',
  marca: 'bg-marca hover:bg-marca-700 text-white focus-visible:outline-marca',
  red: 'bg-red-600 hover:bg-red-700 text-white focus-visible:outline-red-600',
  green: 'bg-green-600 hover:bg-green-700 text-white focus-visible:outline-green-600',
  yellow: 'bg-yellow-400 hover:bg-yellow-500 text-zinc-950 focus-visible:outline-yellow-500',
};
