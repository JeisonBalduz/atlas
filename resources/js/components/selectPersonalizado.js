/**
 * selectPersonalizado.js
 * Componente de Select con Buscador, Posicionamiento Inteligente y soporte para carga
 * remota desde servidor (`data-remote-url`) para evitar renderizar cientos/miles de nodos DOM.
 */

const escaparAtributo = (valor) =>
    String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

const ICONO_CHECK_SVG =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 %CLASS%"><path d="M20 6 9 17l-5-5"/></svg>';

const construirHtmlOpciones = (normalizadas, valorFinal) => {
    let html = '';
    normalizadas.forEach((opcion) => {
        const esCoincidencia = opcion.valor === valorFinal;
        const metaBusqueda = `${opcion.valor} ${opcion.etiqueta} ${opcion.subetiqueta}`.toLowerCase();
        const checkSvg = ICONO_CHECK_SVG.replace('%CLASS%', esCoincidencia ? '' : 'hidden');

        html += `
            <div data-select-item
                 data-value="${escaparAtributo(opcion.valor)}"
                 data-label="${escaparAtributo(opcion.etiqueta)}"
                 data-sublabel="${escaparAtributo(opcion.subetiqueta)}"
                 data-icon="${escaparAtributo(opcion.icono)}"
                 data-search="${escaparAtributo(metaBusqueda)}"
                 class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors ${esCoincidencia ? 'bg-zinc-100' : ''}">
                <div class="flex items-center gap-2.5 truncate">
                    ${opcion.icono ? `<span class="text-base shrink-0">${escaparAtributo(opcion.icono)}</span>` : ''}
                    <span class="font-semibold text-zinc-900 truncate">${escaparAtributo(opcion.etiqueta)}</span>
                    ${opcion.subetiqueta ? `<span class="text-zinc-500 truncate max-w-35">${escaparAtributo(opcion.subetiqueta)}</span>` : ''}
                </div>
                ${checkSvg}
            </div>
        `;
    });

    html += `
        <div class="custom-select-empty ${normalizadas.length === 0 ? '' : 'hidden'} px-3 py-4 text-center text-xs text-zinc-400">
            No se encontraron registros.
        </div>
    `;

    return html;
};

const normalizarOpciones = (opciones) => {
    if (Array.isArray(opciones)) {
        return opciones.map((item) => {
            if (typeof item === 'object' && item !== null) {
                return {
                    valor: String(item.valor ?? item.value ?? item.id ?? ''),
                    etiqueta: String(item.etiqueta ?? item.label ?? item.nombre ?? item.name ?? item.texto ?? item.text ?? item.valor ?? item.value ?? ''),
                    subetiqueta: String(item.subetiqueta || item.sublabel || ''),
                    icono: String(item.icono || item.icon || item.flag || ''),
                };
            }
            return { valor: String(item), etiqueta: String(item), subetiqueta: '', icono: '' };
        });
    }

    if (typeof opciones === 'object' && opciones !== null) {
        return Object.entries(opciones).map(([clave, valor]) => {
            if (typeof valor === 'object' && valor !== null) {
                return {
                    valor: String(valor.valor ?? valor.value ?? clave),
                    etiqueta: String(valor.etiqueta ?? valor.label ?? valor.nombre ?? valor.name ?? clave),
                    subetiqueta: String(valor.subetiqueta || valor.sublabel || ''),
                    icono: String(valor.icono || valor.icon || ''),
                };
            }
            return { valor: String(clave), etiqueta: String(valor), subetiqueta: '', icono: '' };
        });
    }

    return [];
};

/**
 * Inicializa los selectores personalizados en el DOM.
 * @param {string} selector
 */
