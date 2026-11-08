import { apiFetch } from './httpClient.js';

/**
 * Envía las credenciales del usuario al servidor para iniciar sesión.
 * @param {string} usuario 
 * @param {string} clave 
 */
export async function loginRequest(usuario, clave) {
    return await apiFetch('/api/login', {
        method: 'POST',
        body: {
            usuario: usuario,
            clave: clave
        }
    });
}