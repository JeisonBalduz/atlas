export class Solicitud {
    static #controladores = new Map();

    static #construirUrl(url, params) {
        if (!params || typeof params !== 'object') return url;
        const search = new URLSearchParams();
        Object.entries(params).forEach(([clave, valor]) => {
            if (valor !== undefined && valor !== null) {
                search.append(clave, String(valor));
            }
        });
        const query = search.toString();
        if (!query) return url;
        return `${url}${url.includes('?') ? '&' : '?'}${query}`;
    }

    static #extraerMensajeError(data) {
        if (!data || typeof data !== 'object') return '';
        if (typeof data.message === 'string' && data.message) return data.message;
        if (typeof data.error === 'string' && data.error) return data.error;

        if (data.errors && typeof data.errors === 'object') {
            const primerError = Object.values(data.errors)[0];
            if (Array.isArray(primerError) && typeof primerError[0] === 'string') {
                return primerError[0];
            }
            if (typeof primerError === 'string') {
                return primerError;
            }
        }
        return '';
    }

    static #prepararCancelacion(claveCancelacion, signalExterna) {
        if (!claveCancelacion) return { signal: signalExterna, controlador: null };

        const anterior = this.#controladores.get(claveCancelacion);
        if (anterior) anterior.abort();

        const controlador = new AbortController();
        this.#controladores.set(claveCancelacion, controlador);
        return { signal: controlador.signal, controlador };
    }

    /**
     * Ejecuta una petición HTTP con Fetch API soportando JSON, FormData, params,
     * redirección 401 centralizada y AbortController por clave.
     * @param {string} url
     * @param {RequestInit & { params?: Object, claveCancelacion?: string, redirigirSi401?: boolean }} [options={}]
     */
    static async request(url, options = {}) {
        const {
            params,
            claveCancelacion,
            redirigirSi401 = true,
            signal: signalExterna,
            headers: headersOpcionales,
            ...restOptions
        } = options;

        let body = restOptions.body;
        const esFormData = typeof FormData !== 'undefined' && body instanceof FormData;
        const defaultHeaders = { Accept: 'application/json' };

        if (!esFormData) {
            defaultHeaders['Content-Type'] = 'application/json';
            if (body && typeof body === 'object') {
                body = JSON.stringify(body);
            }
        }

        const headersCombinados = { ...defaultHeaders, ...(headersOpcionales || {}) };
        if (esFormData) delete headersCombinados['Content-Type'];

        const { signal, controlador } = this.#prepararCancelacion(claveCancelacion, signalExterna);
        const urlFinal = this.#construirUrl(url, params);

        try {
            const response = await fetch(urlFinal, {
                ...restOptions,
                headers: headersCombinados,
                body,
                signal,
            });

            if (response.status === 401 && redirigirSi401) {
                window.location.href = '/autenticacion';
                return { ok: false, status: 401, aborted: false, data: null, message: 'Sesión expirada' };
            }

            let data = null;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                try {
                    data = await response.json();
                } catch {
                    data = null;
                }
            }

            const mensajeError = this.#extraerMensajeError(data);

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

            console.error('Error de red en Solicitud:', error);
            return { ok: false, status: 500, aborted: false, data: null, message: 'Error de red' };
        } finally {
            if (claveCancelacion && this.#controladores.get(claveCancelacion) === controlador) {
                this.#controladores.delete(claveCancelacion);
            }
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
