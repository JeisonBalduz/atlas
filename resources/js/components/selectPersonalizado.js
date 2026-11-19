/**
 * selectPersonalizado.js
 * Componente de Select con Buscador y Desplegable Inteligente (hacia arriba / hacia abajo).
 * Soporta filtrado en tiempo real de registros pasados desde la vista,
 * así como actualización dinámica de opciones (ej: días según mes y año).
 */

/**
 * Inicializa los selectores personalizados en el DOM.
 * @param {string} selector - Selector CSS para encontrar los contenedores (por defecto: '[data-custom-select]')
 */
export const inicializarSelectsPersonalizados = (selector = '[data-custom-select]') => {
    const contenedores = document.querySelectorAll(selector);

    contenedores.forEach(contenedor => {
        if (contenedor.dataset.selectPersonalizadoInicializado || contenedor.dataset.customSelectInitialized) return;
        contenedor.dataset.selectPersonalizadoInicializado = 'true';
        contenedor.dataset.customSelectInitialized = 'true';

        const selectNativo = contenedor.querySelector('.custom-select-native');
        const botonDisparador = contenedor.querySelector('.custom-select-trigger');
        const menuDesplegable = contenedor.querySelector('.custom-select-dropdown');
        const campoBusqueda = contenedor.querySelector('.custom-select-search');
        const listaOpciones = contenedor.querySelector('.custom-select-options');
        const mensajeVacio = contenedor.querySelector('.custom-select-empty');
        const iconoChevron = botonDisparador?.querySelector('[data-lucide="chevron-down"]');
        const visualizadorSeleccionado = botonDisparador?.querySelector('.custom-select-display');

        if (!selectNativo || !botonDisparador || !menuDesplegable || !visualizadorSeleccionado) return;

        let estaAbierto = false;

        const actualizarInterfazSeleccionada = (valor, etiqueta, subetiqueta = '', icono = '') => {
            if (!valor) {
                const textoPlaceholder = selectNativo.getAttribute('data-placeholder') || 'Seleccionar opción...';
                visualizadorSeleccionado.innerHTML = `<span class="text-xs text-zinc-400 truncate">${textoPlaceholder}</span>`;
            } else {
                let html = '<div class="flex items-center gap-2.5 truncate">';
                if (icono) html += `<span class="text-base shrink-0">${icono}</span>`;
                html += `<span class="text-xs font-semibold text-zinc-900 truncate">${etiqueta}</span>`;
                if (subetiqueta) html += `<span class="text-xs text-zinc-500 truncate max-w-30">${subetiqueta}</span>`;
                html += '</div>';
                visualizadorSeleccionado.innerHTML = html;
            }

            // Actualizar marcas de selección en la lista
            menuDesplegable.querySelectorAll('[data-select-item]').forEach(elementoOpcion => {
                const esCoincidencia = elementoOpcion.dataset.value === valor;
                const iconoCheck = elementoOpcion.querySelector('.custom-select-check');
                if (iconoCheck) {
                    iconoCheck.classList.toggle('hidden', !esCoincidencia);
                }
                elementoOpcion.classList.toggle('bg-zinc-100', esCoincidencia);
            });
        };

        const abrirDesplegable = () => {
            estaAbierto = true;
            menuDesplegable.classList.remove('hidden');

            // Posicionamiento inteligente: si hay menos de 280px debajo, abre hacia arriba
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
                filtrarElementos('');
                setTimeout(() => campoBusqueda.focus(), 50);
            }
        };

        const cerrarDesplegable = () => {
            estaAbierto = false;
            menuDesplegable.classList.add('hidden');
            if (iconoChevron) iconoChevron.classList.remove('rotate-180');
        };

        const filtrarElementos = (consulta) => {
            const consultaLimpia = consulta.toLowerCase().trim();
            const elementos = menuDesplegable.querySelectorAll('[data-select-item]');
            let elementosVisibles = 0;

            elementos.forEach(elementoOpcion => {
                const texto = elementoOpcion.dataset.search || elementoOpcion.textContent.toLowerCase();
                const coincide = !consultaLimpia || texto.includes(consultaLimpia);
                elementoOpcion.classList.toggle('hidden', !coincide);
                if (coincide) elementosVisibles++;
            });

            if (mensajeVacio) {
                mensajeVacio.classList.toggle('hidden', elementosVisibles > 0);
            }
        };

        // Eventos
        botonDisparador.addEventListener('click', (evento) => {
            evento.stopPropagation();
            estaAbierto ? cerrarDesplegable() : abrirDesplegable();
        });

        if (campoBusqueda) {
            campoBusqueda.addEventListener('input', (evento) => {
                filtrarElementos(evento.target.value);
            });
            campoBusqueda.addEventListener('keydown', (evento) => {
                if (evento.key === 'Enter') evento.preventDefault();
                if (evento.key === 'Escape') cerrarDesplegable();
            });
        }

        menuDesplegable.addEventListener('click', (evento) => {
            const elementoOpcion = evento.target.closest('[data-select-item]');
            if (!elementoOpcion) return;

            const valor = elementoOpcion.dataset.value;
            const etiqueta = elementoOpcion.dataset.label;
            const subetiqueta = elementoOpcion.dataset.sublabel || '';
            const icono = elementoOpcion.dataset.icon || '';

            selectNativo.value = valor;
            selectNativo.dispatchEvent(new Event('change', { bubbles: true }));

            actualizarInterfazSeleccionada(valor, etiqueta, subetiqueta, icono);
            cerrarDesplegable();
            botonDisparador.focus();
        });

        // Cerrar al hacer clic fuera
        document.addEventListener('click', (evento) => {
            if (estaAbierto && !contenedor.contains(evento.target)) {
                cerrarDesplegable();
            }
        });

        // Escuchar cambios externos en el select nativo
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

        // Sincronizar valor inicial si ya viene seleccionado
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
 * Útil para campos dependientes como Mes -> Días, Estado -> Municipio, etc.
 * @param {string|HTMLElement} selector - Selector del campo (ej: '#dia') o elemento DOM
 * @param {Array|Object} opciones - Nuevas opciones a inyectar
 * @param {string|number} [valorSeleccionado=''] - Valor a mantener/seleccionar si es válido
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

    // Normalizar opciones a lista de objetos { valor, etiqueta, subetiqueta, icono }
    let normalizadas = [];
    if (Array.isArray(opciones)) {
        normalizadas = opciones.map(item => {
            if (typeof item === 'object' && item !== null) {
                return {
                    valor: String(item.valor ?? item.value ?? item.id ?? ''),
                    etiqueta: String(item.etiqueta ?? item.label ?? item.nombre ?? item.name ?? item.texto ?? item.text ?? item.valor ?? item.value ?? ''),
                    subetiqueta: item.subetiqueta || item.sublabel || '',
                    icono: item.icono || item.icon || item.flag || ''
                };
            }
            return { valor: String(item), etiqueta: String(item), subetiqueta: '', icono: '' };
        });
    } else if (typeof opciones === 'object' && opciones !== null) {
        normalizadas = Object.entries(opciones).map(([clave, valor]) => {
            if (typeof valor === 'object' && valor !== null) {
                return {
                    valor: String(valor.valor ?? valor.value ?? clave),
                    etiqueta: String(valor.etiqueta ?? valor.label ?? valor.nombre ?? valor.name ?? clave),
                    subetiqueta: valor.subetiqueta || valor.sublabel || '',
                    icono: valor.icono || valor.icon || ''
                };
            }
            return { valor: String(clave), etiqueta: String(valor), subetiqueta: '', icono: '' };
        });
    }

    // Verificar si el valor seleccionado sigue existiendo en el nuevo rango
    const valorObjetivo = String(valorSeleccionado || selectNativo.value || '');
    const existe = normalizadas.some(opcion => opcion.valor === valorObjetivo);
    const valorFinal = existe ? valorObjetivo : '';

    // 1. Reconstruir <select> nativo
    selectNativo.innerHTML = `<option value="">${textoPlaceholder}</option>`;
    normalizadas.forEach(opcion => {
        const elementoOpcion = document.createElement('option');
        elementoOpcion.value = opcion.valor;
        elementoOpcion.textContent = opcion.etiqueta;
        if (opcion.subetiqueta) elementoOpcion.dataset.sublabel = opcion.subetiqueta;
        if (opcion.icono) elementoOpcion.dataset.icon = opcion.icono;
        if (opcion.valor === valorFinal) elementoOpcion.selected = true;
        selectNativo.appendChild(elementoOpcion);
    });

    // 2. Reconstruir lista visual del desplegable
    let htmlElementos = '';
    normalizadas.forEach(opcion => {
        const esCoincidencia = opcion.valor === valorFinal;
        const metaBusqueda = `${opcion.valor} ${opcion.etiqueta} ${opcion.subetiqueta}`.toLowerCase();
        htmlElementos += `
            <div data-select-item
                 data-value="${opcion.valor}"
                 data-label="${opcion.etiqueta}"
                 data-sublabel="${opcion.subetiqueta}"
                 data-icon="${opcion.icono}"
                 data-search="${metaBusqueda}"
                 class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors ${esCoincidencia ? 'bg-zinc-100' : ''}">
                <div class="flex items-center gap-2.5 truncate">
                    ${opcion.icono ? `<span class="text-base shrink-0">${opcion.icono}</span>` : ''}
                    <span class="font-semibold text-zinc-900 truncate">${opcion.etiqueta}</span>
                    ${opcion.subetiqueta ? `<span class="text-zinc-500 truncate max-w-30">${opcion.subetiqueta}</span>` : ''}
                </div>
                <i data-lucide="check" class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 ${esCoincidencia ? '' : 'hidden'}"></i>
            </div>
        `;
    });

    htmlElementos += `
        <div class="custom-select-empty hidden px-3 py-4 text-center text-xs text-zinc-400">
            No se encontraron registros.
        </div>
    `;

    if (listaOpciones) {
        listaOpciones.innerHTML = htmlElementos;
    }
    if (window.lucide) window.lucide.createIcons();

    // 3. Actualizar texto visible en el botón disparador
    selectNativo.value = valorFinal;
    if (visualizadorSeleccionado) {
        if (valorFinal) {
            const coincidencia = normalizadas.find(opcion => opcion.valor === valorFinal);
            let displayHtml = '<div class="flex items-center gap-2.5 truncate">';
            if (coincidencia?.icono) displayHtml += `<span class="text-base shrink-0">${coincidencia.icono}</span>`;
            displayHtml += `<span class="text-xs font-semibold text-zinc-900 truncate">${coincidencia?.etiqueta || valorFinal}</span>`;
            if (coincidencia?.subetiqueta) displayHtml += `<span class="text-xs text-zinc-500 truncate max-w-30">${coincidencia.subetiqueta}</span>`;
            displayHtml += '</div>';
            visualizadorSeleccionado.innerHTML = displayHtml;
        } else {
            visualizadorSeleccionado.innerHTML = `<span class="text-xs text-zinc-400 truncate">${textoPlaceholder}</span>`;
        }
    }

    // 4. Notificar cambio para que InputValidador actualice el estado del campo
    selectNativo.dispatchEvent(new Event('change', { bubbles: true }));
};

// Alias para retrocompatibilidad
export const initCustomSelects = inicializarSelectsPersonalizados;
export const updateSelectOptions = actualizarOpcionesSelect;
