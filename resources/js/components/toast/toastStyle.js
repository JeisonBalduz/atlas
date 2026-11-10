// Inyeccion de estilos para animaciones de toast
export const injectToastStyles = () => {
  if (document.getElementById("toast-immutable-styles")) return;

  const styleEl = document.createElement("style");
  styleEl.id = "toast-immutable-styles";
  styleEl.textContent = `
        @keyframes modernSlideIn {
            0% { opacity: 0; transform: translateY(-16px) scale(0.92); }
            60% { opacity: 1; transform: translateY(2px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes modernSlideOut {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-12px) scale(0.95); }
        }
        @keyframes drawLine24 {
            0% { stroke-dashoffset: 24; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes drawLine60 {
            0% { stroke-dashoffset: 60; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes drawLine75 {
            0% { stroke-dashoffset: 75; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.15); }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-3px); }
            40%, 80% { transform: translateX(3px); }
        }
        .toast-anim-in { animation: modernSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .toast-anim-out { animation: modernSlideOut 0.25s cubic-bezier(0.4, 0, 1, 1) forwards; }
        .toast-draw-check { stroke-dasharray: 24; stroke-dashoffset: 24; animation: drawLine24 0.35s ease-in-out forwards; }
        .toast-draw-x1 { stroke-dasharray: 18; stroke-dashoffset: 18; animation: drawLine24 0.2s ease-in-out forwards; }
        .toast-draw-x2 { stroke-dasharray: 18; stroke-dashoffset: 18; animation: drawLine24 0.2s ease-in-out 0.15s forwards; }
        .toast-draw-triangle { stroke-dasharray: 75; stroke-dashoffset: 75; animation: drawLine75 0.4s ease-in-out forwards; }
        .toast-draw-circle { stroke-dasharray: 60; stroke-dashoffset: 60; animation: drawLine60 0.4s ease-in-out forwards; }
        .toast-pop { animation: popIn 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        .toast-shake { animation: shake 0.35s ease-in-out forwards; }
    `;
  document.head.appendChild(styleEl);
};
