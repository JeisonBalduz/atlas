// Datos RESTAURADOS completos para los submenús del Sidebar (modo colapsado)
const menusData = {
  nomina: [
    {
      label: "Registrar empleado",
      url: "#",
    },
    {
      label: "Registrar familiar",
      url: "#",
    },
    {
      label: "Empleados",
      url: "#",
    },
    {
      label: "Familiares",
      url: "#",
    },
  ],
  beneficios: [
    {
      label: "Asignar beneficio",
      url: "#",
    },
    {
      label: "Hcm",
      url: "#",
    },
  ],
  vacaciones: [
    {
      label: "Asignar vacaciones",
      url: "#",
    },
    {
      label: "Suspender vacaciones",
      url: "#",
    },
    {
      label: "Vacaciones",
      url: "#",
    },
  ],
  ausencias: [
    {
      label: "Asignar ausencia",
      url: "#",
    },
    {
      label: "Permisos",
      url: "#",
    },
    {
      label: "Reposos",
      url: "#",
    },
  ],
  asistencias: null,
  usuarios: [
    {
      label: "Registrar Usuarios",
      url: "#",
    },
    {
      label: "Ver usuario",
      url: "#",
    },
    {
      label: "Roles",
      url: "#",
    },
    {
      label: "Permisos",
      url: "#",
    },
  ],
  archivo: [
    {
      label: "Ficha técnica",
      url: "#",
    },
    {
      label: "Cargar archivos",
      url: "#",
    },
    {
      label: "Archivos",
      url: "#",
    },
  ],
  administrador: [
    {
      label: "Base de datos",
      url: "#",
    },
    {
      label: "Auditoria",
      url: "#",
    },
  ],
};

const sidebar = document.getElementById("sidebar");
const mainContent = document.getElementById("main-content");
const hamburgerBtn = document.getElementById("hamburger-btn");
const sidebarBubble = document.getElementById("sidebar-bubble");
const sidebarBubbleList = document.getElementById("sidebar-bubble-list");
const menuBtnContainers = document.querySelectorAll(".menu-item-container");

const notiBtn = document.getElementById("noti-btn");
const notiBubble = document.getElementById("noti-bubble");
const profileBtn = document.getElementById("profile-btn");
const profileBubble = document.getElementById("profile-bubble");

let activeMenuKey = null;

// Toggle Sidebar (Expandir / Colapsar)
hamburgerBtn.addEventListener("click", () => {
  const isExpanded = sidebar.getAttribute("data-expanded") === "true";
  closeAllBubbles();

  if (isExpanded) {
    sidebar.setAttribute("data-expanded", "false");
    mainContent.classList.remove("ml-64");
    mainContent.classList.add("ml-20");

    document.querySelectorAll(".sidebar-text").forEach((el) => {
      el.classList.remove("opacity-100");
      el.classList.add("opacity-0");
      setTimeout(() => el.classList.add("hidden"), 300);
    });

    document.querySelectorAll(".accordion-content").forEach((acc) => {
      acc.setAttribute("data-open", "false");
      acc.classList.add("hidden");
    });
  } else {
    sidebar.setAttribute("data-expanded", "true");
    mainContent.classList.remove("ml-20");
    mainContent.classList.add("ml-64");

    document.querySelectorAll(".sidebar-text").forEach((el) => {
      el.classList.remove("hidden");
      setTimeout(() => {
        el.classList.remove("opacity-0");
        el.classList.add("opacity-100");
      }, 50);
    });

    document.querySelectorAll(".accordion-content").forEach((acc) => {
      acc.classList.remove("hidden");
    });
  }
});

