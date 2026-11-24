import { InputUI } from '/js/class/validator/InputUI.js';

const TRANSICION_CURVA = 'cubic-bezier(0.32, 0.72, 0, 1)';
const PUNTO_BASE = 'relative grid size-11 shrink-0 place-items-center rounded-full border-2 transition-all duration-500';

const ESTADO_PUNTO = {
  completado: 'border-marca bg-marca text-white',
  actual: 'border-marca bg-white text-marca ring-4 ring-marca-100',
  pendiente: 'border-slate-300 bg-white text-zinc-400',
};

const ESTADO_ETIQUETA = {
  completado: 'text-zinc-900',
  actual: 'text-marca',
  pendiente: 'text-zinc-500',
};

const movimientoReducido = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const obtenerCampos = (paso) =>
  [...paso.querySelectorAll('input, select, textarea')].filter(
    (campo) => !campo.disabled && campo.type !== 'hidden'
  );

const estaVacio = (campo) => (campo.type === 'file' ? campo.files.length === 0 : !campo.value.trim());

const etiquetaDe = (campo) => document.querySelector(`label[for="${campo.id}"]`)?.textContent.trim() || campo.name;

const tituloDe = (paso, indice) => paso.dataset.stepTitle ?? `Paso ${indice + 1}`;

const obtenerProblema = (campo) => {
  if (campo.required && estaVacio(campo)) return 'Este campo es obligatorio';
  if (campo.classList.contains('border-red-500')) {
    return InputUI.obtenerElementoError(campo)?.textContent || 'Valor inválido';
  }
  return null;
};

const validarPaso = (paso) => {
  const problemas = obtenerCampos(paso)
    .map((campo) => ({ campo, mensaje: obtenerProblema(campo) }))
    .filter(({ mensaje }) => mensaje);

  if (!problemas.length) return true;

  problemas.forEach(({ campo, mensaje }) => InputUI.establecerInvalido(campo, mensaje));
  const lista = problemas.map(({ campo, mensaje }) => `• ${etiquetaDe(campo)}: ${mensaje}`).join('\n');
  window.alert(`No puedes continuar, revisa los siguientes campos:\n\n${lista}`);
  problemas[0].campo.focus();
  return false;
};

const construirIndicador = (lista, pasos) => {
  const ultimo = pasos.length - 1;
  const posicionEtiqueta = (indice) =>
    indice === 0 ? 'left-0' : indice === ultimo ? 'right-0' : 'left-1/2 -translate-x-1/2';

  lista.innerHTML = pasos
    .map(
      (paso, indice) => `
        <li class="flex items-center ${indice < ultimo ? 'flex-1' : ''}">
          <span data-dot class="${PUNTO_BASE}">
            <i data-lucide="${paso.dataset.stepIcon}" class="size-5"></i>
            <span data-label class="absolute top-full mt-2 whitespace-nowrap text-xs font-medium transition-colors duration-300 ${posicionEtiqueta(indice)}"></span>
          </span>
          ${
            indice < ultimo
              ? `<span class="mx-3 h-1 flex-1 overflow-hidden rounded-full bg-slate-200">
                   <span data-bar class="block h-full origin-left scale-x-0 rounded-full bg-marca transition-transform duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]"></span>
                 </span>`
              : ''
          }
        </li>`
    )
    .join('');

  lista.querySelectorAll('[data-label]').forEach((etiqueta, indice) => {
    etiqueta.textContent = tituloDe(pasos[indice], indice);
  });
  window.lucide?.createIcons();
};

