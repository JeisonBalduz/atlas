import { toastConfig } from './toastConfig.js';
import { createContainer } from './toastContainer.js';
import { injectToastStyles } from './toastStyle.js';

export const removeToast = (id) => {
  const toastEl = document.getElementById(id);
  if (toastEl && !toastEl.classList.contains('closing')) {
    toastEl.classList.add('closing');
    toastEl.classList.remove('toast-anim-in');
    toastEl.classList.add('toast-anim-out');
    setTimeout(() => toastEl.remove(), 220);
  }
};

export const mostrarToast = (type, message, position = 'top-center', duration = 4500) => {
  injectToastStyles();
  const container = createContainer(position);
  const id = `toast-${Date.now()}-${Math.random().toString(36).slice(2, 5)}`;
  const iconHtml = toastConfig(type);

  const toastEl = document.createElement('div');
  toastEl.id = id;
  toastEl.setAttribute('role', type === 'error' ? 'alert' : 'status');
  toastEl.style.cssText =
    'pointer-events: auto; width: 100%; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 14px 16px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 15px 30px rgba(0,0,0,0.08); font-size: 14px; line-height: 1.45; font-weight: 500; font-family: "Inter", system-ui, -apple-system, sans-serif; color: #18181b; box-sizing: border-box; -webkit-font-smoothing: antialiased;';
  toastEl.className = 'toast-anim-in';

  toastEl.innerHTML = `
    <div style="display: flex; align-items: flex-start; gap: 12px; min-width: 0; flex: 1;">
      <div style="position: relative; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;" class="toast-icon-wrap">
        ${iconHtml}
      </div>
      <span style="white-space: normal; overflow-wrap: break-word; word-break: break-word; color: #18181b; flex: 1;" class="toast-text"></span>
    </div>
    <button type="button" data-toast-close aria-label="Cerrar notificación" style="background: none; border: none; color: #71717a; cursor: pointer; padding: 4px; margin-top: 1px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: color 0.2s;">
      <svg style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2.5px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  `;

  toastEl.querySelector('.toast-text').textContent = message ?? '';
  toastEl.querySelector('[data-toast-close]')?.addEventListener('click', () => removeToast(id));

  container.appendChild(toastEl);

  if (type !== 'loading' && duration > 0) {
    setTimeout(() => removeToast(id), duration);
  }

  return id;
};

export const updateToast = (id, newType, newMessage, duration = 4500) => {
  const toastEl = document.getElementById(id);
  if (!toastEl) return;

  const iconWrap = toastEl.querySelector('.toast-icon-wrap');
  const messageEl = toastEl.querySelector('.toast-text');

  if (messageEl) messageEl.textContent = newMessage;
  if (iconWrap) iconWrap.innerHTML = toastConfig(newType);

  if (newType !== 'loading' && duration > 0) {
    setTimeout(() => removeToast(id), duration);
  }
};
