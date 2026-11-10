// Crear contenedor para el toast si no existe, según la posición especificada
export const createContainer = (position) => {
  const containerId = "toast-container-" + position;
  let container = document.getElementById(containerId);

  if (!container) {
    container = document.createElement("div");
    container.id = containerId;

    const positionClasses = {
      "top-center":
        "top: 20px; left: 50%; transform: translateX(-50%); align-items: center;",
      "top-right": "top: 20px; right: 20px; align-items: flex-end;",
      "top-left": "top: 20px; left: 20px; align-items: flex-start;",
      "bottom-center":
        "bottom: 20px; left: 50%; transform: translateX(-50%); align-items: center;",
      "bottom-right": "bottom: 20px; right: 20px; align-items: flex-end;",
      "bottom-left": "bottom: 20px; left: 20px; align-items: flex-start;",
    };

    container.style.cssText =
      "position: fixed; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; max-width: 380px; width: 100%; padding: 0 16px; box-sizing: border-box; " +
      (positionClasses[position] || positionClasses["top-center"]);
    document.body.appendChild(container);
  }
  return container;
};
