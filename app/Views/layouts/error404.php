<?php
/**
 * @var string $titulo
 * @var string $mensaje
 * @var string $codigo
 */
?>

<div class="bg-slate-950 flex justify-center items-center min-h-screen p-6 antialiased">
    <div class="max-w-4xl w-full flex flex-col items-center text-center space-y-12">
        <!-- Palabra de error -->
        <div class="relative">
            <h1 class="text-[12rem] md:text-[20rem] font-black text-white leading-none tracking-tighter opacity-5 select-none">
                <?php echo $codigo; ?>
            </h1>
            <div class="absolute inset-0 flex flex-col items-center justify-center space-y-4">
                <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.6em] animate-pulse">
                    Problema del servidor
                </span>
                <h2 class="text-4xl md:text-6xl font-black text-white tracking-tight"><?php echo $titulo; ?></h2>
            </div>
        </div>

        <div class="space-y-6 max-w-xl">
            <p class="text-slate-400 text-xl md:text-2xl leading-relaxed font-light italic">
                <?php echo $mensaje; ?>
            </p>
            <div class="h-1 w-24 bg-indigo-600 mx-auto rounded-full"></div>
        </div>

        <div class="flex flex-col sm:flex-row gap-6">
            <button
                class="px-12 py-5 bg-white text-slate-950 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-400 transition-all cursor-pointer">
                Regresar al inicio
            </button>
            <button
                class="px-12 py-5 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] border border-slate-800 hover:bg-slate-800 transition-all cursor-pointer">
                Comunicarse con soporte
            </button>
        </div>

        <!-- Línea de decoración -->
        <div class="pt-24 grid grid-cols-4 gap-12 border-t border-slate-900 w-full opacity-20"></div>
    </div>
</div>