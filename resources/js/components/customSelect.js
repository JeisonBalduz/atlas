/**
 * customSelect.js
 * Componente de Select con Buscador y Desplegable Inteligente (dropUp / dropDown).
 * Soporta filtrado en tiempo real de cientos o miles de registros pasados desde Blade,
 * así como actualización dinámica de opciones (ej: días según mes/año).
 */

export const initCustomSelects = (selector = '[data-custom-select]') => {
    const containers = document.querySelectorAll(selector);

    containers.forEach(container => {
        if (container.dataset.customSelectInitialized) return;
        container.dataset.customSelectInitialized = 'true';

        const nativeSelect = container.querySelector('.custom-select-native');
        const triggerBtn = container.querySelector('.custom-select-trigger');
        const dropdown = container.querySelector('.custom-select-dropdown');
        const searchInput = container.querySelector('.custom-select-search');
        const optionsList = container.querySelector('.custom-select-options');
        const emptyMsg = container.querySelector('.custom-select-empty');
        const chevron = triggerBtn.querySelector('[data-lucide="chevron-down"]');
        const selectedDisplay = triggerBtn.querySelector('.custom-select-display');

        if (!nativeSelect || !triggerBtn || !dropdown) return;

        let isOpen = false;

        const updateSelectedUI = (value, label, sublabel = '', icon = '') => {
            if (!value) {
                const placeholder = nativeSelect.getAttribute('data-placeholder') || 'Seleccionar opción...';
                selectedDisplay.innerHTML = `<span class="text-xs text-zinc-400 truncate">${placeholder}</span>`;
            } else {
                let html = '<div class="flex items-center gap-2.5 truncate">';
                if (icon) html += `<span class="text-base shrink-0">${icon}</span>`;
                html += `<span class="text-xs font-semibold text-zinc-900 truncate">${label}</span>`;
                if (sublabel) html += `<span class="text-xs text-zinc-500 truncate max-w-30">${sublabel}</span>`;
                html += '</div>';
                selectedDisplay.innerHTML = html;
            }

            // Actualizar checks en la lista
            dropdown.querySelectorAll('[data-select-item]').forEach(item => {
                const isMatch = item.dataset.value === value;
                const checkIcon = item.querySelector('.custom-select-check');
                if (checkIcon) {
                    checkIcon.classList.toggle('hidden', !isMatch);
                }
                item.classList.toggle('bg-zinc-100', isMatch);
            });
        };

        const openDropdown = () => {
            isOpen = true;
            dropdown.classList.remove('hidden');

            // Posicionamiento inteligente: si hay menos de 280px debajo, abre hacia arriba
            const rect = triggerBtn.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const dropUp = spaceBelow < 280;

            if (dropUp) {
                dropdown.classList.add('bottom-full', 'mb-1.5', 'origin-bottom');
                dropdown.classList.remove('top-full', 'mt-1.5', 'origin-top');
            } else {
                dropdown.classList.add('top-full', 'mt-1.5', 'origin-top');
                dropdown.classList.remove('bottom-full', 'mb-1.5', 'origin-bottom');
            }

            if (chevron) chevron.classList.add('rotate-180');
            if (searchInput) {
                searchInput.value = '';
                filterItems('');
                setTimeout(() => searchInput.focus(), 50);
            }
        };

        const closeDropdown = () => {
            isOpen = false;
            dropdown.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        };

        const filterItems = (query) => {
            const cleanQuery = query.toLowerCase().trim();
            const items = dropdown.querySelectorAll('[data-select-item]');
            let visibleCount = 0;

            items.forEach(item => {
                const text = item.dataset.search || item.textContent.toLowerCase();
                const matches = !cleanQuery || text.includes(cleanQuery);
                item.classList.toggle('hidden', !matches);
                if (matches) visibleCount++;
            });

            if (emptyMsg) {
                emptyMsg.classList.toggle('hidden', visibleCount > 0);
            }
        };

        // Eventos
        triggerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen ? closeDropdown() : openDropdown();
        });

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                filterItems(e.target.value);
            });
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') e.preventDefault();
                if (e.key === 'Escape') closeDropdown();
            });
        }

        dropdown.addEventListener('click', (e) => {
            const item = e.target.closest('[data-select-item]');
            if (!item) return;

            const val = item.dataset.value;
            const label = item.dataset.label;
            const sublabel = item.dataset.sublabel || '';
            const icon = item.dataset.icon || '';

            nativeSelect.value = val;
            nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));

            updateSelectedUI(val, label, sublabel, icon);
            closeDropdown();
            triggerBtn.focus();
        });

        // Click outside para cerrar
        document.addEventListener('click', (e) => {
            if (isOpen && !container.contains(e.target)) {
                closeDropdown();
            }
        });

        // Escuchar cambios externos en el nativeSelect
        nativeSelect.addEventListener('change', () => {
            const selectedOpt = nativeSelect.options[nativeSelect.selectedIndex];
            if (selectedOpt) {
                updateSelectedUI(
                    selectedOpt.value,
                    selectedOpt.text,
                    selectedOpt.dataset.sublabel || '',
                    selectedOpt.dataset.icon || ''
                );
            }
        });

        // Sincronizar valor inicial si ya viene seleccionado
        const initialOpt = nativeSelect.options[nativeSelect.selectedIndex];
        if (initialOpt && initialOpt.value) {
            updateSelectedUI(
                initialOpt.value,
                initialOpt.text,
                initialOpt.dataset.sublabel || '',
                initialOpt.dataset.icon || ''
            );
        }
    });
};

