<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Bienvenido a FlightPHP con BladeOne' }}</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #0b0f19;
            color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #111827;
            border: 1px solid #1f2937;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 500px;
        }
        h1 { color: #38bdf8; margin-bottom: 0.5rem; }
        p { color: #9ca3af; line-height: 1.5; }
        .badge {
            display: inline-block;
            background: #0369a1;
            color: #e0f2fe;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>¡Hola, {{ $name ?? 'Desarrollador' }}!</h1>
        <p>Tu esqueleto de FlightPHP ha sido migrado exitosamente para usar el motor de plantillas <strong>BladeOne</strong>.</p>
        <div class="badge">Entorno: {{ $app_env }}</div>
    </div>
</body>
</html>