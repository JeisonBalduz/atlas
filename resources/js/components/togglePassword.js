// Cambiar de icono al darle clic
export const togglePassword = (inputId, btnId, eyeIcon = "eye", eyeOffIcon = "eye-off") => {
  const input = document.getElementById(inputId);
  const btn = document.getElementById(btnId);

  if (!input || !btn) return;

  btn.addEventListener("click", () => {
    // 1. Alternar tipo de input (password / text)
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";

    // 2. Definir qué icono toca mostrar
    const currentIcon = isPassword ? eyeOffIcon : eyeIcon;

    // 3. Actualizar el HTML interno del botón con un nuevo icono 'i'
    btn.innerHTML = `<i data-lucide="${currentIcon}"></i>`;

    // 4. Forzar a Lucide a renderizar el nuevo SVG dentro del botón
    if (typeof lucide !== "undefined") {
      lucide.createIcons();
    }
  });
};