export const inicializarPasoMultiple = (selector, opciones = {}) => {
  const callbackFinalizar = opciones.alFinalizar;
  const formulario = document.querySelector(selector);
  if (!formulario) return null;

  const pasos = [...formulario.querySelectorAll('[data-step]')];
  const visor = formulario.querySelector('[data-step-viewport]') ?? pasos[0].parentElement;
  const indicador = formulario.querySelector('[data-step-indicator]');
  const botonAnterior = formulario.querySelector('[data-step-prev]');
  const botonSiguiente = formulario.querySelector('[data-step-next]');
  const etiquetaSiguiente = formulario.querySelector('[data-step-next-label]');
  const contador = formulario.querySelector('[data-step-counter]');
  const ultimo = pasos.length - 1;

  let actual = 0;
  let ocupado = false;

  if (indicador) construirIndicador(indicador, pasos);
  const puntos = [...formulario.querySelectorAll('[data-dot]')];
  const etiquetas = [...formulario.querySelectorAll('[data-label]')];
  const barras = [...formulario.querySelectorAll('[data-bar]')];

  const renderizar = () => {
    puntos.forEach((punto, indice) => {
      const estado = indice < actual ? 'completado' : indice === actual ? 'actual' : 'pendiente';
      punto.className = `${PUNTO_BASE} ${ESTADO_PUNTO[estado]}`;
      punto.closest('li').setAttribute('aria-current', indice === actual ? 'step' : 'false');
      etiquetas[indice].classList.remove(...Object.values(ESTADO_ETIQUETA));
      etiquetas[indice].classList.add(ESTADO_ETIQUETA[estado]);
    });
    barras.forEach((barra, indice) => {
      barra.classList.toggle('scale-x-100', indice < actual);
      barra.classList.toggle('scale-x-0', indice >= actual);
    });
    botonAnterior?.classList.toggle('invisible', actual === 0);
    if (etiquetaSiguiente) etiquetaSiguiente.textContent = actual === ultimo ? 'Finalizar' : 'Siguiente';
    if (contador) contador.textContent = `Paso ${actual + 1} de ${pasos.length}`;
  };

  const irA = async (destino) => {
    if (ocupado || destino === actual || destino < 0 || destino > ultimo) return;
    ocupado = true;

    const pasoOrigen = pasos[actual];
    const pasoDestino = pasos[destino];
    const direccion = Math.sign(destino - actual);
    const desplazamientoX = movimientoReducido() ? 0 : 40;
    const alturaInicial = visor.offsetHeight;

    const animacionSalida = pasoOrigen.animate(
      [
        { opacity: 1, transform: 'translateX(0)' },
        { opacity: 0, transform: `translateX(${-direccion * desplazamientoX}px)` },
      ],
      { duration: 180, easing: 'ease-in', fill: 'forwards' }
    );
    await animacionSalida.finished;
    pasoOrigen.hidden = true;
    animacionSalida.cancel();

    pasoDestino.hidden = false;
    actual = destino;
    renderizar();

    visor.style.overflow = 'hidden';
    const alturaFinal = visor.offsetHeight;
    const animaciones = [
      visor.animate([{ height: `${alturaInicial}px` }, { height: `${alturaFinal}px` }], {
        duration: 400,
        easing: TRANSICION_CURVA,
      }),
      pasoDestino.animate(
        [
          { opacity: 0, transform: `translateX(${direccion * desplazamientoX}px)` },
          { opacity: 1, transform: 'translateX(0)' },
        ],
        { duration: 400, easing: TRANSICION_CURVA }
      ),
    ];
    formulario.scrollIntoView({ behavior: movimientoReducido() ? 'auto' : 'smooth', block: 'start' });

    await Promise.all(animaciones.map((animacion) => animacion.finished));
    visor.style.overflow = '';
    obtenerCampos(pasoDestino)[0]?.focus({ preventScroll: true });
    ocupado = false;
  };

  botonSiguiente?.addEventListener('click', () => {
    if (ocupado || !validarPaso(pasos[actual])) return;
    if (actual === ultimo) return callbackFinalizar?.(formulario);
    irA(actual + 1);
  });
  botonAnterior?.addEventListener('click', () => irA(actual - 1));
  formulario.addEventListener('submit', (evento) => evento.preventDefault());

  pasos.forEach((paso, indice) => {
    paso.hidden = indice !== 0;
  });
  renderizar();

  return {
    irA,
    get actual() {
      return actual;
    },
  };
};
