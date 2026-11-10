import { loginRequest } from "./api/authService.js";
import { showError } from "./ui/displayError.js";
import { showToast } from "./components/toast.js";
import { togglePassword } from "./ui/togglePassword.js";
import { buttonLoading } from "./components/loading.js";
import { Request } from "./class/Request.js";   

document.addEventListener("DOMContentLoaded", () => {
  const formLogin = document.getElementById("form-login");
  const inputUser = document.getElementById("user");
  const inputPass = document.getElementById("password");
  const btnEntrar = document.getElementById("btn-entrar");

  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault(); // Evita el envío tradicional del formulario

    // Obtenemos los valores de los inputs
    const user = inputUser.value.trim();
    const pass = inputPass.value.trim();

    // Validación básica en cliente
    if (!user && !pass) {
      showError(".login__error", "Por favor, completa todos los campos");
      return;
    }

    // Validación del usuario
    if (!user) {
      showError(".login__error", "Por favor, ingresa tu nombre de usuario");
      return;
    }

    // Validación de contraseña
    if (!pass) {
      showError(".login__error", "Por favor, ingresa tu contraseña");
      return;
    }

    // Deshabilitar botón mientras procesa
    buttonLoading(btnEntrar, true);

    const dataUser = {
        user:user,
        password: pass
    }
    
    // Realizar petición a la API
    const resultado = await Request.post("/api/login", dataUser);
    
    buttonLoading(btnEntrar, false);

    if (resultado.ok) {
      // Login exitoso: Redirigir al dashboard o panel principal
      showToast("success", resultado.message || "Inicio de sesión exitoso");
      setTimeout(() => {
        window.location.href = resultado.redirect || "/Home";
      }, 1500);
    } else {
      // Mostrar mensaje de error devuelto por el servidor (ej. "Credenciales incorrectas")
      showError(".login__error", resultado.message);
    }
  });

  togglePassword("password", "toggle-password");
});
