import { mostrarError } from './ui/elementoError.js';
import { mostrarToast } from './components/toast.js';
import { alternarClave } from './ui/alternarClave.js';
import { botonCargando } from './components/cargando.js';
import { Solicitud } from './class/Solicitud.js';

const validarCredenciales = (usuario, clave) => {
  if (!usuario && !clave) return 'Por favor, completa todos los campos';
  if (!usuario) return 'Por favor, ingresa tu nombre de usuario';
  if (!clave) return 'Por favor, ingresa tu contraseña';
  return null;
};

document.addEventListener('DOMContentLoaded', () => {
  const formularioAutenticacion = document.getElementById('form-autenticacion');
  const inputUsuario = document.getElementById('usuario');
  const inputClave = document.getElementById('clave');
  const btnEntrar = document.getElementById('btn-entrar');

  formularioAutenticacion?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const usuario = inputUsuario.value.trim();
    const clave = inputClave.value.trim();
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

    if (resultado.ok) {
      mostrarToast('success', resultado.mensaje || 'Inicio de sesión exitoso');
      setTimeout(() => {
        window.location.href = resultado.redirect || '/Inicio';
      }, 1500);
      return;
    }

    mostrarError('.login__error', resultado.mensaje);
  });

  alternarClave('password', 'toggle-password');
});
