/**
 * TablaInteractiva.js
 * Controlador reutilizable para tablas de datos en memoria:
 * búsqueda en tiempo real, filtros personalizados, ordenamiento, paginación,
 * badges semánticos (regla 50/200/800), delegación de acciones y estado vacío.
 */

export class TablaInteractiva {
  /**
   * @param {Object} configuracion
   * @param {string|HTMLElement} configuracion.contenedor - Selector o nodo del componente tabla
   * @param {Array<Object>} [configuracion.datos=[]] - Arreglo de registros en memoria
   * @param {Array<Object>} [configuracion.columnas=[]] - Definición de columnas [{ clave, etiqueta, ordenable, alineacion, renderizar }]
   * @param {string} [configuracion.claveId='id'] - Propiedad identificadora de cada fila
   * @param {number} [configuracion.porPagina=10] - Registros visibles por página
   * @param {Function} [configuracion.alAccionar] - Callback (accion, id, registro)
   * @param {string} [configuracion.mensajeVacio] - Mensaje cuando no hay coincidencias
   */
  constructor(configuracion) {
    this.contenedor =
      typeof configuracion.contenedor === "string"
        ? document.querySelector(configuracion.contenedor)
        : configuracion.contenedor;

    if (!this.contenedor) {
      throw new Error("TablaInteractiva: no se encontró el contenedor indicado.");
    }

    this.datosOriginales = Array.isArray(configuracion.datos) ? [...configuracion.datos] : [];
    this.columnas = Array.isArray(configuracion.columnas) ? [...configuracion.columnas] : [];
    this.claveId = configuracion.claveId || "id";
    this.porPagina = Number(configuracion.porPagina) || 10;
    this.alAccionar = configuracion.alAccionar || null;
    this.mensajeVacio = configuracion.mensajeVacio || null;

    this.paginaActual = 1;
    this.terminoBusqueda = "";
    this.columnaOrden = null;
    this.direccionOrden = "asc";
    this.filtrosActivos = {};
    this.filtroPersonalizado = null;

    // Referencias internas del componente BladeOne
    this.inputBuscador = this.contenedor.querySelector("[data-tabla-buscador]");
    this.selectPorPagina = this.contenedor.querySelector("[data-tabla-por-pagina]");
    this.encabezado = this.contenedor.querySelector("[data-tabla-encabezado]");
    this.cuerpo = this.contenedor.querySelector("[data-tabla-cuerpo]");
    this.estadoVacio = this.contenedor.querySelector("[data-tabla-vacio]");
    this.textoVacio = this.contenedor.querySelector("[data-tabla-vacio-texto]");
    this.resumen = this.contenedor.querySelector("[data-tabla-resumen]");
    this.paginacion = this.contenedor.querySelector("[data-tabla-paginacion]");

    if (this.selectPorPagina) {
      this.selectPorPagina.value = String(this.porPagina);
    }

    if (this.mensajeVacio && this.textoVacio) {
      this.textoVacio.textContent = this.mensajeVacio;
    }

    this.#vincularEventos();
    this.renderizar();
  }

