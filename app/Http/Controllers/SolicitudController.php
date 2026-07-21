<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudMail;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SolicitudController extends Controller
{
    public function create()
    {
        return view('solicitudes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria' => ['required', 'string', 'max:50'],
            'asunto' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
        ]);

        $ultima = Solicitud::query()->latest('id')->first();
        $numero = $ultima ? ((int) substr($ultima->folio, 4)) + 1 : 1;
        $folio = 'SOL-'.str_pad((string) $numero, 5, '0', STR_PAD_LEFT);

        $datosExtra = $request->except([
            '_token', '_method', 'categoria', 'asunto', 'descripcion',
        ]);

        $solicitud = Solicitud::create([
            'folio' => $folio,
            'usuario_id' => Auth::id(),
            'categoria' => $validated['categoria'],
            'asunto' => $validated['asunto'],
            'descripcion' => $validated['descripcion'],
            'datos_extra' => $datosExtra ?: null,
            'estado' => 'pendiente',
            'correo_enviado' => false,
            'correo_enviado_at' => null,
        ]);

        try {
            Mail::to('alejandrotsc01@gmail.com')->send(
                new SolicitudMail($solicitud)
            );

            $solicitud->update([
                'correo_enviado' => true,
                'correo_enviado_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de la solicitud '.$folio, [
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('solicitudes.create')
            ->with('success', 'Solicitud enviada correctamente.')
            ->with('folio', $folio);
    }

    public function misSolicitudes(Request $request)
    {
        $request->validate([
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'anio' => ['nullable', 'integer', 'between:2020,'.now()->year],
        ]);

        $mes = (int) $request->input('mes', now()->month);
        $anio = (int) $request->input('anio', now()->year);

        $aniosDisponibles = Solicitud::query()
            ->where('usuario_id', Auth::id())
            ->whereNotNull('created_at')
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');

        if (! $aniosDisponibles->contains(now()->year)) {
            $aniosDisponibles->push(now()->year);
            $aniosDisponibles = $aniosDisponibles->sortDesc()->values();
        }

        $solicitudes = Solicitud::query()
            ->where('usuario_id', Auth::id())
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->latest()
            ->get();

        return view('solicitudes.mis-solicitudes', compact(
            'solicitudes', 'mes', 'anio', 'aniosDisponibles'
        ));
    }

    public function show(Solicitud $solicitud)
    {
        abort_unless(
            (int) $solicitud->usuario_id === (int) Auth::id(),
            403,
            'No tienes permiso para consultar esta solicitud.'
        );

        return view('solicitudes.show', compact('solicitud'));
    }
}
