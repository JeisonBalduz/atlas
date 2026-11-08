<?php

declare(strict_types=1);

namespace App\Http;

class CleanInputs
{
    private array $data = [];

    public function __construct()
    {
        // Capturamos tanto JSON (para fetch) como datos POST tradicionales
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (\str_contains($contentType, 'application/json')) {
            $input = \json_decode(\file_get_contents('php://input'), true);
            $this->data = \is_array($input) ? $input : [];
        } else {
            $this->data = $_POST;
        }
    }

    /**
     * Obtiene un valor específico del request limpiando espacios y barras invertidas básicas
     */
    public function get(string $key, $default = null)
    {
        if (!isset($this->data[$key])) {
            return $default;
        }

        $value = $this->data[$key];

        // Si es un string, aplicamos trim() para eliminar espacios a ambos lados y stripslashes por seguridad básica
        if (\is_string($value)) {
            return \trim(\stripslashes($value));
        }

        return $value;
    }

    /**
     * Retorna todos los datos limpios
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Valida que los campos requeridos tengan contenido
     */
    public function validate(array $fields): ?string
    {
        foreach ($fields as $field) {
            $value = $this->get($field);
            if (empty($value)) {
                return "El campo '{$field}' es obligatorio.";
            }
        }
        return null;
    }

    /**
     * Valida que un campo contenga estrictamente solo números (ej. Cédula, Teléfono)
     */
    public function validateNumeric(string $field): bool
    {
        $value = $this->get($field);
        return ctype_digit((string) $value);
    }

    /**
     * Valida que un campo contenga estrictamente solo letras
     */
    public function validateAlpha(string $field): bool
    {
        $value = $this->get($field);
        return ctype_alpha((string) $value);
    }
}