  /**
   * Escapa caracteres HTML para evitar inyección al renderizar celdas de texto.
   * @param {unknown} valor
   * @returns {string}
   */
  static escaparHtml(valor) {
    return String(valor ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#39;");
  }

  /**
   * Genera un badge semántico siguiendo la regla 50 / 200 / 800-900 de Atlas.
   * @param {string} texto
   * @param {'green'|'red'|'yellow'|'sky'|'marca'|'neutro'} [variante='neutro']
   * @returns {string}
   */
  static crearBadgeEstado(texto, variante = "neutro") {
    const estilos = {
      green: "bg-green-50 border-green-200 text-green-800",
      red: "bg-red-50 border-red-200 text-red-800",
      yellow: "bg-yellow-50 border-yellow-200 text-yellow-900",
      sky: "bg-sky-50 border-sky-200 text-sky-800",
      marca: "bg-marca-50 border-marca-200 text-marca-800",
      neutro: "bg-slate-50 border-slate-200 text-zinc-900",
    };

    const clase = estilos[variante] || estilos.neutro;
    return `<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${clase}">${TablaInteractiva.escaparHtml(texto)}</span>`;
  }

  /**
   * Genera el grupo estándar de botones de acción para cada fila (Mostrar, Editar, Eliminar).
   * @param {string|number} id
   * @param {Object} [opciones]
   * @param {boolean} [opciones.mostrar=true]
   * @param {boolean} [opciones.editar=true]
   * @param {boolean} [opciones.eliminar=true]
   * @param {string|null} [opciones.hrefEditar=null]
   * @param {string} [opciones.etiquetaEliminar='Eliminar']
   * @returns {string}
   */
  static crearBotonesAccion(id, opciones = {}) {
    const {
      mostrar = true,
      editar = true,
      eliminar = true,
      hrefEditar = null,
      etiquetaEliminar = "Eliminar",
    } = opciones;

    const idSeguro = TablaInteractiva.escaparHtml(id);
    const botones = [];

    if (mostrar) {
      botones.push(`
        <button type="button" data-accion="mostrar" data-id="${idSeguro}" title="Mostrar detalle" aria-label="Mostrar detalle"
          class="inline-flex items-center justify-center rounded-lg border border-sky-200 bg-sky-50 p-2 text-sky-800 transition-all hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 cursor-pointer">
          <i data-lucide="eye" class="h-4 w-4" aria-hidden="true"></i>
        </button>
      `);
    }

    if (editar) {
      if (hrefEditar) {
        botones.push(`
          <a href="${TablaInteractiva.escaparHtml(hrefEditar)}" data-accion="editar" data-id="${idSeguro}" title="Editar registro" aria-label="Editar registro"
            class="inline-flex items-center justify-center rounded-lg border border-yellow-200 bg-yellow-50 p-2 text-yellow-900 transition-all hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 cursor-pointer">
            <i data-lucide="pencil" class="h-4 w-4" aria-hidden="true"></i>
          </a>
        `);
      } else {
        botones.push(`
          <button type="button" data-accion="editar" data-id="${idSeguro}" title="Editar registro" aria-label="Editar registro"
            class="inline-flex items-center justify-center rounded-lg border border-yellow-200 bg-yellow-50 p-2 text-yellow-900 transition-all hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 cursor-pointer">
            <i data-lucide="pencil" class="h-4 w-4" aria-hidden="true"></i>
          </button>
        `);
      }
    }

    if (eliminar) {
      botones.push(`
        <button type="button" data-accion="eliminar" data-id="${idSeguro}" title="${TablaInteractiva.escaparHtml(etiquetaEliminar)}" aria-label="${TablaInteractiva.escaparHtml(etiquetaEliminar)}"
          class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 p-2 text-red-800 transition-all hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 cursor-pointer">
          <i data-lucide="trash-2" class="h-4 w-4" aria-hidden="true"></i>
        </button>
      `);
    }

    return `<div class="inline-flex items-center justify-end gap-1.5">${botones.join("")}</div>`;
  }

  /**
   * Reemplaza el arreglo de datos y vuelve a renderizar la tabla.
   * @param {Array<Object>} nuevosDatos
   */
  actualizarDatos(nuevosDatos) {
    this.datosOriginales = Array.isArray(nuevosDatos) ? [...nuevosDatos] : [];
    this.paginaActual = 1;
    this.renderizar();
  }

  /**
   * Reemplaza dinámicamente las columnas (útil para vistas con pestañas como Catálogo).
   * @param {Array<Object>} nuevasColumnas
   * @param {string} [nuevaClaveId]
   */
  establecerColumnas(nuevasColumnas, nuevaClaveId = null) {
    this.columnas = Array.isArray(nuevasColumnas) ? [...nuevasColumnas] : [];
    if (nuevaClaveId) {
      this.claveId = nuevaClaveId;
    }
    this.columnaOrden = null;
    this.direccionOrden = "asc";
    this.#renderizarEncabezado();
    this.renderizar();
  }

  /**
   * Aplica o limpia un filtro por igualdad exacta sobre una propiedad.
   * @param {string} clave
   * @param {string} valor
   */
  establecerFiltro(clave, valor) {
    if (valor === "" || valor === null || valor === undefined || valor === "todos") {
      delete this.filtrosActivos[clave];
    } else {
      this.filtrosActivos[clave] = String(valor);
    }
    this.paginaActual = 1;
    this.renderizar();
  }

  /**
   * Registra una función predicado personalizada para filtros complejos.
   * @param {Function|null} funcionFiltro
   */
  establecerFiltroPersonalizado(funcionFiltro) {
    this.filtroPersonalizado = funcionFiltro;
    this.paginaActual = 1;
    this.renderizar();
  }

  /**
   * Devuelve una copia de los datos originales en memoria.
   * @returns {Array<Object>}
   */
  obtenerDatos() {
    return [...this.datosOriginales];
  }

  #vincularEventos() {
    if (this.inputBuscador) {
      this.inputBuscador.addEventListener("input", (evento) => {
        this.terminoBusqueda = evento.target.value.trim().toLowerCase();
        this.paginaActual = 1;
        this.renderizar();
      });
    }

    if (this.selectPorPagina) {
      this.selectPorPagina.addEventListener("change", (evento) => {
        this.porPagina = Number(evento.target.value) || 10;
        this.paginaActual = 1;
        this.renderizar();
      });
    }

    if (this.encabezado) {
      this.encabezado.addEventListener("click", (evento) => {
        const botonOrden = evento.target.closest("[data-ordenar-columna]");
        if (!botonOrden) return;

        const clave = botonOrden.dataset.ordenarColumna;
        if (this.columnaOrden === clave) {
          this.direccionOrden = this.direccionOrden === "asc" ? "desc" : "asc";
        } else {
          this.columnaOrden = clave;
          this.direccionOrden = "asc";
        }
        this.renderizar();
      });
    }

    if (this.cuerpo) {
      this.cuerpo.addEventListener("click", (evento) => {
        const disparador = evento.target.closest("[data-accion]");
        if (!disparador || !this.alAccionar) return;

        // Si es un enlace con href real distinto de '#', dejamos navegar si el callback no lo intercepta
        const accion = disparador.dataset.accion;
        const id = disparador.dataset.id;
        const registro = this.datosOriginales.find(
          (item) => String(item[this.claveId]) === String(id)
        );

        this.alAccionar(accion, id, registro || null, evento);
      });
    }

    if (this.paginacion) {
      this.paginacion.addEventListener("click", (evento) => {
        const boton = evento.target.closest("[data-pagina]");
        if (!boton || boton.disabled) return;

        const destino = Number(boton.dataset.pagina);
        if (!Number.isNaN(destino) && destino >= 1) {
          this.paginaActual = destino;
          this.renderizar();
        }
      });
    }
  }