export const inicializarSelectsPersonalizados = (selector = '[data-custom-select]') => {
    const contenedores = document.querySelectorAll(selector);

    contenedores.forEach((contenedor) => {
        if (contenedor.dataset.selectPersonalizadoInicializado || contenedor.dataset.customSelectInitialized) return;
        contenedor.dataset.selectPersonalizadoInicializado = 'true';
        contenedor.dataset.customSelectInitialized = 'true';

        const selectNativo = contenedor.querySelector('.custom-select-native');
        const botonDisparador = contenedor.querySelector('.custom-select-trigger');
        const menuDesplegable = contenedor.querySelector('.custom-select-dropdown');
        const campoBusqueda = contenedor.querySelector('.custom-select-search');
        const listaOpciones = contenedor.querySelector('.custom-select-options');
        const iconoChevron = botonDisparador?.querySelector('[data-lucide="chevron-down"]');
        const visualizadorSeleccionado = botonDisparador?.querySelector('.custom-select-display');
        const remoteUrl = (contenedor.dataset.remoteUrl || '').trim();

        if (!selectNativo || !botonDisparador || !menuDesplegable || !visualizadorSeleccionado) return;

        let estaAbierto = false;
        let controladorRemoto = null;
        let timerDebounce = null;
        let cacheInicialRemota = null;

        const actualizarInterfazSeleccionada = (valor, etiqueta, subetiqueta = '', icono = '') => {
            if (!valor) {
                const textoPlaceholder = selectNativo.getAttribute('data-placeholder') || 'Seleccionar opción...';
                visualizadorSeleccionado.innerHTML = `<span class="text-xs text-zinc-400 truncate">${escaparAtributo(textoPlaceholder)}</span>`;
            } else {
                let html = '<div class="flex items-center gap-2.5 truncate">';
                if (icono) html += `<span class="text-base shrink-0">${escaparAtributo(icono)}</span>`;
                html += `<span class="text-xs font-semibold text-zinc-900 truncate">${escaparAtributo(etiqueta)}</span>`;
                if (subetiqueta) html += `<span class="text-xs text-zinc-500 truncate max-w-30">${escaparAtributo(subetiqueta)}</span>`;
                html += '</div>';
                visualizadorSeleccionado.innerHTML = html;
            }

            menuDesplegable.querySelectorAll('[data-select-item]').forEach((elementoOpcion) => {
                const esCoincidencia = elementoOpcion.dataset.value === valor;
                const iconoCheck = elementoOpcion.querySelector('.custom-select-check');
                if (iconoCheck) {
                    iconoCheck.classList.toggle('hidden', !esCoincidencia);
                }
                elementoOpcion.classList.toggle('bg-zinc-100', esCoincidencia);
            });
        };

        const renderizarOpcionesRemotas = (items) => {
            if (!listaOpciones) return;
            const normalizadas = normalizarOpciones(items);
            listaOpciones.innerHTML = construirHtmlOpciones(normalizadas, String(selectNativo.value || ''));
        };

        const cargarOpcionesRemotas = async (consulta) => {
            if (!remoteUrl || !listaOpciones) return;

            const q = String(consulta || '').trim();
            if (q === '' && cacheInicialRemota !== null) {
                renderizarOpcionesRemotas(cacheInicialRemota);
                return;
            }

            if (controladorRemoto) {
                controladorRemoto.abort();
            }
            controladorRemoto = new AbortController();

            listaOpciones.innerHTML =
                '<div class="px-3 py-4 text-center text-xs text-zinc-400">Buscando registros...</div>';

            try {
                const separador = remoteUrl.includes('?') ? '&' : '?';
                const url = `${remoteUrl}${separador}q=${encodeURIComponent(q)}&limit=30`;
                const respuesta = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controladorRemoto.signal,
                });

                if (respuesta.status === 401) {
                    window.location.href = '/autenticacion';
                    return;
                }

                const json = await respuesta.json().catch(() => null);
                const items = Array.isArray(json?.data) ? json.data : [];
                if (q === '') {
                    cacheInicialRemota = items;
                }
                renderizarOpcionesRemotas(items);
            } catch (error) {
                if (error?.name === 'AbortError') return;
                listaOpciones.innerHTML =
                    '<div class="px-3 py-4 text-center text-xs text-red-500">No se pudieron cargar las opciones.</div>';
            }
        };

        const filtrarElementos = (consulta) => {
            const consultaLimpia = consulta.toLowerCase().trim();
            const elementos = menuDesplegable.querySelectorAll('[data-select-item]');
            const mensajeVacio = menuDesplegable.querySelector('.custom-select-empty');
            let elementosVisibles = 0;

            elementos.forEach((elementoOpcion) => {
                const texto = elementoOpcion.dataset.search || elementoOpcion.textContent.toLowerCase();
                const coincide = !consultaLimpia || texto.includes(consultaLimpia);
                elementoOpcion.classList.toggle('hidden', !coincide);
                if (coincide) elementosVisibles++;
            });

            if (mensajeVacio) {
                mensajeVacio.classList.toggle('hidden', elementosVisibles > 0);
            }
        };

        const abrirDesplegable = () => {
            estaAbierto = true;
            menuDesplegable.classList.remove('hidden');

            const rectangulo = botonDisparador.getBoundingClientRect();
            const espacioInferior = window.innerHeight - rectangulo.bottom;
            const abrirHaciaArriba = espacioInferior < 280;

            if (abrirHaciaArriba) {
                menuDesplegable.classList.add('bottom-full', 'mb-1.5', 'origin-bottom');
                menuDesplegable.classList.remove('top-full', 'mt-1.5', 'origin-top');
            } else {
                menuDesplegable.classList.add('top-full', 'mt-1.5', 'origin-top');
                menuDesplegable.classList.remove('bottom-full', 'mb-1.5', 'origin-bottom');
            }

            if (iconoChevron) iconoChevron.classList.add('rotate-180');
            if (campoBusqueda) {
                campoBusqueda.value = '';
                if (remoteUrl) {
                    cargarOpcionesRemotas('');
                } else {
                    filtrarElementos('');
                }
                setTimeout(() => campoBusqueda.focus(), 50);
            } else if (remoteUrl) {
                cargarOpcionesRemotas('');
            }
        };

        const cerrarDesplegable = () => {
            estaAbierto = false;
            menuDesplegable.classList.add('hidden');
            if (iconoChevron) iconoChevron.classList.remove('rotate-180');
        };

        botonDisparador.addEventListener('click', (evento) => {
            evento.stopPropagation();
            estaAbierto ? cerrarDesplegable() : abrirDesplegable();
        });

        if (campoBusqueda) {
            campoBusqueda.addEventListener('input', (evento) => {
                const consulta = evento.target.value;
                if (remoteUrl) {
                    clearTimeout(timerDebounce);
                    timerDebounce = setTimeout(() => cargarOpcionesRemotas(consulta), 180);
                    return;
                }
                filtrarElementos(consulta);
            });
            campoBusqueda.addEventListener('keydown', (evento) => {
                if (evento.key === 'Enter') evento.preventDefault();
                if (evento.key === 'Escape') cerrarDesplegable();
            });
        }

        menuDesplegable.addEventListener('click', (evento) => {
            const elementoOpcion = evento.target.closest('[data-select-item]');
            if (!elementoOpcion) return;

            const valor = elementoOpcion.dataset.value || '';
            const etiqueta = elementoOpcion.dataset.label || '';
            const subetiqueta = elementoOpcion.dataset.sublabel || '';
            const icono = elementoOpcion.dataset.icon || '';

            // En selects remotos garantizamos que la opción exista en el <select> nativo
            let opcionNativa = Array.from(selectNativo.options).find((opt) => opt.value === valor);
            if (!opcionNativa && valor !== '') {
                opcionNativa = document.createElement('option');
                opcionNativa.value = valor;
                opcionNativa.textContent = etiqueta;
                if (subetiqueta) opcionNativa.dataset.sublabel = subetiqueta;
                if (icono) opcionNativa.dataset.icon = icono;
                selectNativo.appendChild(opcionNativa);
            }

            selectNativo.value = valor;
            selectNativo.dispatchEvent(new Event('change', { bubbles: true }));

            actualizarInterfazSeleccionada(valor, etiqueta, subetiqueta, icono);
            cerrarDesplegable();
            botonDisparador.focus();
        });

        document.addEventListener('click', (evento) => {
            if (estaAbierto && !contenedor.contains(evento.target)) {
                cerrarDesplegable();
            }
        });

        selectNativo.addEventListener('change', () => {
            const opcionSeleccionada = selectNativo.options[selectNativo.selectedIndex];
            if (opcionSeleccionada) {
                actualizarInterfazSeleccionada(
                    opcionSeleccionada.value,
                    opcionSeleccionada.text,
                    opcionSeleccionada.dataset.sublabel || '',
                    opcionSeleccionada.dataset.icon || ''
                );
            }
        });

        selectNativo.form?.addEventListener('reset', () => {
            setTimeout(() => {
                actualizarInterfazSeleccionada('', '');
            }, 0);
        });

        const opcionInicial = selectNativo.options[selectNativo.selectedIndex];
        if (opcionInicial && opcionInicial.value) {
            actualizarInterfazSeleccionada(
                opcionInicial.value,
                opcionInicial.text,
                opcionInicial.dataset.sublabel || '',
                opcionInicial.dataset.icon || ''
            );
        }
    });
};

