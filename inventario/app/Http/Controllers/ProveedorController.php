<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(): View
    {
        $proveedores = Proveedor::with('emails', 'telefonos', 'usuario')
            ->withCount('productos')
            ->orderBy('nombre')
            ->paginate(15);

        return view('proveedores.index', compact('proveedores'));
    }

    public function create(): View
    {
        return view('proveedores.create');
    }

    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $proveedor = Proveedor::create([
                'nombre' => trim($request->validated('nombre')),
                'numero_cedula' => $request->user()->numero_cedula,
            ]);

            self::guardarContacto($proveedor, $request->validated('correo'), $request->validated('telefono'));
        });

        return redirect()->route('proveedores.index')
            ->with('status', 'Proveedor registrado correctamente.');
    }

    public function edit(Proveedor $proveedor): View
    {
        $proveedor->load('emails', 'telefonos');

        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        DB::transaction(function () use ($request, $proveedor) {
            $proveedor->update(['nombre' => trim($request->validated('nombre'))]);

            self::guardarContacto($proveedor, $request->validated('correo'), $request->validated('telefono'));
        });

        return redirect()->route('proveedores.index')
            ->with('status', 'Proveedor actualizado correctamente.');
    }

    /**
     * Guarda el correo y teléfono principales del proveedor; si se dejan vacíos, se eliminan.
     */
    private static function guardarContacto(Proveedor $proveedor, ?string $correo, ?string $telefono): void
    {
        $correo = trim((string) $correo);
        $telefono = trim((string) $telefono);

        $email = $proveedor->emails()->first();
        if ($correo === '') {
            $email?->delete();
        } elseif ($email) {
            $email->update(['correo' => $correo]);
        } else {
            $proveedor->emails()->create(['correo' => $correo]);
        }

        $telf = $proveedor->telefonos()->first();
        if ($telefono === '') {
            $telf?->delete();
        } elseif ($telf) {
            $telf->update(['numero' => $telefono]);
        } else {
            $proveedor->telefonos()->create(['numero' => $telefono]);
        }
    }
}
