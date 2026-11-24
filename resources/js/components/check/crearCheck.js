/**
 * Construye un check personalizado con la estética institucional de Atlas.
 * @param {Object} opciones
 * @param {string} [opciones.id='']
 * @param {string} opciones.nombre
 * @param {string} [opciones.valor='1']
 * @param {string} opciones.etiqueta
 * @param {string} [opciones.descripcion='']
 * @param {boolean} [opciones.marcado=false]
 * @param {string} [opciones.claseInput='']
 * @param {string} [opciones.atributos='']
 * @returns {string}
 */
export const crearCheck = ({
  id = '',
  nombre,
  valor = '1',
  etiqueta,
  descripcion = '',
  marcado = false,
  claseInput = '',
  atributos = '',
}) => {
  const attrId = id ? `id="${id}"` : '';
  const subtexto = descripcion ? `<span class="block text-xs text-zinc-500">${descripcion}</span>` : '';

  return `
    <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition-colors hover:bg-slate-100/80 cursor-pointer select-none">
      <span class="min-w-0">
        <span class="block text-sm font-medium text-zinc-900">${etiqueta}</span>
        ${subtexto}
      </span>
      <input
        type="checkbox"
        ${attrId}
        name="${nombre}"
        value="${valor}"
        ${marcado ? 'checked' : ''}
        ${atributos}
        class="h-4 w-4 shrink-0 rounded border-slate-300 text-marca accent-marca focus:ring-2 focus:ring-marca-500 cursor-pointer ${claseInput}"
      />
    </label>
  `;
};
