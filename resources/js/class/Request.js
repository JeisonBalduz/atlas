export class Request {
    
    // Peticion principal (Petición individual)
    static async request(url, options = {}) {
        const defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        let body = options.body;
        if (body && typeof body === 'object' && !(body instanceof FormData)) {
            body = JSON.stringify(body);
        }

        const config = {
            ...options,
            headers: { ...defaultHeaders, ...(options.headers || {}) },
            body
        };

        try {
            const response = await fetch(url, config);
            let data = null;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            }

            return {
                ok: response.ok,
                status: response.status,
                data: data,
                message: data?.message || (response.ok ? 'Éxito' : 'Error en la solicitud')
            };
        } catch (error) {
            console.error('Error de red en Request:', error);
            return { ok: false, status: 500, data: null, message: 'Error de red' };
        }
    }

    // Metodos individulaes
    static get(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    }

    static post(url, data, options = {}) {
        return this.request(url, { ...options, method: 'POST', body: data });
    }

    static put(url, data, options = {}) {
        return this.request(url, { ...options, method: 'PUT', body: data });
    }

    static delete(url, options = {}) {
        return this.request(url, { ...options, method: 'DELETE' });
    }

    // Metodos para lotes (Múltiples peticiones)
    static async all(urls = []) {
        // Convertimos el array de URLs en un array de promesas de tipo GET
        const promesas = urls.map(url => this.get(url));

        // Ejecutamos todas al mismo tiempo con Promise.allSettled
        const resultados = await Promise.allSettled(promesas);

        // Limpiamos la respuesta para que sea fácil de leer en tu vista
        return resultados.map(resultado => {
            if (resultado.status === 'fulfilled') {
                return resultado.value; // Retorna el objeto {ok, data, message} de tu petición
            } else {
                return { ok: false, status: 500, data: null, message: 'Falló la promesa' };
            }
        });
    }
}