import { Solicitud } from '/js/class/Solicitud.js';
import { mostrarError, ocultarError } from '/js/components/alerta/alerta.js';
import { botonCargando } from '/js/components/cargando.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { alternarClave } from '/js/utils/alternarClave.js';

const validarCredenciales = (usuario, clave) => {
  if (!usuario && !clave) return 'Por favor, completa todos los campos.';
  if (!usuario) return 'Por favor, ingresa tu nombre de usuario.';
  if (!clave) return 'Por favor, ingresa tu contraseña.';
  return null;
};

document.addEventListener('DOMContentLoaded', () => {
  const formularioAutenticacion = document.getElementById('form-autenticacion');
  const inputUsuario = document.getElementById('usuario');
  const inputClave = document.getElementById('clave');
  const btnEntrar = document.getElementById('btn-entrar');

  alternarClave('clave', 'toggle-password');

  formularioAutenticacion?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    ocultarError('.login__error');

    const usuario = inputUsuario?.value.trim() || '';
    const clave = inputClave?.value.trim() || '';
    const errorValidacion = validarCredenciales(usuario, clave);

    if (errorValidacion) {
      mostrarError('.login__error', errorValidacion);
      return;
    }

    botonCargando(btnEntrar, true);
    const resultado = await Solicitud.post(
      '/api/autenticacion',
      { usuario, clave },
      { redirigirSi401: false }
    );
    botonCargando(btnEntrar, false);

    if (resultado.ok && resultado.data?.ok !== false) {
      const mensajeExito = resultado.data?.message || resultado.message || 'Inicio de sesión exitoso.';
      const urlDestino = resultado.data?.redirect || '/inicio';
      mostrarToast('success', mensajeExito);
      setTimeout(() => {
        window.location.href = urlDestino;
      }, 900);
      return;
    }

    mostrarError('.login__error', resultado.message || 'Credenciales inválidas.');
  });
});
