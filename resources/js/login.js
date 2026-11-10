import { loginRequest } from "./api/authService.js";
import { showError, hiddenError } from "./utils/displayError.js";
import { showToast } from "./components/toast.js";
import { togglePassword } from "./components/togglePassword.js";
import { buttonLoading } from "./components/loading.js";    

document.addEventListener("DOMContentLoaded", () => {
  const formLogin = document.getElementById("form-login");
  const inputUser = document.getElementById("user");
  const inputPass = document.getElementById("password");
  const btnEntrar = document.getElementById("btn-entrar");

  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault(); // Evita el envío tradicional del formulario

    // Obtenemos los valores de los inputs
    const dataUser = inputUser.value.trim();
    const dataPass = inputPass.value.trim();

    // Validación básica en cliente
    if (!dataUser && !dataPass) {
      showError(".login__error", "Por favor, completa todos los campos");
      return;
    }

    // Validación del usuario
    if (!dataUser) {
      showError(".login__error", "Por favor, ingresa tu nombre de usuario");
      return;
    }

    // Validación de contraseña
    if (!dataPass) {
      showError(".login__error", "Por favor, ingresa tu contraseña");
      return;
    }

    // Deshabilitar botón mientras procesa
    buttonLoading(btnEntrar, true);

    // Realizar petición a la API
    const resultado = await loginRequest(dataUser, dataPass);

    buttonLoading(btnEntrar, false);

    if (resultado.ok) {
      // Login exitoso: Redirigir al dashboard o panel principal
      showToast("success", resultado.message || "Inicio de sesión exitoso");
      setTimeout(() => {
        window.location.href = resultado.redirect || "/Home"; 
      }, 1500); 
    } else {
      // Mostrar mensaje de error devuelto por el servidor (ej. "Credenciales incorrectas")
      mostrarError(resultado.message || "Usuario o contraseña incorrectos.");
    }
  });



 togglePassword("password", "toggle-password");
});
