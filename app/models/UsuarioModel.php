<?php
namespace App\Atlas\models;

class UsuarioModel {
    public static function encontrarPorEmail($email) {
        $rutaJson = __DIR__ . '/../../data/usuarios.json';
        if (!file_exists($rutaJson)) return null;

        $usuarios = json_decode(file_get_contents($rutaJson), true);
        foreach ($usuarios as $usuario) {
            if ($usuario['email'] === $email) {
                return $usuario;
            }
        }
        return null;
    }
}