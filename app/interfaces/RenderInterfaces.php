<?php
namespace App\Interfaces;

// Declarar que el objeto debe ser capaz de renderizar en tipo string
interface RenderInterfaces {
    
   public function render(): string;
}