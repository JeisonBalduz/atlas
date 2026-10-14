const activePage = document.body.dataset.page;
const activeUser = AtlasAuth.require();
if (activeUser) {
  const nav = document.getElementById("nav-lateral");
  const home = document.createElement("a");
  home.href = "inicio.html";
  home.className = "nav-link";
  home.innerHTML = '<i data-lucide="house"></i><span>Inicio</span>';
  if (activePage === "inicio") home.setAttribute("aria-current", "page");
  nav.append(home);
  ATLAS_NAV.forEach(([title, icon, items], index) => {
    const details = document.createElement("details");
    details.className = "nav-group";
    details.open = items.some(([id]) => id === activePage);
    const summary = document.createElement("summary");
    summary.innerHTML =
      '<i data-lucide="' +
      icon +
      '"></i><span></span><i data-lucide="chevron-down" class="nav-chevron"></i>';
    summary.querySelector("span").textContent = title;
    details.append(summary);
    const submenu = document.createElement("div");
    submenu.className = "submenu";
    items.forEach(([id, label]) => {
      const a = document.createElement("a");
      a.href = id + ".html";
      a.textContent = label;
      a.className = "nav-child";
      if (id === activePage) a.setAttribute("aria-current", "page");
      submenu.append(a);
    });
    details.append(submenu);
    nav.append(details);
  });
  document
    .querySelectorAll("[data-user-name]")
    .forEach((el) => (el.textContent = activeUser.name));
  document
    .querySelectorAll("[data-user-role]")
    .forEach((el) => (el.textContent = activeUser.role));
  document.getElementById("avatar-initial").textContent = activeUser.name[0];
  const avatar = document.getElementById("btn-avatar"),
    menu = document.getElementById("menu-avatar");
  const close = () => {
    menu.hidden = true;
    avatar.setAttribute("aria-expanded", "false");
  };
  avatar.addEventListener("click", () => {
    menu.hidden = !menu.hidden;
    avatar.setAttribute("aria-expanded", String(!menu.hidden));
  });
  document.addEventListener("click", (e) => {
    if (!menu.contains(e.target) && !avatar.contains(e.target)) close();
  });
  document
    .getElementById("btn-salir")
    .addEventListener("click", () => AtlasAuth.logout());
  const drawer = document.getElementById("drawer"),
    backdrop = document.getElementById("fondo-drawer"),
    button = document.getElementById("btn-menu");
  const closeDrawer = () => {
    drawer.classList.remove("abierto");
    backdrop.hidden = true;
    button.setAttribute("aria-expanded", "false");
  };
  button.addEventListener("click", () => {
    drawer.classList.toggle("abierto");
    backdrop.hidden = !drawer.classList.contains("abierto");
    button.setAttribute("aria-expanded", String(!backdrop.hidden));
  });
  backdrop.addEventListener("click", closeDrawer);
  document
    .getElementById("close-drawer")
    .addEventListener("click", closeDrawer);
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      close();
      closeDrawer();
    }
  });
  window.lucide?.createIcons({ attrs: { "stroke-width": 2.5 } });
}
