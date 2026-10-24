<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventario:probar-lector', function () {
    $this->info("==========================================================");
    $this->info("   TEST DE LECTOR DE CÓDIGO DE BARRAS (3nStar / USB HID)");
    $this->info("==========================================================");
    $this->line("Apunta tu lector 3nStar a cualquier código de barras y presiona el gatillo.");
    $this->line("Presiona Ctrl+C en cualquier momento para salir.\n");

    // Listar algunos códigos de prueba disponibles
    $disponibles = \App\Models\Producto::where('estado', 'disponible')->take(5)->get(['numero_serie', 'nombre']);
    if ($disponibles->isNotEmpty()) {
        $this->line("Códigos de ejemplo actualmente en tu base de datos:");
        foreach ($disponibles as $d) {
            $this->line("  • <fg=cyan>{$d->numero_serie}</> -> {$d->nombre}");
        }
        $this->newLine();
    }

    while (true) {
        $input = $this->ask('👉 DISPARA EL LECTOR AHORA (o escribe y presiona Enter)');

        if ($input === null || trim($input) === '') {
            $this->warn('No se recibió ningún dato. Intenta disparar de nuevo.');
            continue;
        }

        $serie = trim($input);
        $this->newLine();
        $this->info("----------------------------------------------------------");
        $this->info(" [DATOS CAPTURADOS DEL LECTOR]");
        $this->line(" Texto recibido : <fg=yellow;options=bold>{$serie}</>");
        $this->line(" Longitud       : " . strlen($serie) . " caracteres");
        $this->line(" Hexadecimal    : " . bin2hex($serie));
        $this->info("----------------------------------------------------------");

        // Buscar en la base de datos
        $producto = \App\Models\Producto::with(['categoria', 'almacen.direccion.ciudad', 'proveedor'])
            ->where('numero_serie', $serie)
            ->first();

        if ($producto) {
            $this->info("✓ ¡EQUIPO ENCONTRADO EN EL INVENTARIO!");
            $this->table(
                ['Propiedad', 'Detalle'],
                [
                    ['Número de Serie', $producto->numero_serie],
                    ['Nombre / Modelo', $producto->nombre],
                    ['Estado Actual', strtoupper($producto->estado)],
                    ['Categoría', $producto->categoria?->nombre ?? 'N/A'],
                    ['Almacén / Bodega', $producto->almacen?->nombre ?? 'N/A'],
                    ['Proveedor', $producto->proveedor?->nombre ?? 'N/A'],
                ]
            );

            if ($producto->estado === 'disponible') {
                $this->info("🟢 Estado: DISPONIBLE (Listo para salida técnica en el sistema).");
            } else {
                $this->warn("🟠 Estado: " . strtoupper($producto->estado) . " (No se puede consumir hasta reingresar).");
            }
        } else {
            $this->error("❌ El código '{$serie}' NO está registrado en la base de datos.");
            $this->line("Causa posible: Estás escaneando un equipo nuevo que aún no ha sido ingresado en Inventario.");
            $this->line("Puedes registrarlo primero desde el botón '+ Ingresar productos'.");
        }

        $this->newLine();
    }
})->purpose('Herramienta interactiva para probar el lector de código de barras 3nStar');

