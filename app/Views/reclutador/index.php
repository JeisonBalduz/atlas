<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
</head>
<body class="bg-base-200">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-base-100 shadow-md p-5 flex flex-col justify-between">
            <div>
                <h1 class="text-xl font-bold text-primary mb-6">Panel Admin</h1>
                <ul class="menu bg-base-100 rounded-box p-0 space-y-1">
                    <li><a class="active">Dashboard General</a></li>
                   <li><a href="/pruebanueva2/verUsuarios">Gestion de Usuarios</a></li>
                    <li><a href="#">Configuraciones</a></li>
                    <li><a href="/pruebanueva2/vercomidas">vercomidas</a></li>
                </ul>
            </div>
            <div>
                <a href="/pruebanueva2/logout" class="btn btn-error btn-sm w-full">Cerrar Sesión</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto">

            <div class="navbar bg-base-100 shadow-sm rounded-box mb-6 px-4">
                <div class="flex-1">
                    <span class="text-lg font-semibold"> <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></span>
                </div>
                <div class="flex-none">
                    <span class="badge badge-primary badge-lg uppercase">Rol: <?= htmlspecialchars($_SESSION['usuario']['rol']) ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stats shadow bg-base-100">
                    <div class="stat">
                        <div class="stat-title">Estado del Sistema</div>
                        <div class="stat-value text-success text-2xl">Activo</div>
                        <div class="stat-desc">MVC Namespace Atlas\Jei</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>