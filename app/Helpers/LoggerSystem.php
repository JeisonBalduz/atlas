<?php
namespace App\Atlas\Helpers;

class LoggerSystem {
    // 1. Declaramos la propiedad limpia sin asignarle valor aquí arriba
    private static string $logFile;

    public static function init() {
        // 2. Asignamos el valor aquí adentro, donde PHP sí permite usar __DIR__
        self::$logFile = __DIR__ . '/../../storage/logs/error_server.log';

        // Asegurar que la carpeta de logs exista
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Configuración básica de PHP
        ini_set('log_errors', 1);
        ini_set('error_log', self::$logFile);
        ini_set('display_errors', 0); // Oculta errores visuales feos en producción
        error_reporting(E_ALL);        // Reportar absolutamente todo

        // Registrar los capturadores del sistema
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    // El parámetro $exception necesita tipado \Throwable para que no dé advertencias en PHP 8
    public static function handleException(\Throwable $exception) {
        self::writeLog(
            'EXCEPTION_FATAL', 
            $exception->getMessage(), 
            $exception->getFile(), 
            $exception->getLine(),
            $exception->getTraceAsString()
        );
    }

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        $type = self::getErrorType($errno);
        self::writeLog($type, $errstr, $errfile, $errline);
        return true; 
    }

    public static function handleFatalError() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::writeLog('FATAL_ERROR', $error['message'], $error['file'], $error['line']);
        }
    }

    private static function writeLog(string $type, string $message, string $file, int $line, ?string $trace = null) {
        $fecha = date('Y-m-d H:i:s');
        
        $logEntry = "=======================================================================\n";
        $logEntry .= "⏰ FECHA/HORA: [$fecha]\n";
        $logEntry .= "🚨 TIPO ERROR: $type\n";
        $logEntry .= "💬 MENSAJE:   $message\n";
        $logEntry .= "📂 ARCHIVO:   $file\n";
        $logEntry .= "📍 LÍNEA:     $line\n";
        
        if ($trace) {
            $logEntry .= "🔍 TRAZA (STACK TRACE):\n$trace\n";
        }
        
        $logEntry .= "=======================================================================\n\n";

        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }

    private static function getErrorType(int $errno): string {
        return match ($errno) {
            E_USER_ERROR => 'USER_ERROR',
            E_WARNING, E_USER_WARNING => 'WARNING',
            E_NOTICE, E_USER_NOTICE => 'NOTICE',
            E_DEPRECATED, E_USER_DEPRECATED => 'DEPRECATED',
            default => 'UNKNOWN_ERROR_TYPE',
        };
    }
}
