// Configuración de estilos y animaciones para los diferentes tipos de toast
export const toastConfig = (type) => {
  switch (type) {
    case "success":
      return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(16, 185, 129, 0.15); color: #059669; display: flex; align-items: center; justify-content: center;" class="toast-pop">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 3px;" viewBox="0 0 24 24">
                        <path class="toast-draw-check" stroke-linecap="round" stroke-linejoin="round" d="M4 12l6 6L20 6" />
                    </svg>
                </div>`;
    case "error":
      return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(244, 63, 94, 0.15); color: #e11d48; display: flex; align-items: center; justify-content: center;" class="toast-shake">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 3px;" viewBox="0 0 24 24">
                        <path class="toast-draw-x1" stroke-linecap="butt" d="M6 6l12 12" />
                        <path class="toast-draw-x2" stroke-linecap="butt" d="M18 6L6 18" />
                    </svg>
                </div>`;
    case "warning":
      return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(245, 158, 11, 0.15); color: #d97706; display: flex; align-items: center; justify-content: center;" class="toast-pop">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2.5px;" viewBox="0 0 24 24">
                        <path class="toast-draw-triangle" stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 3.5h.01" />
                    </svg>
                </div>`;
    case "info":
      return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(99, 102, 241, 0.15); color: #4f46e5; display: flex; align-items: center; justify-content: center;" class="toast-pop">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2.5px;" viewBox="0 0 24 24">
                        <circle class="toast-draw-circle" cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 16v-4m0-3.5h.01" />
                    </svg>
                </div>`;
    case "loading":
    default:
      return `
                <div style="width: 24px; height: 24px; min-width: 24px; display: flex; align-items: center; justify-content: center;" class="animate-spin">
                    <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"></path>
                    </svg>
                </div>`;
  }
};