// Lógica de Menús del Sidebar
menuBtnContainers.forEach((container) => {
  const btn = container.querySelector(".menu-btn");
  const menuKey = container.getAttribute("data-menu");
  const accordion = container.querySelector(".accordion-content");
  const chevron = container.querySelector(".chevron");

  btn.addEventListener("click", (e) => {
    e.stopPropagation();
    closeHeaderBubbles();

    const isSidebarExpanded = sidebar.getAttribute("data-expanded") === "true";

    if (isSidebarExpanded) {
      if (!accordion) return;
      const isOpen = accordion.getAttribute("data-open") === "true";

      document
        .querySelectorAll(".accordion-content")
        .forEach((acc) => acc.setAttribute("data-open", "false"));
      document
        .querySelectorAll(".chevron")
        .forEach((chev) => (chev.style.transform = "rotate(0deg)"));

      if (!isOpen) {
        accordion.setAttribute("data-open", "true");
        if (chevron) chevron.style.transform = "rotate(180deg)";
      }
    } else {
      const items = menusData[menuKey];
      if (!items) {
        closeSidebarBubble();
        return;
      }

      if (
        activeMenuKey === menuKey &&
        sidebarBubble.classList.contains("scale-y-100")
      ) {
        closeSidebarBubble();
        return;
      }

      sidebarBubbleList.innerHTML = "";
      items.forEach((item) => {
        const li = document.createElement("li");
        li.innerHTML = `<a href="${item.url}" class="block px-4 py-2.5 mx-1 rounded-xl transition-all text-zinc-300 hover:text-white hover:bg-zinc-800/50">${item.label}</a>`;
        sidebarBubbleList.appendChild(li);
      });

      const rect = btn.getBoundingClientRect();
      sidebarBubble.style.top = `${rect.top}px`;
      sidebarBubble.style.left = `${rect.right + 24}px`; // Ajustado de 16px a 24px para mayor separación

      sidebarBubble.classList.remove(
        "opacity-0",
        "scale-y-0",
        "pointer-events-none",
      );
      sidebarBubble.classList.add("opacity-100", "scale-y-100");

      // Sistema anticolisión para que no se corte abajo
      setTimeout(() => {
        const bubbleRect = sidebarBubble.getBoundingClientRect();
        if (bubbleRect.bottom > window.innerHeight) {
          sidebarBubble.style.top = `${window.innerHeight - bubbleRect.height - 20}px`;
        }
      }, 50);

      activeMenuKey = menuKey;
    }
  });
});

function toggleHeaderBubble(bubbleToToggle, e) {
  e.stopPropagation();
  closeSidebarBubble();
  const isTargetOpen = bubbleToToggle.classList.contains("scale-y-100");
  closeHeaderBubbles();
  if (!isTargetOpen) {
    bubbleToToggle.classList.remove(
      "opacity-0",
      "scale-y-0",
      "pointer-events-none",
    );
    bubbleToToggle.classList.add("opacity-100", "scale-y-100");
  }
}

notiBtn.addEventListener("click", (e) => toggleHeaderBubble(notiBubble, e));
profileBtn.addEventListener("click", (e) =>
  toggleHeaderBubble(profileBubble, e),
);

function closeSidebarBubble() {
  sidebarBubble.classList.remove("opacity-100", "scale-y-100");
  sidebarBubble.classList.add("opacity-0", "scale-y-0", "pointer-events-none");
  activeMenuKey = null;
}

function closeHeaderBubbles() {
  notiBubble.classList.remove("opacity-100", "scale-y-100");
  notiBubble.classList.add("opacity-0", "scale-y-0", "pointer-events-none");
  profileBubble.classList.remove("opacity-100", "scale-y-100");
  profileBubble.classList.add("opacity-0", "scale-y-0", "pointer-events-none");
}

function closeAllBubbles() {
  closeSidebarBubble();
  closeHeaderBubbles();
}

document.addEventListener("click", (e) => {
  if (!sidebarBubble.contains(e.target)) closeSidebarBubble();
  if (!notiBubble.contains(e.target) && !profileBubble.contains(e.target))
    closeHeaderBubbles();
});

mainContent.addEventListener("scroll", closeAllBubbles);
sidebar.addEventListener("scroll", closeSidebarBubble);

sidebarBubble.addEventListener("click", (e) => e.stopPropagation());
notiBubble.addEventListener("click", (e) => e.stopPropagation());
profileBubble.addEventListener("click", (e) => e.stopPropagation());
