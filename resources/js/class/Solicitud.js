export class Solicitud {
    /**
     * Ejecuta una petición HTTP con Fetch API soportando JSON, FormData y AbortController.
     * @param {string} url
     * @param {RequestInit} [options={}]
     */
    static async request(url, options = {}) {
        let body = options.body;
        const esFormData = typeof FormData !== 'undefined' && body instanceof FormData;

        const defaultHeaders = {
            Accept: 'application/json',
        };

        if (!esFormData) {
            defaultHeaders['Content-Type'] = 'application/json';
            if (body && typeof body === 'object') {
                body = JSON.stringify(body);
            }
        }

        const headersCombinados = { ...defaultHeaders, ...(options.headers || {}) };
        if (esFormData) {
            delete headersCombinados['Content-Type'];
        }

        const config = {
            ...options,
            headers: headersCombinados,
            body,
        };

        try {
            const response = await fetch(url, config);
            let data = null;
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                data = await response.json();
            }

            let mensajeError = data?.message || '';
            if (!mensajeError && data?.errors && typeof data.errors === 'object') {
                const primerError = Object.values(data.errors)[0];
                if (typeof primerError === 'string') {
                    mensajeError = primerError;
                }
            }

            return {
                ok: response.ok,
                status: response.status,
                aborted: false,
                data,
                message: mensajeError || (response.ok ? 'Éxito' : 'Error en la solicitud'),
            };
        } catch (error) {
            if (error?.name === 'AbortError') {
                return { ok: false, status: 0, aborted: true, data: null, message: 'Solicitud cancelada' };
            }

            console.error('Error de red en Request:', error);
            return { ok: false, status: 500, aborted: false, data: null, message: 'Error de red' };
        }
    }

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

    static async all(urls = []) {
        const promesas = urls.map((url) => this.get(url));
        const resultados = await Promise.allSettled(promesas);

        return resultados.map((resultado) => {
            if (resultado.status === 'fulfilled') {
                return resultado.value;
            }
            return { ok: false, status: 500, aborted: false, data: null, message: 'Falló la promesa' };
        });
    }
}
