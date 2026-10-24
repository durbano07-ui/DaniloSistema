<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Consumo;
use App\Http\Requests\StoreReporteRequest;
use App\Support\ReporteExport;
use Carbon\Carbon;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Obtiene el formato institucional oficial configurado por el Administrador (CU-A2).
     */
    public static function getFormato(): array
    {
        $path = storage_path('app/formato_reporte.json');
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'empresa' => 'Inventory',
            'departamento' => 'Control de Inventarios y Bodegas',
            'ubicacion' => 'Guaranda, Ecuador — Documento Técnico Oficial',
            'nota_legal' => 'Documento oficial generado para fines de control, auditoría y fiscalización de inventario institucional.',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Sin selección no se muestra nada; "Existencias" muestra las opciones de PDF y Excel
        if (! $request->filled('tipo') || in_array($request->tipo, ['Existencias', 'Salidas y Despachos'], true)) {
            return view('reportes.index', ['reportes' => null]);
        }

        $query = Reporte::with('usuario.rol')->latest('id_reporte');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $reportes = $query->paginate(15)->withQueryString();

        return view('reportes.index', compact('reportes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $almacenes = Almacen::with('direccion.ciudad')->orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();
        $tecnicos = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'Tecnico');
        })->where('estado', true)->orderBy('nombre')->get();

        return view('reportes.create', compact('almacenes', 'categorias', 'tecnicos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReporteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $reporte = DB::transaction(function () use ($validated, $request) {
            $rep = Reporte::create([
                'tipo' => $validated['tipo'],
                'fecha' => $validated['fecha'],
                'numero_cedula' => $request->user()->numero_cedula,
            ]);

            if ($validated['tipo'] === 'Inventario General') {
                $series = Producto::pluck('numero_serie')->toArray();
                if (!empty($series)) {
                    $rep->productos()->sync($series);
                }
            } elseif ($validated['tipo'] === 'Existencias por Bodega') {
                $almacenIds = !empty($validated['id_almacen'])
                    ? [$validated['id_almacen']]
                    : Almacen::pluck('id_almacen')->toArray();
                if (!empty($almacenIds)) {
                    $rep->almacenes()->sync($almacenIds);
                }
            } elseif ($validated['tipo'] === 'Personal y Roles') {
                $cedulas = Usuario::pluck('numero_cedula')->toArray();
                if (!empty($cedulas)) {
                    $rep->usuarios()->sync($cedulas);
                }
            } elseif ($validated['tipo'] === 'Salidas y Despachos') {
                $query = Consumo::query();

                if (!empty($validated['fecha_desde'])) {
                    $query->whereDate('fecha', '>=', $validated['fecha_desde']);
                }
                if (!empty($validated['fecha_hasta'])) {
                    $query->whereDate('fecha', '<=', $validated['fecha_hasta']);
                }
                if (!empty($validated['numero_cedula_tecnico'])) {
                    $query->where('numero_cedula', $validated['numero_cedula_tecnico']);
                }
                if (!empty($validated['id_almacen'])) {
                    $query->whereHas('producto', function ($q) use ($validated) {
                        $q->where('id_almacen', $validated['id_almacen']);
                    });
                }

                $consumoIds = $query->pluck('id_consumo')->toArray();
                if (!empty($consumoIds)) {
                    $rep->consumos()->sync($consumoIds);
                }
            }

            return $rep;
        });

        return redirect()->route('reportes.show', $reporte)->with('status', 'Reporte consolidado y generado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reporte $reporte): View
    {
        $reporte->load([
            'usuario.rol',
            'productos.categoria',
            'productos.modelo',
            'productos.almacen',
            'productos.proveedor',
            'almacenes.productos.categoria',
            'almacenes.productos.modelo',
            'almacenes.direccion.ciudad',
            'usuarios.rol',
            'consumos.producto.categoria',
            'consumos.orden',
            'consumos.producto.almacen',
            'consumos.usuario',
        ]);

        $formato = self::getFormato();

        return view('reportes.show', compact('reporte', 'formato'));
    }

    /**
     * Visor del reporte de existencias en PDF.
     */
    public function existenciasPdfVisor(): View
    {
        return view('reportes.visor', [
            'titulo' => 'Reporte de Existencias',
            'formato' => 'pdf',
            'volver' => route('reportes.index', ['tipo' => 'Existencias']),
            'archivo' => route('reportes.existencias.pdf.archivo'),
            'descarga' => route('reportes.existencias.pdf.archivo', ['descargar' => 1]),
        ]);
    }

    /**
     * PDF de existencias (se muestra en el navegador o se descarga).
     */
    public function existenciasPdf(Request $request): \Illuminate\Http\Response
    {
        $html = view('reportes.existencias-pdf', [
            'filas' => ReporteExport::existenciasActuales(),
            'formato' => self::getFormato(),
            'fecha' => now(),
        ])->render();

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans']);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombre = 'Existencias-' . now()->format('Y-m-d') . '.pdf';
        $disposicion = $request->boolean('descargar') ? 'attachment' : 'inline';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposicion}; filename=\"{$nombre}\"",
        ]);
    }

    /**
     * Visor del reporte de existencias en formato de hoja de cálculo.
     */
    public function existenciasExcelVisor(): View
    {
        return view('reportes.visor', [
            'titulo' => 'Reporte de Existencias',
            'formato' => 'excel',
            'volver' => route('reportes.index', ['tipo' => 'Existencias']),
            'descarga' => route('reportes.existencias.excel.descargar'),
            'nombreArchivo' => 'Existencias-' . now()->format('Y-m-d') . '.xlsx',
            'columnas' => ReporteExport::COLUMNAS_EXISTENCIAS,
            'filas' => ReporteExport::existenciasActuales(),
            'columnaTotal' => 5,
        ]);
    }

    /**
     * Descarga el reporte de existencias en Excel (.xlsx).
     */
    public function existenciasExcel(): StreamedResponse
    {
        $filas = ReporteExport::existenciasActuales();
        $formato = self::getFormato();

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('Existencias');

        $hoja->setCellValue('A1', $formato['empresa']);
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $hoja->setCellValue('A2', 'Reporte de Existencias — ' . now()->format('d/m/Y H:i'));

        $hoja->fromArray(ReporteExport::COLUMNAS_EXISTENCIAS, null, 'A4');
        $estilo = $hoja->getStyle('A4:F4');
        $estilo->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');

        $fila = 5;
        foreach ($filas as $datos) {
            foreach ($datos as $i => $valor) {
                $hoja->setCellValueExplicit([$i + 1, $fila], $valor, is_int($valor) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
            }
            $fila++;
        }

        $hoja->setCellValue("A{$fila}", 'Total en stock');
        $hoja->setCellValue("F{$fila}", $filas->sum(5));
        $hoja->getStyle("A{$fila}:F{$fila}")->getFont()->setBold(true);

        foreach (range('A', 'F') as $columna) {
            $hoja->getColumnDimension($columna)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'Existencias-' . now()->format('Y-m-d') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Rango de fechas del Acta de Entrega (por defecto, el mes actual).
     */
    private static function rangoSalidas(Request $request): array
    {
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        return [
            $validated['desde'] ?? now()->startOfMonth()->toDateString(),
            $validated['hasta'] ?? now()->toDateString(),
        ];
    }

    /**
     * Visor del Acta de Entrega de salidas en PDF.
     */
    public function salidasPdfVisor(Request $request): View
    {
        [$desde, $hasta] = self::rangoSalidas($request);

        return view('reportes.visor', [
            'titulo' => 'Acta de Entrega',
            'formato' => 'pdf',
            'volver' => route('reportes.index', ['tipo' => 'Salidas y Despachos', 'desde' => $desde, 'hasta' => $hasta]),
            'archivo' => route('reportes.salidas.pdf.archivo', compact('desde', 'hasta')),
            'descarga' => route('reportes.salidas.pdf.archivo', compact('desde', 'hasta') + ['descargar' => 1]),
        ]);
    }

    /**
     * PDF del Acta de Entrega de salidas (horizontal, formato GRH-FO-16).
     */
    public function salidasPdf(Request $request): \Illuminate\Http\Response
    {
        [$desde, $hasta] = self::rangoSalidas($request);

        $html = view('reportes.acta-salidas-pdf', [
            'filas' => ReporteExport::actaSalidas($desde, $hasta),
            'encabezado' => ReporteExport::ENCABEZADO_ACTA_SALIDAS,
        ])->render();

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans', 'isPhpEnabled' => true, 'chroot' => public_path()]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $nombre = "Acta-Entrega-{$desde}-al-{$hasta}.pdf";
        $disposicion = $request->boolean('descargar') ? 'attachment' : 'inline';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposicion}; filename=\"{$nombre}\"",
        ]);
    }

    /**
     * Visor del Acta de Entrega de salidas en formato de hoja de cálculo.
     */
    public function salidasExcelVisor(Request $request): View
    {
        [$desde, $hasta] = self::rangoSalidas($request);

        return view('reportes.visor', [
            'titulo' => 'Acta de Entrega',
            'formato' => 'excel',
            'volver' => route('reportes.index', ['tipo' => 'Salidas y Despachos', 'desde' => $desde, 'hasta' => $hasta]),
            'descarga' => route('reportes.salidas.excel.descargar', compact('desde', 'hasta')),
            'nombreArchivo' => "Acta-Entrega-{$desde}-al-{$hasta}.xlsx",
            'columnas' => ReporteExport::COLUMNAS_ACTA_SALIDAS,
            'filas' => ReporteExport::actaSalidas($desde, $hasta),
            'columnaTotal' => null,
        ]);
    }

    /**
     * Descarga el Acta de Entrega de salidas en Excel (.xlsx).
     */
    public function salidasExcel(Request $request): StreamedResponse
    {
        [$desde, $hasta] = self::rangoSalidas($request);
        $filas = ReporteExport::actaSalidas($desde, $hasta);
        $enc = ReporteExport::ENCABEZADO_ACTA_SALIDAS;

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('Acta de Entrega');
        $hoja->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        // Logo de Fibercom en la esquina superior izquierda
        $logo = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $logo->setName('Fibercom');
        $logo->setPath(public_path('images/logo-fibercom.png'));
        $logo->setHeight(48);
        $logo->setCoordinates('A1');
        $logo->setWorksheet($hoja);
        $hoja->getRowDimension(1)->setRowHeight(20);
        $hoja->getRowDimension(2)->setRowHeight(20);

        $hoja->setCellValue('E2', 'ACTA DE ENTREGA');
        $hoja->getStyle('E2')->getFont()->setBold(true)->setSize(14);
        $hoja->setCellValue('I1', 'CÓDIGO:');
        $hoja->setCellValue('J1', $enc['codigo']);
        $hoja->setCellValue('I2', 'VERSIÓN:');
        $hoja->setCellValue('J2', $enc['version']);
        $hoja->setCellValue('I3', 'FECHA:');
        $hoja->setCellValue('J3', $enc['fecha']);
        $hoja->getStyle('I1:I3')->getFont()->setBold(true);
        $hoja->setCellValue('A4', 'Período: ' . Carbon::parse($desde)->format('d/m/Y') . ' al ' . Carbon::parse($hasta)->format('d/m/Y'));

        $hoja->fromArray(ReporteExport::COLUMNAS_ACTA_SALIDAS, null, 'A5');
        $estilo = $hoja->getStyle('A5:J5');
        $estilo->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3B4A57');

        $fila = 6;
        foreach ($filas as $datos) {
            foreach ($datos as $i => $valor) {
                $hoja->setCellValueExplicit([$i + 1, $fila], $valor, is_int($valor) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
            }
            $fila++;
        }

        $hoja->getStyle('A5:J' . max(5, $fila - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja->getStyle('A6:J' . max(6, $fila - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        foreach (range('A', 'J') as $columna) {
            $hoja->getColumnDimension($columna)->setAutoSize(true);
        }
        // Notas con ajuste de texto; columnas de firma con ancho fijo para firmar a mano
        $hoja->getColumnDimension('F')->setAutoSize(false)->setWidth(45);
        $hoja->getStyle('F6:F' . max(6, $fila - 1))->getAlignment()->setWrapText(true);
        $hoja->getColumnDimension('H')->setAutoSize(false)->setWidth(18);
        $hoja->getColumnDimension('J')->setAutoSize(false)->setWidth(18);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, "Acta-Entrega-{$desde}-al-{$hasta}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Exporta el reporte a un archivo de Excel (.xlsx).
     */
    public function excel(Reporte $reporte): StreamedResponse
    {
        $export = new ReporteExport($reporte);
        $formato = self::getFormato();

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle(substr($export->codigo(), 0, 31));

        // Encabezado del documento
        $hoja->setCellValue('A1', $formato['empresa']);
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $hoja->setCellValue('A2', $formato['departamento']);
        $hoja->setCellValue('A4', 'Reporte');
        $hoja->setCellValue('B4', $export->codigo() . ' — ' . $reporte->tipo);
        $hoja->setCellValue('A5', 'Fecha');
        $hoja->setCellValue('B5', \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y'));
        $hoja->setCellValue('A6', 'Emitido por');
        $hoja->setCellValue('B6', $reporte->usuario?->nombre . ' (' . $reporte->numero_cedula . ')');
        $hoja->getStyle('A4:A6')->getFont()->setBold(true);

        $fila = 8;
        $maxColumnas = 1;
        foreach ($export->secciones() as $seccion) {
            $columnas = count($seccion['encabezados']);
            $maxColumnas = max($maxColumnas, $columnas);
            $ultima = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnas);

            if ($seccion['titulo']) {
                $hoja->setCellValue("A{$fila}", $seccion['titulo']);
                $hoja->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(12);
                $fila++;
            }

            $hoja->fromArray($seccion['encabezados'], null, "A{$fila}");
            $estilo = $hoja->getStyle("A{$fila}:{$ultima}{$fila}");
            $estilo->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
            $fila++;

            // Los textos se guardan como texto para no alterar series ni cédulas con ceros iniciales
            foreach ($seccion['filas'] as $datos) {
                foreach (array_values($datos) as $i => $valor) {
                    $hoja->setCellValueExplicit(
                        [$i + 1, $fila],
                        $valor,
                        is_int($valor) || is_float($valor) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING
                    );
                }
                $fila++;
            }

            if ($seccion['total']) {
                $hoja->setCellValue("A{$fila}", $seccion['total'][0]);
                $hoja->setCellValue("{$ultima}{$fila}", $seccion['total'][1]);
                $hoja->getStyle("A{$fila}:{$ultima}{$fila}")->getFont()->setBold(true);
                $fila++;
            }

            $fila++;
        }

        for ($i = 1; $i <= $maxColumnas; $i++) {
            $hoja->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $export->nombreArchivo('xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Exporta el reporte a un archivo PDF.
     */
    public function pdf(Reporte $reporte): StreamedResponse
    {
        $export = new ReporteExport($reporte);

        $html = view('reportes.pdf', [
            'reporte' => $reporte,
            'export' => $export,
            'formato' => self::getFormato(),
        ])->render();

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans']);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $export->nombreArchivo('pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reporte $reporte): RedirectResponse
    {
        $this->authorize('generar-reportes');

        DB::transaction(function () use ($reporte) {
            $reporte->productos()->detach();
            $reporte->almacenes()->detach();
            $reporte->usuarios()->detach();
            $reporte->consumos()->detach();
            $reporte->delete();
        });

        return redirect()->route('reportes.index')->with('status', 'Reporte eliminado exitosamente.');
    }

    /**
     * CU-04: Enviar reporte de inventario a la administración central.
     */
    public function enviar(Reporte $reporte): RedirectResponse
    {
        $this->authorize('enviar-reportes');

        return redirect()->route('reportes.show', $reporte)
            ->with('status', 'El reporte #REP-' . str_pad($reporte->id_reporte, 4, '0', STR_PAD_LEFT) . ' ha sido enviado formalmente a la Administración Central.');
    }

    /**
     * CU-A2: Muestra el formulario para editar el formato institucional de reportes (Exclusivo Administrador).
     */
    public function editFormato(): View
    {
        $this->authorize('editar-formato-reporte');
        $formato = self::getFormato();

        return view('reportes.formato', compact('formato'));
    }

    /**
     * CU-A2: Actualiza el formato institucional de reportes.
     */
    public function updateFormato(Request $request): RedirectResponse
    {
        $this->authorize('editar-formato-reporte');

        $validated = $request->validate([
            'empresa' => ['required', 'string', 'max:100'],
            'departamento' => ['required', 'string', 'max:150'],
            'ubicacion' => ['required', 'string', 'max:150'],
            'nota_legal' => ['required', 'string', 'max:500'],
        ]);

        $path = storage_path('app/formato_reporte.json');
        file_put_contents($path, json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->route('reportes.index')
            ->with('status', 'Formato institucional de reportes actualizado exitosamente.');
    }
}
