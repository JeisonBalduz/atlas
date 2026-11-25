const VARIANTES_ITEM_DROPDOWN = {
  marca: 'text-marca hover:bg-marca-50',
  zinc: 'text-zinc-800 hover:bg-slate-100',
  sky: 'text-sky-800 hover:bg-sky-50',
  green: 'text-green-800 hover:bg-green-50',
  yellow: 'text-yellow-900 hover:bg-yellow-50',
  red: 'text-red-700 hover:bg-red-50',
};

const BADGE_ITEM_DROPDOWN = {
  marca: 'bg-marca-100 text-marca-800',
  zinc: 'bg-slate-200 text-zinc-800',
  sky: 'bg-sky-100 text-sky-800',
  green: 'bg-green-100 text-green-800',
  yellow: 'bg-yellow-100 text-yellow-900',
  red: 'bg-red-100 text-red-800',
};

let dropdownAbierto = null;
let eventosGlobalesRegistrados = false;

// Recibe el menú abierto; lo cierra y limpia su estado visual y de top-layer.
const cerrarDropdownActivo = () => {
  if (!dropdownAbierto) return;
  const { boton, menu, controlador } = dropdownAbierto;
  dropdownAbierto = null;
  controlador?.abort();

  if (typeof menu.hidePopover === 'function' && menu.matches?.(':popover-open')) {
    try {
      menu.hidePopover();
    } catch {
      // Ignorar si ya estaba cerrado
    }
  }
  menu.classList.add('hidden');
  boton.setAttribute('aria-expanded', 'false');
};

// Recibe botón disparador y menú; calcula coordenadas fijas alineadas a la derecha sin recorte por scroll.
const posicionarMenuDropdown = (boton, menu) => {
  const rect = boton.getBoundingClientRect();
  if (rect.width === 0 && rect.height === 0) return false;

  const anchoMenu = Math.max(menu.offsetWidth || 196, 196);
  const altoMenu = menu.offsetHeight || 200;
  const espacioAbajo = window.innerHeight - rect.bottom;
  const abrirArriba = espacioAbajo < altoMenu + 12 && rect.top > espacioAbajo;
  const izquierda = Math.max(8, Math.min(rect.right - anchoMenu, window.innerWidth - anchoMenu - 8));

  menu.style.position = 'fixed';
  menu.style.margin = '0';
  menu.style.right = 'auto';
  menu.style.left = `${izquierda}px`;
  menu.style.minWidth = '12.5rem';
  menu.style.zIndex = '9996';

  if (abrirArriba) {
    menu.style.top = 'auto';
    menu.style.bottom = `${window.innerHeight - rect.top + 6}px`;
  } else {
    menu.style.bottom = 'auto';
    menu.style.top = `${rect.bottom + 6}px`;
  }

  return rect.bottom > 0 && rect.top < window.innerHeight;
};

// Recibe un descriptor de opción; construye el HTML de un botón o enlace dentro del menú desplegable.
const renderItemDropdown = ({
  accion = '',
  etiqueta,
  icono = '',
  variante = 'zinc',
  href = null,
  id = '',
  titulo = '',
  contador = null,
  separadorSuperior = false,
}) => {
  const claseVariante = VARIANTES_ITEM_DROPDOWN[variante] || VARIANTES_ITEM_DROPDOWN.zinc;
  const claseBadge = BADGE_ITEM_DROPDOWN[variante] || BADGE_ITEM_DROPDOWN.zinc;
  const separador = separadorSuperior ? '<div class="my-1 border-t border-slate-100"></div>' : '';
  const iconoHtml = icono
    ? `<i data-lucide="${icono}" class="h-4 w-4 shrink-0" aria-hidden="true"></i>`
    : '';
  const badgeHtml =
    contador !== null && contador !== undefined
      ? `<span class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[11px] font-bold leading-none ${claseBadge}">${contador}</span>`
      : '';
  const clasesBase = `flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-xs font-medium transition-colors cursor-pointer ${claseVariante}`;
  const titleAttr = titulo ? `title="${titulo}"` : '';

  if (href) {
    return `${separador}<a href="${href}" data-dropdown-item class="${clasesBase}" ${titleAttr}>${iconoHtml}<span>${etiqueta}</span>${badgeHtml}</a>`;
  }

  return `${separador}<button type="button" data-dropdown-item data-accion="${accion}" data-id="${id}" class="${clasesBase}" ${titleAttr}>${iconoHtml}<span>${etiqueta}</span>${badgeHtml}</button>`;
};

