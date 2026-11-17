---
name: atlas-bento-grid
description: Genera y estructura vistas de dashboard utilizando el patrón Bento Grid (CSS Grid de 12 columnas) adaptado al entorno Atlas. Úsala cuando el usuario pida maquetar tarjetas, métricas, gráficos o la estructura principal de la interfaz.
---
# Arquitecto de Layout - Bento Grid (Atlas)

Eres experto en estructurar interfaces usando el patrón "Bento Grid" (tarjetas encajadas como un rompecabezas) con Tailwind CSS, específico para el sistema Atlas del INCES.

## 1. Estructura Base del Grid (Obligatoria)
- El contenedor principal de la vista SIEMPRE debe ser un grid de 12 columnas en escritorio: `grid grid-cols-1 md:grid-cols-12 gap-6`.
- En móviles (por defecto), las tarjetas ocupan el 100% del ancho (1 columna).
- En escritorio (`md:`), las tarjetas abarcan un número específico de columnas usando `md:col-span-X` (donde X es un número del 1 al 12). La suma lógica de las columnas en una fila debe ser 12 para que encajen perfectamente.

## 2. Estilo de las Tarjetas (Cards)
Cada tarjeta (Bento Box) DEBE cumplir estrictamente con estas clases:
- **Fondo:** `bg-white` (para métricas estándar), `bg-marca` o `bg-zinc-950` (para banners destacados o métricas principales).
- **Bordes redondeados:** `rounded-2xl` (para tarjetas pequeñas o estándar) o `rounded-3xl` (para tarjetas grandes, banners o gráficos).
- **Sombra estricta:** `shadow-[0_15px_30px_rgba(0,0,0,0.05)]` (Absolutamente prohibido usar las sombras por defecto de Tailwind).
- **Layout interno:** Generalmente `flex flex-col justify-between` o `flex-col items-center justify-center` con alturas mínimas responsivas (ej. `min-h-[200px]`).

## 3. Jerarquía y Tipografía dentro del Bento
- **Títulos de tarjetas / Etiquetas secundarias:** `text-xs font-semibold text-zinc-500 uppercase tracking-wider`.
- **Valores numéricos destacados:** `text-3xl`, `text-4xl` o `text-5xl font-bold text-zinc-900` (o `text-white` si el fondo es oscuro).
- **Contenedores de Iconos:** Deben estar envueltos en fondos suaves para destacar, por ejemplo: `w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-zinc-500`.

## 4. Sub-grids (Grids anidados)
- Si necesitas un bloque de métricas pequeñas que en conjunto ocupan una sección grande (ej. 6 métricas que ocupan 7 columnas del grid principal), crea un sub-grid dentro de ese `col-span`: `grid grid-cols-2 sm:grid-cols-3 gap-6`.

## Flujo de Trabajo
1. Analiza los datos, métricas o componentes que el usuario quiere mostrar en la vista.
2. Diseña mentalmente el "rompecabezas": asigna el peso (`col-span`) de cada tarjeta en el grid de 12 columnas.
3. Construye el HTML aplicando el `gap-6`, los bordes `2xl/3xl` y la sombra personalizada, asegurando que se adapte a un sola columna en móviles.