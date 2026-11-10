export const showError = (selector, mensaje) => {
  const errorElement = document.querySelector(selector);
  errorElement.textContent = mensaje;
  errorElement.classList.remove("hidden");
}

export const hiddenError = (selector, mensaje) => {
  const errorElement = document.querySelector(selector);
  errorElement.textContent = '';
  errorElement.classList.add("hidden");
  
}