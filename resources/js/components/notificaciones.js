function injectToastStyles() {
    if (document.getElementById('toast-immutable-styles')) return;

    const styleEl = document.createElement('style');
    styleEl.id = 'toast-immutable-styles';
    styleEl.textContent = `
        @keyframes modernSlideIn {
            0% { opacity: 0; transform: translateY(-16px) scale(0.92); }
            60% { opacity: 1; transform: translateY(2px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes modernSlideOut {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-12px) scale(0.95); }
        }
        @keyframes drawLine24 {
            0% { stroke-dashoffset: 24; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes drawLine60 {
            0% { stroke-dashoffset: 60; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes drawLine75 {
            0% { stroke-dashoffset: 75; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.15); }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-3px); }
            40%, 80% { transform: translateX(3px); }
        }
        .toast-anim-in { animation: modernSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .toast-anim-out { animation: modernSlideOut 0.25s cubic-bezier(0.4, 0, 1, 1) forwards; }
        .toast-draw-check { stroke-dasharray: 24; stroke-dashoffset: 24; animation: drawLine24 0.35s ease-in-out forwards; }
        .toast-draw-x1 { stroke-dasharray: 18; stroke-dashoffset: 18; animation: drawLine24 0.2s ease-in-out forwards; }
        .toast-draw-x2 { stroke-dasharray: 18; stroke-dashoffset: 18; animation: drawLine24 0.2s ease-in-out 0.15s forwards; }
        .toast-draw-triangle { stroke-dasharray: 75; stroke-dashoffset: 75; animation: drawLine75 0.4s ease-in-out forwards; }
        .toast-draw-circle { stroke-dasharray: 60; stroke-dashoffset: 60; animation: drawLine60 0.4s ease-in-out forwards; }
        .toast-pop { animation: popIn 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        .toast-shake { animation: shake 0.35s ease-in-out forwards; }
    `;
    document.head.appendChild(styleEl);
}

// 2. Contenedor autónomo
function getOrCreateContainer(position) {
    const containerId = 'toast-container-' + position;
    let container = document.getElementById(containerId);
    
    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        
        const positionClasses = {
            'top-center': 'top: 20px; left: 50%; transform: translateX(-50%); align-items: center;',
            'top-right': 'top: 20px; right: 20px; align-items: flex-end;',
            'top-left': 'top: 20px; left: 20px; align-items: flex-start;',
            'bottom-center': 'bottom: 20px; left: 50%; transform: translateX(-50%); align-items: center;',
            'bottom-right': 'bottom: 20px; right: 20px; align-items: flex-end;',
            'bottom-left': 'bottom: 20px; left: 20px; align-items: flex-start;'
        };

        container.style.cssText = "position: fixed; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; max-width: 380px; width: 100%; padding: 0 16px; box-sizing: border-box; " + (positionClasses[position] || positionClasses['top-center']);
        document.body.appendChild(container);
    }
    return container;
}

// 3. Configuraciones SVG con medidas estrictas e inmutables (width/height en px fijos)
function getToastConfig(type) {
    switch (type) {
        case 'success':
            return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(16, 185, 129, 0.15); color: #059669; display: flex; align-items: center; justify-content: center;" class="toast-pop">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 3px;" viewBox="0 0 24 24">
                        <path class="toast-draw-check" stroke-linecap="round" stroke-linejoin="round" d="M4 12l6 6L20 6" />
                    </svg>
                </div>`;
        case 'error':
            return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(244, 63, 94, 0.15); color: #e11d48; display: flex; align-items: center; justify-content: center;" class="toast-shake">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 3px;" viewBox="0 0 24 24">
                        <path class="toast-draw-x1" stroke-linecap="butt" d="M6 6l12 12" />
                        <path class="toast-draw-x2" stroke-linecap="butt" d="M18 6L6 18" />
                    </svg>
                </div>`;
        case 'warning':
            return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(245, 158, 11, 0.15); color: #d97706; display: flex; align-items: center; justify-content: center;" class="toast-pop">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2.5px;" viewBox="0 0 24 24">
                        <path class="toast-draw-triangle" stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 3.5h.01" />
                    </svg>
                </div>`;
        case 'info':
            return `
                <div style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; border-radius: 8px; background-color: rgba(99, 102, 241, 0.15); color: #4f46e5; display: flex; align-items: center; justify-content: center;" class="toast-pop">
                    <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2.5px;" viewBox="0 0 24 24">
                        <circle class="toast-draw-circle" cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 16v-4m0-3.5h.01" />
                    </svg>
                </div>`;
        case 'loading':
        default:
            return `
                <div style="width: 24px; height: 24px; min-width: 24px; display: flex; align-items: center; justify-content: center;" class="animate-spin">
                    <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"></path>
                    </svg>
                </div>`;
    }
}

// 4. Funciones principales
function showToast(type, message, position = 'top-center', duration = 4000) {
    injectToastStyles();
    const container = getOrCreateContainer(position);
    const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).substring(2, 5);
    const iconHtml = getToastConfig(type);

    const toastEl = document.createElement('div');
    toastEl.id = id;
    toastEl.style.cssText = "pointer-events: auto; width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); font-size: 12px; font-weight: 500; font-family: ui-sans-serif, system-ui, sans-serif; color: #1f2937; box-sizing: border-box;";
    
    toastEl.className = "toast-anim-in";

    toastEl.innerHTML = `
        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
            <div style="position: relative; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" class="toast-icon-wrap">
                ${iconHtml}
            </div>
            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f2937;" class="toast-text">${message}</span>
        </div>
        <button onclick="removeToast('${id}')" style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: color 0.2s;" onmouseover="this.style.color='#4b5563'" onmouseout="this.style.color='#9ca3af'">
            <svg style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2.5px;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;

    container.appendChild(toastEl);

    if (type !== 'loading' && duration > 0) {
        setTimeout(() => removeToast(id), duration);
    }

    return id;
}

function updateToast(id, newType, newMessage, duration = 4000) {
    const toastEl = document.getElementById(id);
    if (!toastEl) return;

    const iconWrap = toastEl.querySelector('.toast-icon-wrap');
    const messageEl = toastEl.querySelector('.toast-text');

    messageEl.textContent = newMessage;
    iconWrap.innerHTML = getToastConfig(newType);

    if (newType !== 'loading' && duration > 0) {
        setTimeout(() => removeToast(id), duration);
    }
}

function removeToast(id) {
    const toastEl = document.getElementById(id);
    if (toastEl && !toastEl.classList.contains('closing')) {
        toastEl.classList.add('closing');
        toastEl.classList.remove('toast-anim-in');
        toastEl.classList.add('toast-anim-out');
        
        setTimeout(() => toastEl.remove(), 250);
    }
}