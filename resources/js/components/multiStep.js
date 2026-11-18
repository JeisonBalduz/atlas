import { InputUI } from '../class/validator/InputUI.js';

/**
 * multiStep.js
 * Formulario por pasos. Cada <section data-step data-step-title="..." data-step-icon="..."> es un paso.
 * El indicador superior se genera solo: agregar un paso = agregar otra <section>.
 *
 * Estructura esperada dentro del <form>:
 *   <ol data-step-indicator></ol>
 *   <div data-step-viewport> <section data-step ...> ... </section> </div>
 *   <button data-step-prev> / <button data-step-next><span data-step-next-label></span></button>
 *   <span data-step-counter></span> (opcional)
 */

const EASE = 'cubic-bezier(0.32, 0.72, 0, 1)';

const DOT_BASE = 'relative grid size-11 shrink-0 place-items-center rounded-full border-2 transition-all duration-500';
const DOT_STATE = {
    done: 'border-marca bg-marca text-white',
    current: 'border-marca bg-white text-marca ring-4 ring-marca-100',
    pending: 'border-slate-300 bg-white text-zinc-400',
};
const LABEL_STATE = { done: 'text-zinc-900', current: 'text-marca', pending: 'text-zinc-500' };

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const getFields = (step) => [...step.querySelectorAll('input, select, textarea')]
    .filter((field) => !field.disabled && field.type !== 'hidden');

const isEmpty = (field) => (field.type === 'file' ? field.files.length === 0 : !field.value.trim());

const labelOf = (field) => document.querySelector(`label[for="${field.id}"]`)?.textContent.trim() || field.name;

const titleOf = (step, i) => step.dataset.stepTitle ?? `Paso ${i + 1}`;

// Devuelve el problema del campo o null si está correcto
const getProblem = (field) => {
    if (field.required && isEmpty(field)) return 'Este campo es obligatorio';
    if (field.classList.contains('border-red-500')) {
        return InputUI.getErrorElement(field)?.textContent || 'Valor inválido';
    }
    return null;
};

const validateStep = (step) => {
    const problems = getFields(step)
        .map((field) => ({ field, msg: getProblem(field) }))
        .filter(({ msg }) => msg);

    if (!problems.length) return true;

    problems.forEach(({ field, msg }) => InputUI.setInvalid(field, msg));
    const list = problems.map(({ field, msg }) => `• ${labelOf(field)}: ${msg}`).join('\n');
    window.alert(`No puedes continuar, revisa los siguientes campos:\n\n${list}`);
    problems[0].field.focus();
    return false;
};

const buildIndicator = (list, steps) => {
    const last = steps.length - 1;
    // Primer y último label alineados al borde para que no se salgan de la card
    const labelPos = (i) => (i === 0 ? 'left-0' : i === last ? 'right-0' : 'left-1/2 -translate-x-1/2');

    list.innerHTML = steps.map((step, i) => `
        <li class="flex items-center ${i < last ? 'flex-1' : ''}">
            <span data-dot class="${DOT_BASE}">
                <i data-lucide="${step.dataset.stepIcon}" class="size-5"></i>
                <span data-label class="absolute top-full mt-2 whitespace-nowrap text-xs font-medium transition-colors duration-300 ${labelPos(i)}"></span>
            </span>
            ${i < last ? `
            <span class="mx-3 h-1 flex-1 overflow-hidden rounded-full bg-slate-200">
                <span data-bar class="block h-full origin-left scale-x-0 rounded-full bg-marca transition-transform duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]"></span>
            </span>` : ''}
        </li>`).join('');

    // textContent evita inyectar HTML desde los títulos
    list.querySelectorAll('[data-label]').forEach((label, i) => { label.textContent = titleOf(steps[i], i); });
    window.lucide?.createIcons();
};

/**
 * Inicializa el formulario por pasos.
 * @param {string} selector - Selector del <form>
 * @param {Object} options
 * @param {(form: HTMLFormElement) => void} [options.onFinish] - Se ejecuta al validar el último paso
 */
export const initMultiStep = (selector, { onFinish } = {}) => {
    const form = document.querySelector(selector);
    if (!form) return null;

    const steps = [...form.querySelectorAll('[data-step]')];
    const viewport = form.querySelector('[data-step-viewport]') ?? steps[0].parentElement;
    const indicator = form.querySelector('[data-step-indicator]');
    const prevBtn = form.querySelector('[data-step-prev]');
    const nextBtn = form.querySelector('[data-step-next]');
    const nextLabel = form.querySelector('[data-step-next-label]');
    const counter = form.querySelector('[data-step-counter]');
    const last = steps.length - 1;

    let current = 0;
    let busy = false;

    if (indicator) buildIndicator(indicator, steps);
    const dots = [...form.querySelectorAll('[data-dot]')];
    const labels = [...form.querySelectorAll('[data-label]')];
    const bars = [...form.querySelectorAll('[data-bar]')];

    const render = () => {
        dots.forEach((dot, i) => {
            const state = i < current ? 'done' : i === current ? 'current' : 'pending';
            dot.className = `${DOT_BASE} ${DOT_STATE[state]}`;
            dot.closest('li').setAttribute('aria-current', i === current ? 'step' : 'false');
            labels[i].classList.remove(...Object.values(LABEL_STATE));
            labels[i].classList.add(LABEL_STATE[state]);
        });
        bars.forEach((bar, i) => {
            bar.classList.toggle('scale-x-100', i < current);
            bar.classList.toggle('scale-x-0', i >= current);
        });
        prevBtn?.classList.toggle('invisible', current === 0);
        if (nextLabel) nextLabel.textContent = current === last ? 'Finalizar' : 'Siguiente';
        if (counter) counter.textContent = `Paso ${current + 1} de ${steps.length}`;
    };

    const goTo = async (to) => {
        if (busy || to === current || to < 0 || to > last) return;
        busy = true;

        const from = steps[current];
        const next = steps[to];
        const dir = Math.sign(to - current);
        const dx = reducedMotion() ? 0 : 40;
        const startHeight = viewport.offsetHeight;

        // 1. Sale el paso actual hacia el lado contrario
        const out = from.animate(
            [{ opacity: 1, transform: 'translateX(0)' }, { opacity: 0, transform: `translateX(${-dir * dx}px)` }],
            { duration: 180, easing: 'ease-in', fill: 'forwards' },
        );
        await out.finished;
        from.hidden = true;
        out.cancel();

        // 2. Entra el nuevo paso y la altura se ajusta suavemente
        next.hidden = false;
        current = to;
        render();

        viewport.style.overflow = 'hidden';
        const endHeight = viewport.offsetHeight;
        const animations = [
            viewport.animate([{ height: `${startHeight}px` }, { height: `${endHeight}px` }], { duration: 400, easing: EASE }),
            next.animate(
                [{ opacity: 0, transform: `translateX(${dir * dx}px)` }, { opacity: 1, transform: 'translateX(0)' }],
                { duration: 400, easing: EASE },
            ),
        ];
        form.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'start' });

        await Promise.all(animations.map((a) => a.finished));
        viewport.style.overflow = '';
        getFields(next)[0]?.focus({ preventScroll: true });
        busy = false;
    };

    nextBtn?.addEventListener('click', () => {
        if (busy || !validateStep(steps[current])) return;
        if (current === last) return onFinish?.(form);
        goTo(current + 1);
    });
    prevBtn?.addEventListener('click', () => goTo(current - 1));
    form.addEventListener('submit', (e) => e.preventDefault());

    steps.forEach((s, i) => { s.hidden = i !== 0; });
    render();

    return { goTo, get current() { return current; } };
};
