import { toastConfig } from "./toast/toastConfig.js";
import { createContainer } from "./toast/toastContainer.js";
import { injectToastStyles } from "./toast/toastStyle.js";

// Función para mostrar un toast
export const showToast = (
  type,
  message,
  position = "top-center",
  duration = 4000,
) => {
  injectToastStyles();
  const container = createContainer(position);
  const id =
    "toast-" + Date.now() + "-" + Math.random().toString(36).substring(2, 5);
  const iconHtml = toastConfig(type);

  const toastEl = document.createElement("div");
  toastEl.id = id;
  toastEl.style.cssText =
    "pointer-events: auto; width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); font-size: 12px; font-weight: 500; font-family: ui-sans-serif, system-ui, sans-serif; color: #1f2937; box-sizing: border-box;";

  toastEl.className = "toast-anim-in";

  toastEl.innerHTML = `
        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
            <div style="position: relative; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" class="toast-icon-wrap">
                ${iconHtml}
            </div>
            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f2937;" class="toast-text">${message}</span>
        </div>
        <button onclick="removeToast('${id}')" style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: color 0.2s;" onmouseover="this.style.color='#4b5563'" onmouseout="this.style.color='#9ca3af'">
            <svg style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2.5px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;

  container.appendChild(toastEl);

  if (type !== "loading" && duration > 0) {
    setTimeout(() => removeToast(id), duration);
  }

  return id;
};

// Función para eliminar un toast
export const removeToast = (id) => {
  const toastEl = document.getElementById(id);
  if (toastEl && !toastEl.classList.contains("closing")) {
    toastEl.classList.add("closing");
    toastEl.classList.remove("toast-anim-in");
    toastEl.classList.add("toast-anim-out");

    setTimeout(() => toastEl.remove(), 250);
  }
};

// Función para actualizar un toast existente
export const updateToast = (id, newType, newMessage, duration = 4000) => {
  const toastEl = document.getElementById(id);
  if (!toastEl) return;

  const iconWrap = toastEl.querySelector(".toast-icon-wrap");
  const messageEl = toastEl.querySelector(".toast-text");

  messageEl.textContent = newMessage;
  iconWrap.innerHTML = toastConfig(newType);

  if (newType !== "loading" && duration > 0) {
    setTimeout(() => removeToast(id), duration);
  }
};
