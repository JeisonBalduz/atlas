
import { mostrarError } from "./ui/elementoError.js";
import { mostrarToast } from "./components/toast.js";
import { alternarClave } from "./ui/alternarClave.js";
import { botonCargando } from "./components/cargando.js";
import { Solicitud } from "./class/Solicitud.js";   

document.addEventListener("DOMContentLoaded", () => {
  const formularioAutenticacion = document.getElementById("form-autenticacion");
  const inputUsuario = document.getElementById("usuario");
  const inputClave = document.getElementById("clave");
  const btnEntrar = document.getElementById("btn-entrar");

  formularioAutenticacion.addEventListener("submit", async (e) => {
    e.preventDefault(); // Evita el envío tradicional del formulario

    // Obtenemos los valores de los inputs
    const usuario = inputUsuario.value.trim();
    const clave = inputClave.value.trim();

    // Validación básica en cliente
    if (!usuario && !clave) {
     mostrarError(".login__error", "Por favor, completa todos los campos");
      return;
    }

    // Validación del usuario
    if (!usuario) {
     mostrarError(".login__error", "Por favor, ingresa tu nombre de usuario");
      return;
    }

    // Validación de contraseña
    if (!clave) {
     mostrarError(".login__error", "Por favor, ingresa tu contraseña");
      return;
    }

    // Deshabilitar botón mientras procesa
    botonCargando(btnEntrar, true);

    const datosUsuario = {
        usuario:usuario,
        clave: clave
    }

    // Realizar petición a la API
    const resultado = await Solicitud.post("/api/autenticacion", datosUsuario);
    
    botonCargando(btnEntrar, false);

    if (resultado.ok) {
      // Login exitoso: Redirigir al dashboard o panel principal
      mostrarToast("success", resultado.mensaje || "Inicio de sesión exitoso");
      setTimeout(() => {
        window.location.href = resultado.redirect || "/Inicio";
      }, 1500);
      
    } else {
      // Mostrar mensaje de error devuelto por el servidor (ej. "Credenciales incorrectas")
     mostrarError(".login__error", resultado.mensaje);
    }
  });

 alternarClave("password", "toggle-password");
});
