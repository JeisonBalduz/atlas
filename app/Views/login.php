<?php
use App\Atlas\Config\App;
?>

<section id="vista-login" class="flex min-h-screen w-full flex-col md:flex-row">
  <!-- Columna visual institucional -->
  <div class="relative flex min-h-[220px] flex-col justify-end overflow-hidden p-8 md:w-1/2 md:min-h-screen md:p-14">
    <img src="./assets/img/fondo.png" alt="Taller de formación técnica"
      class="absolute inset-0 h-full w-full object-cover" />

    <div class="absolute inset-0 login-overlay"></div>
  </div>

  <!-- Formulario -->
  <div class="flex w-full flex-1 items-center justify-center bg-white px-6 py-12 md:w-1/2">
    <form id="form-login" class="w-full max-w-sm" novalidate>
      <div class="mb-2 flex flex-col items-center justify-center">
        <img src="./assets/icons/logo_atlas.png" alt="" class="mb-3 w-16 h-16" alt="Foto">
        <h2 class="font-medium text-zinc-800 text-2xl">
          Inicia sesión en tu cuenta
        </h2>
        <p class="mt-3 text-center text-sm text-gray-600">
          Introduce tu correo electrónico y contraseña a continuación para iniciar sesión
        </p>
      </div>

      <label for="user" class="mb-1.5 block text-sm font-medium text-slate-900">Usuario</label>

      <div class="relative">
        <i data-lucide="user"
          class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <input id="user" name="usuario" type="text" autocomplete="username" maxlength="40" placeholder="norma.ingreso"
          class="w-full rounded-lg border border-slate-300 
          bg-slate-50 py-2.5 pl-10 text-sm text-black outline-none transition 
          focus:bg-white focus:border-black focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>

      <div class="flex items-center justify-between mb-1.5 mt-5 text-sm font-medium">
        <!-- Lado Izquierdo: Etiqueta del campo -->
        <label for="password" class="text-zinc-900 cursor-pointer">
          Contraseña
        </label>

        <!-- Lado Derecho: Enlaces y pregunta -->
        <div class="flex items-center space-x-1 text-sm text-black font-normal">
          <a href="#" class=" underline underline-offset-6">
            <span>¿Olvidaste tu contraseña?</span>
          </a>
        </div>
      </div>

      <div class="relative ">
        <i data-lucide="lock"
          class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <input id="password" name="clave" type="password" autocomplete="current-password" maxlength="64"
          placeholder="••••"
          class="w-full rounded-lg border border-slate-300 
          bg-slate-50 py-2.5 pl-10 text-sm text-black outline-none transition 
          focus:bg-white focus:border-black focus:ring-2 focus:ring-black focus:ring-offset-2" />
        <button id="btn-ver-clave" type="button" aria-label="Mostrar contraseña"
          class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
          <i data-lucide="eye" class="h-4 w-4"></i>
        </button>
      </div>

      <p id="login-error" role="alert"
        class="oculto mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600">
      </p>

      <button type="submit" id="btn-entrar" class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-black px-4 py-2.5 text-sm 
          font-semibold text-white shadow-sm transition-all duration-200 hover:bg-bg-zinc-900  hover:shadow-md 
          focus:outline-none focus:ring-4 focus:ring-zinc-200 active:scale-[0.99]">
        Iniciar Sesión
        <i data-lucide="arrow-right" class="h-4 w-4"></i>
      </button>

      <div class="flex items-center justify-center space-x-1 text-sm mt-4">
        <span class="text-zinc-600">¿No tienes cuenta?</span>
        <a href="#" class="font-medium underline text-black underline-offset-4">
          Solicitar
        </a>
      </div>
    </form>
  </div>
</section>

<script src="<?= App::URL_JS_MODULES ?>/login.js"></script>