<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ActaController;
use App\Http\Controllers\ConsumoController;
use App\Http\Controllers\NotaConsumoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    if (auth()->user()->tieneRol('Tecnico')) {
        return redirect()->route('consumos.index');
    }
    if (auth()->user()->tieneRol('Almacenista')) {
        return redirect()->route('productos.index');
    }
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->tieneRol('Tecnico')) {
        return redirect()->route('consumos.index');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Escaneo de Códigos de Barras (Lector físico y cámara)
    Route::get('/productos/scan/{serie}', [ProductoController::class, 'scanBarcode'])->name('productos.scan');
    Route::get('/productos/check-serie/{serie}', [ProductoController::class, 'checkSerie'])->name('productos.check-serie');
});

Route::middleware(['auth', 'can:gestionar-personal'])->group(function () {
    Route::resource('usuarios', UsuarioController::class)->except(['show']);
    Route::post('/usuarios/{usuario}/restablecer-password', [UsuarioController::class, 'restablecerPassword'])->name('usuarios.restablecer-password');
});

Route::middleware(['auth', 'can:gestionar-almacen'])->group(function () {
    Route::resource('almacenes', AlmacenController::class)->parameters(['almacenes' => 'almacen'])->except(['show']);
});

Route::middleware(['auth', 'can:gestionar-inventario'])->group(function () {
    Route::resource('productos', ProductoController::class)->except(['show']);
    Route::resource('categorias', CategoriaController::class)->parameters(['categorias' => 'categoria'])->except(['show']);
    Route::post('/marcas', [MarcaController::class, 'store'])->name('marcas.store');
    Route::put('/marcas/{marca}', [MarcaController::class, 'update'])->name('marcas.update');
    Route::delete('/marcas/{marca}', [MarcaController::class, 'destroy'])->name('marcas.destroy');
    Route::post('/modelos', [ModeloController::class, 'store'])->name('modelos.store');
    Route::put('/modelos/{modelo}', [ModeloController::class, 'update'])->name('modelos.update');
    Route::delete('/modelos/{modelo}', [ModeloController::class, 'destroy'])->name('modelos.destroy');
});

Route::middleware(['auth', 'can:generar-reportes'])->group(function () {
    Route::get('/reportes/formato', [ReporteController::class, 'editFormato'])->name('reportes.formato.edit');
    Route::put('/reportes/formato', [ReporteController::class, 'updateFormato'])->name('reportes.formato.update');
    Route::post('/reportes/{reporte}/enviar', [ReporteController::class, 'enviar'])->name('reportes.enviar');
    Route::get('/reportes/existencias/pdf', [ReporteController::class, 'existenciasPdfVisor'])->name('reportes.existencias.pdf');
    Route::get('/reportes/existencias/pdf/archivo', [ReporteController::class, 'existenciasPdf'])->name('reportes.existencias.pdf.archivo');
    Route::get('/reportes/existencias/excel', [ReporteController::class, 'existenciasExcelVisor'])->name('reportes.existencias.excel');
    Route::get('/reportes/existencias/excel/descargar', [ReporteController::class, 'existenciasExcel'])->name('reportes.existencias.excel.descargar');
    Route::get('/reportes/salidas/pdf', [ReporteController::class, 'salidasPdfVisor'])->name('reportes.salidas.pdf');
    Route::get('/reportes/salidas/pdf/archivo', [ReporteController::class, 'salidasPdf'])->name('reportes.salidas.pdf.archivo');
    Route::get('/reportes/salidas/excel', [ReporteController::class, 'salidasExcelVisor'])->name('reportes.salidas.excel');
    Route::get('/reportes/salidas/excel/descargar', [ReporteController::class, 'salidasExcel'])->name('reportes.salidas.excel.descargar');
    Route::get('/reportes/{reporte}/excel', [ReporteController::class, 'excel'])->name('reportes.excel');
    Route::get('/reportes/{reporte}/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
    Route::resource('reportes', ReporteController::class)->parameters(['reportes' => 'reporte'])->except(['edit', 'update']);
});

Route::middleware(['auth', 'can:registrar-consumo'])->group(function () {
    Route::get('/consumos/orden/{orden}', [ConsumoController::class, 'orden'])->name('consumos.orden');
    Route::post('/consumos/{consumo}/notas', [NotaConsumoController::class, 'store'])->name('consumos.notas.store');
    Route::get('/consumos/factura/nueva', [ActaController::class, 'create'])->name('consumos.factura.create');
    Route::post('/consumos/factura', [ActaController::class, 'store'])->name('consumos.factura.store');
    Route::get('/consumos/factura/buscar', [ActaController::class, 'buscar'])->name('consumos.factura.buscar');
    Route::get('/consumos/acta/{orden}', [ActaController::class, 'show'])->name('consumos.acta');
    Route::get('/consumos/acta/{orden}/pdf', [ActaController::class, 'pdf'])->name('consumos.acta.pdf');
    Route::get('/consumos/formato-acta', [ActaController::class, 'editFormato'])->name('consumos.formato-acta.edit');
    Route::put('/consumos/formato-acta', [ActaController::class, 'updateFormato'])->name('consumos.formato-acta.update');
    Route::resource('consumos', ConsumoController::class)->parameters(['consumos' => 'consumo'])->except(['edit', 'update']);
});

require __DIR__.'/auth.php';