/**
 * Actualiza dinámicamente las opciones de un select personalizado.
 * @param {string|HTMLElement} selector
 * @param {Array|Object} opciones
 * @param {string|number} [valorSeleccionado='']
 */
export const actualizarOpcionesSelect = (selector, opciones = [], valorSeleccionado = '') => {
    const elemento = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!elemento) return;

    const contenedor = elemento.closest('[data-custom-select]');
    if (!contenedor) return;

    const selectNativo = contenedor.querySelector('.custom-select-native');
    const listaOpciones = contenedor.querySelector('.custom-select-options');
    const botonDisparador = contenedor.querySelector('.custom-select-trigger');
    const visualizadorSeleccionado = botonDisparador?.querySelector('.custom-select-display');
    const textoPlaceholder = selectNativo.getAttribute('data-placeholder') || 'Seleccionar opción...';

    const normalizadas = normalizarOpciones(opciones);

    const valorObjetivo = String(valorSeleccionado || selectNativo.value || '');
    const existe = normalizadas.some((opcion) => opcion.valor === valorObjetivo);
    const valorFinal = existe ? valorObjetivo : '';

    selectNativo.innerHTML = `<option value="">${escaparAtributo(textoPlaceholder)}</option>`;
    normalizadas.forEach((opcion) => {
        const elementoOpcion = document.createElement('option');
        elementoOpcion.value = opcion.valor;
        elementoOpcion.textContent = opcion.etiqueta;
        if (opcion.subetiqueta) elementoOpcion.dataset.sublabel = opcion.subetiqueta;
        if (opcion.icono) elementoOpcion.dataset.icon = opcion.icono;
        if (opcion.valor === valorFinal) elementoOpcion.selected = true;
        selectNativo.appendChild(elementoOpcion);
    });

    if (listaOpciones) {
        listaOpciones.innerHTML = construirHtmlOpciones(normalizadas, valorFinal);
    }

    selectNativo.value = valorFinal;
    if (visualizadorSeleccionado) {
        if (valorFinal) {
            const coincidencia = normalizadas.find((opcion) => opcion.valor === valorFinal);
            let displayHtml = '<div class="flex items-center gap-2.5 truncate">';
            if (coincidencia?.icono) displayHtml += `<span class="text-base shrink-0">${escaparAtributo(coincidencia.icono)}</span>`;
            displayHtml += `<span class="text-xs font-semibold text-zinc-900 truncate">${escaparAtributo(coincidencia?.etiqueta || valorFinal)}</span>`;
            if (coincidencia?.subetiqueta) displayHtml += `<span class="text-xs text-zinc-500 truncate max-w-30">${escaparAtributo(coincidencia.subetiqueta)}</span>`;
            displayHtml += '</div>';
            visualizadorSeleccionado.innerHTML = displayHtml;
        } else {
            visualizadorSeleccionado.innerHTML = `<span class="text-xs text-zinc-400 truncate">${escaparAtributo(textoPlaceholder)}</span>`;
        }
    }

    selectNativo.dispatchEvent(new Event('change', { bubbles: true }));
};

export const initCustomSelects = inicializarSelectsPersonalizados;
export const updateSelectOptions = actualizarOpcionesSelect;
