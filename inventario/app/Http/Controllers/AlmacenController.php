<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlmacenRequest;
use App\Http\Requests\UpdateAlmacenRequest;
use App\Models\Almacen;
use App\Models\Ciudad;
use App\Models\Direccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AlmacenController extends Controller
{
    public function index(): View
    {
        $almacenes = Almacen::with('direccion.ciudad', 'usuario')
            ->withCount('productos')
            ->orderBy('nombre')
            ->paginate(15);

        return view('almacenes.index', compact('almacenes'));
    }

    public function create(): View
    {
        $ciudades = Ciudad::orderBy('nombre')->get();

        return view('almacenes.create', compact('ciudades'));
    }

    public function store(StoreAlmacenRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $direccion = Direccion::create([
                'calle' => $request->validated('calle'),
                'id_ciudad' => $request->validated('id_ciudad'),
            ]);

            Almacen::create([
                'nombre' => $request->validated('nombre'),
                'id_direccion' => $direccion->id_direccion,
                'numero_cedula' => auth()->id(),
            ]);
        });

        return redirect()->route('almacenes.index')
            ->with('status', 'Almacén creado correctamente.');
    }

    public function edit(Almacen $almacen): View
    {
        $almacen->load('direccion');
        $ciudades = Ciudad::orderBy('nombre')->get();

        return view('almacenes.edit', compact('almacen', 'ciudades'));
    }

    public function update(UpdateAlmacenRequest $request, Almacen $almacen): RedirectResponse
    {
        DB::transaction(function () use ($request, $almacen) {
            $almacen->direccion->update([
                'calle' => $request->validated('calle'),
                'id_ciudad' => $request->validated('id_ciudad'),
            ]);

            $almacen->update([
                'nombre' => $request->validated('nombre'),
            ]);
        });

        return redirect()->route('almacenes.index')
            ->with('status', 'Almacén actualizado correctamente.');
    }

    public function destroy(Request $request, Almacen $almacen): RedirectResponse
    {
        $this->authorize('gestionar-almacen');

        if ($almacen->productos()->exists()) {
            return redirect()->route('almacenes.index')
                ->with('error', 'No se puede eliminar: este almacén todavía tiene productos asociados.');
        }

        DB::transaction(function () use ($almacen) {
            $direccion = $almacen->direccion;
            $almacen->delete();
            $direccion->delete();
        });

        return redirect()->route('almacenes.index')
            ->with('status', 'Almacén eliminado correctamente.');
    }
}