  #renderizarEncabezado() {
    if (!this.encabezado) return;

    const celdas = this.columnas
      .map((columna) => {
        const alineacion =
          columna.alineacion === "right"
            ? "text-right"
            : columna.alineacion === "center"
              ? "text-center"
              : "text-left";

        if (columna.ordenable && columna.clave) {
          return `
            <th scope="col" class="whitespace-nowrap px-4 py-3.5 text-xs font-semibold text-zinc-500 ${alineacion}">
              <button type="button" data-ordenar-columna="${TablaInteractiva.escaparHtml(columna.clave)}"
                class="inline-flex items-center gap-1.5 rounded font-semibold text-zinc-500 transition-all hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                <span>${TablaInteractiva.escaparHtml(columna.etiqueta)}</span>
                <i data-lucide="arrow-up-down" class="h-3.5 w-3.5" aria-hidden="true"></i>
              </button>
            </th>
          `;
        }

        return `
          <th scope="col" class="whitespace-nowrap px-4 py-3.5 text-xs font-semibold text-zinc-500 ${alineacion}">
            <span>${TablaInteractiva.escaparHtml(columna.etiqueta)}</span>
          </th>
        `;
      })
      .join("");

    this.encabezado.innerHTML = `<tr>${celdas}</tr>`;
  }

  #obtenerDatosFiltradosYOrdenados() {
    let resultado = [...this.datosOriginales];

    // 1. Filtros exactos por clave
    const entradasFiltros = Object.entries(this.filtrosActivos);
    if (entradasFiltros.length > 0) {
      resultado = resultado.filter((registro) =>
        entradasFiltros.every(
          ([clave, valor]) =>
            String(registro[clave] ?? "").toLowerCase() === valor.toLowerCase()
        )
      );
    }

    // 2. Filtro personalizado
    if (typeof this.filtroPersonalizado === "function") {
      resultado = resultado.filter((registro) => this.filtroPersonalizado(registro));
    }

    // 3. Buscador global
    if (this.terminoBusqueda) {
      resultado = resultado.filter((registro) =>
        Object.values(registro).some((valor) =>
          String(valor ?? "").toLowerCase().includes(this.terminoBusqueda)
        )
      );
    }

    // 4. Ordenamiento
    if (this.columnaOrden) {
      const clave = this.columnaOrden;
      const factor = this.direccionOrden === "asc" ? 1 : -1;
      resultado.sort((a, b) => {
        const valorA = a[clave] ?? "";
        const valorB = b[clave] ?? "";
        if (typeof valorA === "number" && typeof valorB === "number") {
          return (valorA - valorB) * factor;
        }
        return String(valorA).localeCompare(String(valorB), "es", { numeric: true }) * factor;
      });
    }

    return resultado;
  }

  renderizar() {
    if (!this.cuerpo) return;

    const filtrados = this.#obtenerDatosFiltradosYOrdenados();
    const totalRegistros = filtrados.length;
    const totalPaginas = Math.max(1, Math.ceil(totalRegistros / this.porPagina));

    if (this.paginaActual > totalPaginas) {
      this.paginaActual = totalPaginas;
    }

    const inicio = (this.paginaActual - 1) * this.porPagina;
    const paginaDatos = filtrados.slice(inicio, inicio + this.porPagina);

    if (paginaDatos.length === 0) {
      this.cuerpo.innerHTML = "";
      this.estadoVacio?.classList.remove("hidden");
      this.estadoVacio?.classList.add("flex");
    } else {
      this.estadoVacio?.classList.add("hidden");
      this.estadoVacio?.classList.remove("flex");

      this.cuerpo.innerHTML = paginaDatos
        .map((registro) => {
          const celdas = this.columnas
            .map((columna) => {
              const alineacion =
                columna.alineacion === "right"
                  ? "text-right"
                  : columna.alineacion === "center"
                    ? "text-center"
                    : "text-left";

              const contenido =
                typeof columna.renderizar === "function"
                  ? columna.renderizar(registro)
                  : TablaInteractiva.escaparHtml(registro[columna.clave] ?? "—");

              return `<td class="whitespace-nowrap px-4 py-3.5 text-sm text-zinc-900 ${alineacion}">${contenido}</td>`;
            })
            .join("");

          return `<tr class="transition-colors hover:bg-slate-50/80">${celdas}</tr>`;
        })
        .join("");
    }

    // Actualizar resumen
    if (this.resumen) {
      const mostrados = paginaDatos.length;
      const desde = totalRegistros === 0 ? 0 : inicio + 1;
      const hasta = inicio + mostrados;
      this.resumen.textContent =
        totalRegistros === 0
          ? "Mostrando 0 de 0 registros"
          : `Mostrando ${desde}–${hasta} de ${totalRegistros} registros`;
    }

    this.#renderizarPaginacion(totalPaginas);

    if (typeof window.lucide !== "undefined") {
      window.lucide.createIcons();
    }
  }

  #renderizarPaginacion(totalPaginas) {
    if (!this.paginacion) return;

    const crearBoton = (etiqueta, pagina, activo = false, deshabilitado = false) => {
      const clasesEstado = activo
        ? "border-marca bg-marca text-white"
        : "border-slate-200 bg-white text-zinc-900 hover:bg-slate-100";
      const clasesDeshabilitado = deshabilitado ? "opacity-40 pointer-events-none" : "cursor-pointer";

      return `
        <button type="button" data-pagina="${pagina}" ${deshabilitado ? "disabled" : ""}
          class="inline-flex min-w-9 items-center justify-center rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-all focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 ${clasesEstado} ${clasesDeshabilitado}">
          ${etiqueta}
        </button>
      `;
    };

    const botones = [];
    botones.push(crearBoton("Anterior", this.paginaActual - 1, false, this.paginaActual <= 1));

    for (let pagina = 1; pagina <= totalPaginas; pagina++) {
      botones.push(crearBoton(String(pagina), pagina, pagina === this.paginaActual, false));
    }

    botones.push(
      crearBoton("Siguiente", this.paginaActual + 1, false, this.paginaActual >= totalPaginas)
    );

    this.paginacion.innerHTML = botones.join("");
  }
}