/**
 * Actualiza dinámicamente las opciones de un select personalizado.
 * Útil para campos dependientes como Mes -> Días, Estado -> Municipio, etc.
 * @param {string|HTMLElement} selector - Selector del campo (ej: '#dia') o elemento
 * @param {Array|Object} options - Nuevas opciones a inyectar
 * @param {string|number} [selectedValue=''] - Valor a mantener/seleccionar si es válido
 */
export const updateSelectOptions = (selector, options = [], selectedValue = '') => {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return;

    const container = el.closest('[data-custom-select]');
    if (!container) return;

    const nativeSelect = container.querySelector('.custom-select-native');
    const optionsList = container.querySelector('.custom-select-options');
    const triggerBtn = container.querySelector('.custom-select-trigger');
    const selectedDisplay = triggerBtn.querySelector('.custom-select-display');
    const placeholder = nativeSelect.getAttribute('data-placeholder') || 'Seleccionar opción...';

    // Normalizar opciones a lista de objetos { value, label, sublabel, icon }
    let normalized = [];
    if (Array.isArray(options)) {
        normalized = options.map(item => {
            if (typeof item === 'object' && item !== null) {
                return {
                    value: String(item.value ?? item.id ?? ''),
                    label: String(item.label ?? item.name ?? item.text ?? item.value ?? ''),
                    sublabel: item.sublabel || '',
                    icon: item.icon || item.flag || ''
                };
            }
            return { value: String(item), label: String(item), sublabel: '', icon: '' };
        });
    } else if (typeof options === 'object' && options !== null) {
        normalized = Object.entries(options).map(([k, v]) => {
            if (typeof v === 'object' && v !== null) {
                return {
                    value: String(v.value ?? k),
                    label: String(v.label ?? v.name ?? k),
                    sublabel: v.sublabel || '',
                    icon: v.icon || ''
                };
            }
            return { value: String(k), label: String(v), sublabel: '', icon: '' };
        });
    }

    // Verificar si el valor seleccionado sigue existiendo en el nuevo rango
    const targetValue = String(selectedValue || nativeSelect.value || '');
    const exists = normalized.some(opt => opt.value === targetValue);
    const finalValue = exists ? targetValue : '';

    // 1. Reconstruir <select> nativo
    nativeSelect.innerHTML = `<option value="">${placeholder}</option>`;
    normalized.forEach(opt => {
        const optionEl = document.createElement('option');
        optionEl.value = opt.value;
        optionEl.textContent = opt.label;
        if (opt.sublabel) optionEl.dataset.sublabel = opt.sublabel;
        if (opt.icon) optionEl.dataset.icon = opt.icon;
        if (opt.value === finalValue) optionEl.selected = true;
        nativeSelect.appendChild(optionEl);
    });

    // 2. Reconstruir lista visual del dropdown
    let itemsHtml = '';
    normalized.forEach(opt => {
        const isMatch = opt.value === finalValue;
        const searchMeta = `${opt.value} ${opt.label} ${opt.sublabel}`.toLowerCase();
        itemsHtml += `
            <div data-select-item
                 data-value="${opt.value}"
                 data-label="${opt.label}"
                 data-sublabel="${opt.sublabel}"
                 data-icon="${opt.icon}"
                 data-search="${searchMeta}"
                 class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors ${isMatch ? 'bg-zinc-100' : ''}">
                <div class="flex items-center gap-2.5 truncate">
                    ${opt.icon ? `<span class="text-base shrink-0">${opt.icon}</span>` : ''}
                    <span class="font-semibold text-zinc-900 truncate">${opt.label}</span>
                    ${opt.sublabel ? `<span class="text-zinc-500 truncate max-w-30">${opt.sublabel}</span>` : ''}
                </div>
                <i data-lucide="check" class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 ${isMatch ? '' : 'hidden'}"></i>
            </div>
        `;
    });

    itemsHtml += `
        <div class="custom-select-empty hidden px-3 py-4 text-center text-xs text-zinc-400">
            No se encontraron registros.
        </div>
    `;

    optionsList.innerHTML = itemsHtml;
    if (window.lucide) window.lucide.createIcons();

    // 3. Actualizar texto visible en el botón disparador
    nativeSelect.value = finalValue;
    if (finalValue) {
        const matched = normalized.find(o => o.value === finalValue);
        let displayHtml = '<div class="flex items-center gap-2.5 truncate">';
        if (matched?.icon) displayHtml += `<span class="text-base shrink-0">${matched.icon}</span>`;
        displayHtml += `<span class="text-xs font-semibold text-zinc-900 truncate">${matched?.label || finalValue}</span>`;
        if (matched?.sublabel) displayHtml += `<span class="text-xs text-zinc-500 truncate max-w-30">${matched.sublabel}</span>`;
        displayHtml += '</div>';
        selectedDisplay.innerHTML = displayHtml;
    } else {
        selectedDisplay.innerHTML = `<span class="text-xs text-zinc-400 truncate">${placeholder}</span>`;
    }

    // 4. Notificar cambio para que InputValidator actualice el estado del campo
    nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
};
