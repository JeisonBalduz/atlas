const menusData = {
  inicio: null,
  nomina: [
    { label: 'Registrar empleado', url: '/empleado/registrar' },
    { label: 'Registrar familiar', url: '/empleado/registrar/familiar' },
    { label: 'Ver Empleados', url: '/empleado/registros' },
    { label: 'Ver Familiares', url: '/empleado/registros/familiar' },
  ],
  tablasmaestras: null,
  beneficios: [{ label: 'Asignar beneficio', url: '/beneficios' }],
  vacaciones: [
    { label: 'Asignar vacaciones', url: '/vacaciones' },
    { label: 'Suspender vacaciones', url: '#' },
  ],
  ausencias: [
    { label: 'Asignar ausencia', url: '/ausencia' },
    { label: 'Permisos', url: '#' },
    { label: 'Reposos', url: '#' },
  ],
  asistencias: null,
  usuarios: [
    { label: 'Registrar Usuarios', url: '#' },
    { label: 'Ver usuario', url: '#' },
    { label: 'Roles', url: '#' },
    { label: 'Permisos', url: '#' },
  ],
  archivo: [
    { label: 'Ficha técnica', url: '#' },
    { label: 'Cargar archivos', url: '#' },
    { label: 'Archivos', url: '#' },
  ],
  administrador: [
    { label: 'Base de datos', url: '#' },
    { label: 'Auditoria', url: '#' },
  ],
};

const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');
const hamburgerBtn = document.getElementById('hamburger-btn');
const sidebarBubble = document.getElementById('sidebar-bubble');
const sidebarBubbleList = document.getElementById('sidebar-bubble-list');
const notiBtn = document.getElementById('noti-btn');
const notiBubble = document.getElementById('noti-bubble');
const profileBtn = document.getElementById('profile-btn');
const profileBubble = document.getElementById('profile-bubble');

let activeMenuKey = null;

const alternarBurbuja = (elemento, mostrar) => {
  if (!elemento) return;
  elemento.classList.toggle('opacity-100', mostrar);
  elemento.classList.toggle('scale-y-100', mostrar);
  elemento.classList.toggle('opacity-0', !mostrar);
  elemento.classList.toggle('scale-y-0', !mostrar);
  elemento.classList.toggle('pointer-events-none', !mostrar);
};

const closeSidebarBubble = () => {
  alternarBurbuja(sidebarBubble, false);
  activeMenuKey = null;
};

const closeHeaderBubbles = () => {
  alternarBurbuja(notiBubble, false);
  alternarBurbuja(profileBubble, false);
};

const closeAllBubbles = () => {
  closeSidebarBubble();
  closeHeaderBubbles();
};

const alternarSidebar = () => {
  const isExpanded = sidebar.getAttribute('data-expanded') === 'true';
  closeAllBubbles();

  sidebar.setAttribute('data-expanded', String(!isExpanded));
  mainContent.classList.toggle('ml-64', !isExpanded);
  mainContent.classList.toggle('ml-20', isExpanded);

  document.querySelectorAll('.sidebar-text').forEach((el) => {
    if (isExpanded) {
      el.classList.remove('opacity-100');
      el.classList.add('opacity-0');
      setTimeout(() => el.classList.add('hidden'), 300);
    } else {
      el.classList.remove('hidden');
      setTimeout(() => {
        el.classList.remove('opacity-0');
        el.classList.add('opacity-100');
      }, 50);
    }
  });

  document.querySelectorAll('.accordion-content').forEach((acc) => {
    if (isExpanded) acc.setAttribute('data-open', 'false');
    acc.classList.toggle('hidden', isExpanded);
  });
};

const alternarAcordeon = (accordion, chevron) => {
  if (!accordion) return;
  const isOpen = accordion.getAttribute('data-open') === 'true';

  document.querySelectorAll('.accordion-content').forEach((acc) => acc.setAttribute('data-open', 'false'));
  document.querySelectorAll('.chevron').forEach((chev) => (chev.style.transform = 'rotate(0deg)'));

  if (!isOpen) {
    accordion.setAttribute('data-open', 'true');
    if (chevron) chevron.style.transform = 'rotate(180deg)';
  }
};

const mostrarBurbujaMenu = (btn, menuKey) => {
  const items = menusData[menuKey];
  if (!items || !sidebarBubble || !sidebarBubbleList) {
    closeSidebarBubble();
    return;
  }

  if (activeMenuKey === menuKey && sidebarBubble.classList.contains('scale-y-100')) {
    closeSidebarBubble();
    return;
  }

  sidebarBubbleList.innerHTML = items
    .map(
      (item) =>
        `<li><a href="${item.url}" class="block px-4 py-2.5 mx-1 rounded-xl transition-all text-zinc-300 hover:text-white hover:bg-zinc-800/50">${item.label}</a></li>`
    )
    .join('');

  const rect = btn.getBoundingClientRect();
  sidebarBubble.style.top = `${rect.top}px`;
  sidebarBubble.style.left = `${rect.right + 24}px`;
  alternarBurbuja(sidebarBubble, true);

  setTimeout(() => {
    const bubbleRect = sidebarBubble.getBoundingClientRect();
    if (bubbleRect.bottom > window.innerHeight) {
      sidebarBubble.style.top = `${window.innerHeight - bubbleRect.height - 20}px`;
    }
  }, 50);

  activeMenuKey = menuKey;
};

const toggleHeaderBubble = (bubbleToToggle, e) => {
  if (!bubbleToToggle) return;
  e.stopPropagation();
  closeSidebarBubble();
  const isTargetOpen = bubbleToToggle.classList.contains('scale-y-100');
  closeHeaderBubbles();
  if (!isTargetOpen) alternarBurbuja(bubbleToToggle, true);
};

if (sidebar && mainContent && hamburgerBtn) {
  hamburgerBtn.addEventListener('click', alternarSidebar);

  document.querySelectorAll('.menu-item-container').forEach((container) => {
    const btn = container.querySelector('.menu-btn');
    const menuKey = container.getAttribute('data-menu');
    const accordion = container.querySelector('.accordion-content');
    const chevron = container.querySelector('.chevron');

    btn?.addEventListener('click', (e) => {
      e.stopPropagation();
      closeHeaderBubbles();
      const isExpanded = sidebar.getAttribute('data-expanded') === 'true';
      isExpanded ? alternarAcordeon(accordion, chevron) : mostrarBurbujaMenu(btn, menuKey);
    });
  });

  notiBtn?.addEventListener('click', (e) => toggleHeaderBubble(notiBubble, e));
  profileBtn?.addEventListener('click', (e) => toggleHeaderBubble(profileBubble, e));

  document.addEventListener('click', (e) => {
    if (sidebarBubble && !sidebarBubble.contains(e.target)) closeSidebarBubble();
    if ((!notiBubble || !notiBubble.contains(e.target)) && (!profileBubble || !profileBubble.contains(e.target))) {
      closeHeaderBubbles();
    }
  });

  mainContent.addEventListener('scroll', closeAllBubbles);
  sidebar.addEventListener('scroll', closeSidebarBubble);
  sidebarBubble?.addEventListener('click', (e) => e.stopPropagation());
  notiBubble?.addEventListener('click', (e) => e.stopPropagation());
  profileBubble?.addEventListener('click', (e) => e.stopPropagation());
}
