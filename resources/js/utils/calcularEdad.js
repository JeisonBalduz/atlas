/**
 * Calcula la edad en años cumplidos a partir de año, mes y día.
 * @param {number} ano
 * @param {number} mes
 * @param {number} dia
 * @returns {string}
 */
export const calcularEdad = (ano, mes, dia) => {
  if (!ano || !mes || !dia) return '';
  const hoy = new Date();
  let edad = hoy.getFullYear() - ano;
  const mesActual = hoy.getMonth() + 1;
  const diaActual = hoy.getDate();

  if (mesActual < mes || (mesActual === mes && diaActual < dia)) {
    edad -= 1;
  }

  return edad >= 0 && edad <= 110 ? String(edad) : '';
};
