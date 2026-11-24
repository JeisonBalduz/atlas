/**
 * Alterna la visibilidad de un campo de contraseña y su icono Lucide.
 * @param {string} inputId
 * @param {string} btnId
 * @param {string} [iconoVer='eye']
 * @param {string} [iconoOcultar='eye-off']
 */
export const alternarClave = (inputId, btnId, iconoVer = 'eye', iconoOcultar = 'eye-off') => {
  const input = document.getElementById(inputId);
  const btn = document.getElementById(btnId);
  if (!input || !btn) return;

  btn.addEventListener('click', () => {
    const esClave = input.type === 'password';
    input.type = esClave ? 'text' : 'password';
    btn.innerHTML = `<i data-lucide="${esClave ? iconoOcultar : iconoVer}"></i>`;
    window.lucide?.createIcons();
  });
};
