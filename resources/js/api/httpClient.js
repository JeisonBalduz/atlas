/**
 * Realiza una petición fetch y retorna siempre un JSON estructurado.
 * 
 * @param {string} url - Ruta de la API (ej. '/api/login')
 * @param {object} [options={}] - Opciones de fetch (method, body, headers, etc.)
 * @returns {Promise<object>} - Retorna un objeto con { ok, status, data, message }
 */
export async function apiFetch(url, options = {}) {
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    // Si pasas un objeto en el 'body', lo convertimos automáticamente a JSON string
    let body = options.body;
    if (body && typeof body === 'object' && !(body instanceof FormData)) {
        body = JSON.stringify(body);
    }

    const config = {
        ...options,
        headers: {
            ...defaultHeaders,
            ...(options.headers || {})
        },
        body
    };

    try {
        const response = await fetch(url, config);

        // Intentamos parsear la respuesta como JSON
        let data;
        try {
            data = await response.json();
        } catch (e) {
            data = { message: 'Respuesta no válida del servidor' };
        }

        // Si la respuesta HTTP no es exitosa (ej. 400, 401, 500)
        if (!response.ok) {
            throw {
                status: response.status,
                ok: false,
                ...data
            };
        }

        // Retorno exitoso
        return {
            ok: true,
            status: response.status,
            data
        };

    } catch (error) {
        // Captura errores de red (sin internet) o excepciones lanzadas arriba
        console.error('Error en apiFetch:', error);
        return {
            ok: false,
            status: error.status || 500,
            message: error.message || 'Error de conexión con el servidor',
            ...error
        };
    }
}