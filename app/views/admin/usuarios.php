<?php
$rutaJson = __DIR__ . '/../../../storage/empleados.json';

$empleados = [];
if (file_exists($rutaJson)) {
    $empleados = json_decode(file_get_contents($rutaJson), true);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Institucional - INCES</title>
    <!-- Tailwind CSS CDN para un diseño moderno y responsivo -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- ================= PANEL LATERAL / SIDEBAR ================= -->
        <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shadow-xl">
            <!-- Logo o Título de la Institución -->
            <div class="p-6 text-xl font-extrabold text-white border-b border-slate-800 flex items-center space-x-3">
                <span class="bg-blue-600 text-white p-2 rounded-lg text-sm">INCES</span>
                <span>Sistema Aragua</span>
            </div>

            <!-- Menú de Navegación -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="<?= $_SESSION['usuario']['rol']; ?>" class="flex items-center py-2.5 px-4 rounded-lg bg-blue-600 text-white font-medium transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Panel Principal
                </a>
                <a href="/pruebanueva2/public/analista" class="flex items-center py-2.5 px-4 rounded-lg hover:bg-slate-800 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Módulo Personal
                </a>
                <a href="/pruebanueva2/public/comedor" class="flex items-center py-2.5 px-4 rounded-lg hover:bg-slate-800 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Control Comedor
                </a>
            </nav>

            <!-- Botón de Cerrar Sesión Abajo -->
            <div class="p-4 border-t border-slate-800">
                <a href="/pruebanueva2/public/logout" class="flex items-center py-2 px-4 rounded-lg text-red-400 hover:bg-red-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Cerrar Sesión
                </a>
            </div>
        </aside>

        <!-- ================= CONTENIDO PRINCIPAL ================= -->
        <main class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Barra Superior -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-8 z-10">
                <h2 class="text-xl font-semibold text-slate-800">Listado General de Registros</h2>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-slate-600">Bienvenido, Usuario institucional</span>
                    <div class="h-9 w-9 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">I</div>
                </div>
            </header>

            <!-- Área de la Tabla de Empleados -->
            <div class="flex-1 overflow-y-auto p-8">
                <div class="bg-white shadow-md rounded-xl overflow-hidden border border-slate-200">
                    
                    <!-- Encabezado de la Tarjeta de la Tabla -->
                    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                        <h3 class="text-lg font-medium text-slate-700">Personal Registrado (<?= count($empleados ?? []) ?>)</h3>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Base de Datos Activa</span>
                    </div>

                    <!-- Tabla HTML -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Nombre y Apellido</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Cargo</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Departamento</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $emp): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-700">#<?= htmlspecialchars($emp['id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?= htmlspecialchars($emp['nombre']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium"><?= htmlspecialchars($emp['cargo']) ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= htmlspecialchars($emp['departamento']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-6 text-center text-slate-500">No hay registros disponibles en este archivo JSON.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </main>
    </div>

</body>
</html>