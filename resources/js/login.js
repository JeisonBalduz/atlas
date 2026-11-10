import { loginRequest } from "./api/authService.js";
import { showError, hiddenError } from "./utils/displayError.js";
import { showToast } from "./components/toast.js";

document.addEventListener("DOMContentLoaded", () => {
  const formLogin = document.getElementById("form-login");
  const inputUser = document.getElementById("user");
  const inputPass = document.getElementById("password");
  const btnEntrar = document.getElementById("btn-entrar");

  if (!formLogin) return;

  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault(); // Evita el envío tradicional del formulario

    // Obtenemos los valores de los inputs
    const usuario = inputUser.value.trim();
    const clave = inputPass.value.trim();

    // Validación básica en cliente
    if (!usuario || !clave) {
      mostrarError("Por favor, completa todos los campos.");
      return;
    }

    // Deshabilitar botón mientras procesa (Feedback visual)
    setLoading(true);

    // Realizar petición a la API
    const resultado = await loginRequest(usuario, clave);

    setLoading(false);

    if (resultado.ok) {
      // Login exitoso: Redirigir al dashboard o panel principal
      showToast("success", "¡Inicio de sesión exitoso!");
    } else {
      // Mostrar mensaje de error devuelto por el servidor (ej. "Credenciales incorrectas")
      mostrarError(resultado.message || "Usuario o contraseña incorrectos.");
    }
  });

  function setLoading(isLoading) {
    if (isLoading) {
      btnEntrar.disabled = true;
      btnEntrar.classList.add("opacity-70", "cursor-not-allowed");
      btnEntrar.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                3</svg>
                Verificando...
            `;
    } else {
      btnEntrar.disabled = false;
      btnEntrar.classList.remove("opacity-70", "cursor-not-allowed");
      btnEntrar.innerHTML = `
                Iniciar Sesión
                <i data-lucide="arrow-right" class="h-4 w-4"></i>
            `;
      // Refrescar iconos de Lucide si el botón los regenera
      if (window.lucide) lucide.createIcons();
    }
  }

  document.getElementById("toggle-password").addEventListener("click", (e) => {
    console.log("si le diste clik");
    const show = password.type === "password";
    password.type = show ? "text" : "password";
    e.currentTarget.setAttribute(
      "aria-label",
      show ? "Ocultar contraseña" : "Mostrar contraseña",
    );
  });
});