// Recibe etiqueta, icono y lista de opciones; retorna el HTML del nuevo Dropdown de acciones para tablas.
export const crearDropdownAcciones = ({
  etiqueta = 'Acciones',
  icono = 'ellipsis-vertical',
  variante = 'zinc',
  items = [],
} = {}) => {
  const listaValida = items.filter(Boolean);
  const claseTrigger =
    variante === 'yellow'
      ? 'border-yellow-300 bg-yellow-50 text-yellow-900 hover:bg-yellow-100'
      : 'border-slate-300 bg-white text-zinc-900 hover:bg-slate-50';

  return `
    <div data-dropdown-acciones class="relative inline-flex justify-end">
      <button
        type="button"
        data-dropdown-trigger
        aria-haspopup="menu"
        aria-expanded="false"
        class="inline-flex items-center justify-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-1 active:scale-[0.98] cursor-pointer whitespace-nowrap ${claseTrigger}"
      >
        <i data-lucide="${icono}" class="h-3.5 w-3.5 shrink-0" aria-hidden="true"></i>
        <span>${etiqueta}</span>
        <i data-lucide="chevron-down" class="h-3.5 w-3.5 shrink-0 text-zinc-400" aria-hidden="true"></i>
      </button>

      <div
        popover="manual"
        role="menu"
        data-dropdown-menu
        class="hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-[0_15px_30px_rgba(0,0,0,0.05)]"
      >
        <div class="flex flex-col gap-0.5">
          ${listaValida.map(renderItemDropdown).join('')}
        </div>
      </div>
    </div>
  `;
};

// Recibe ninguno; activa la delegación global de eventos para abrir, posicionar y cerrar dropdowns de tablas.
export const inicializarDropdownsAcciones = () => {
  if (eventosGlobalesRegistrados) return;
  eventosGlobalesRegistrados = true;

  document.addEventListener('click', (evento) => {
    const disparador = evento.target.closest('[data-dropdown-trigger]');
    if (disparador) {
      evento.stopPropagation();
      const contenedor = disparador.closest('[data-dropdown-acciones]');
      const menu = contenedor?.querySelector('[data-dropdown-menu]');
      if (!menu) return;

      const esMismo = dropdownAbierto?.menu === menu;
      cerrarDropdownActivo();
      if (esMismo) return;

      menu.classList.remove('hidden');
      if (typeof menu.showPopover === 'function') {
        try {
          menu.showPopover();
        } catch {
          // Respaldo si el navegador no soporta popover manual
        }
      }
      posicionarMenuDropdown(disparador, menu);
      disparador.setAttribute('aria-expanded', 'true');
      window.lucide?.createIcons();

      const controlador = new AbortController();
      const { signal } = controlador;
      window.addEventListener(
        'scroll',
        (ev) => {
          if (menu.contains(ev.target)) return;
          if (!posicionarMenuDropdown(disparador, menu)) cerrarDropdownActivo();
        },
        { capture: true, passive: true, signal }
      );
      window.addEventListener('resize', () => posicionarMenuDropdown(disparador, menu), { passive: true, signal });

      dropdownAbierto = { boton: disparador, menu, controlador };
      return;
    }

    const item = evento.target.closest('[data-dropdown-item]');
    if (item) {
      cerrarDropdownActivo();
      return;
    }

    if (dropdownAbierto && !dropdownAbierto.menu.contains(evento.target)) {
      cerrarDropdownActivo();
    }
  });

  document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape') cerrarDropdownActivo();
  });
};
