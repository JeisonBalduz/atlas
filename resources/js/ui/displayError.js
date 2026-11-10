export const showError = (selector, mensaje) => {
  const errorElement = document.querySelector(selector);
  errorElement.textContent = mensaje;
  errorElement.classList.remove("hidden");
}

export const hiddenError = (selector) => {
  const hiddenElement = document.querySelector(selector);
  hiddenElement.textContent = '';
  hiddenElement.classList.add("hidden");
  
}