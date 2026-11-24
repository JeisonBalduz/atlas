// Inyección de estilos para animaciones nítidas de toast (sin scale fraccional que emborrone el texto)
export const injectToastStyles = () => {
  if (document.getElementById('toast-immutable-styles')) return;

  const styleEl = document.createElement('style');
  styleEl.id = 'toast-immutable-styles';
  styleEl.textContent = `
    @keyframes modernSlideIn {
      0% { opacity: 0; transform: translate3d(0, -12px, 0); }
      100% { opacity: 1; transform: translate3d(0, 0, 0); }
    }
    @keyframes modernSlideOut {
      0% { opacity: 1; transform: translate3d(0, 0, 0); }
      100% { opacity: 0; transform: translate3d(0, -10px, 0); }
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
      0% { opacity: 0; }
      100% { opacity: 1; }
    }
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25%, 75% { transform: translateX(-2px); }
      50% { transform: translateX(2px); }
    }
    .toast-anim-in { animation: modernSlideIn 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .toast-anim-out { animation: modernSlideOut 0.2s cubic-bezier(0.4, 0, 1, 1) forwards; }
    .toast-draw-check { stroke-dasharray: 24; stroke-dashoffset: 24; animation: drawLine24 0.35s ease-in-out forwards; }
    .toast-draw-x1 { stroke-dasharray: 18; stroke-dashoffset: 18; animation: drawLine24 0.2s ease-in-out forwards; }
    .toast-draw-x2 { stroke-dasharray: 18; stroke-dashoffset: 18; animation: drawLine24 0.2s ease-in-out 0.15s forwards; }
    .toast-draw-triangle { stroke-dasharray: 75; stroke-dashoffset: 75; animation: drawLine75 0.4s ease-in-out forwards; }
    .toast-draw-circle { stroke-dasharray: 60; stroke-dashoffset: 60; animation: drawLine60 0.4s ease-in-out forwards; }
    .toast-pop { animation: popIn 0.2s ease-out forwards; }
    .toast-shake { animation: shake 0.3s ease-in-out forwards; }
  `;
  document.head.appendChild(styleEl);
};
