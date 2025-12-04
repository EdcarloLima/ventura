<?php

use Illuminate\Http\Request;
use App\Domain\Parking\Actions\RegisterEntryAction;
use App\Domain\Parking\Models\Ticket;
use App\Domain\Parking\Models\ParkingSpot;

/*
|--------------------------------------------------------------------------
| API Routes - MVP 0
|--------------------------------------------------------------------------
*/

// ============================================
// ENTRADA DE VEÍCULOS (MVP 0)
// ============================================
Route::post('/vehicles/entry', function (Request $request, RegisterEntryAction $action) {
    try {
        $validated = $request->validate([
            'plate' => 'required|string|size:7|regex:/^[A-Z]{3}[0-9]{4}$/',
            'gate_id' => 'nullable|string',
        ]);

        $result = $action->execute(
            strtoupper($validated['plate']),
            $validated['gate_id'] ?? 'entrada-1'
        );

        return response()->json([
            'success' => true,
            'message' => 'Veículo registrado com sucesso',
            'data' => [
                'ticket' => [
                    'id' => $result->ticket->id,
                    'entry_at' => $result->ticket->entryAt->format('Y-m-d H:i:s'),
                    'status' => $result->ticket->status,
                ],
                'vehicle' => [
                    'plate' => $result->vehicle->plate,
                    'type' => $result->vehicle->type,
                ],
                'spot' => [
                    'code' => $result->spot->code,
                    'status' => $result->spot->status,
                ],
            ],
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $e->getCode() ?: 500);
    }
});

// ============================================
// CONSULTAS (MVP 0)
// ============================================

// Listar vagas disponíveis
Route::get('/spots/available', function () {
    $spots = ParkingSpot::where('status', 'Disponível')
        ->orderBy('code')
        ->get(['id', 'code', 'status']);

    return response()->json([
        'success' => true,
        'total' => $spots->count(),
        'data' => $spots,
    ]);
});

// Listar tickets ativos
Route::get('/tickets/active', function () {
    $tickets = Ticket::whereIn('status', ['Aberto', 'Pagamento pendente', 'Pago'])
        ->with(['vehicle', 'parkingSpot'])
        ->orderBy('entry_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'total' => $tickets->count(),
        'data' => $tickets,
    ]);
});

// Consultar ticket específico
Route::get('/tickets/{ticketId}', function (string $ticketId) {
    $ticket = Ticket::with(['vehicle', 'parkingSpot'])
        ->findOrFail($ticketId);

    return response()->json([
        'success' => true,
        'data' => [
            'ticket' => [
                'id' => $ticket->id,
                'entry_at' => $ticket->entry_at->format('Y-m-d H:i:s'),
                'status' => $ticket->status,
                'total_amount' => $ticket->total_amount,
            ],
            'vehicle' => [
                'plate' => $ticket->vehicle->plate,
                'type' => $ticket->vehicle->type,
                'brand' => $ticket->vehicle->brand,
                'model' => $ticket->vehicle->model,
            ],
            'spot' => [
                'code' => $ticket->parkingSpot->code,
                'status' => $ticket->parkingSpot->status,
            ],
        ],
    ]);
});

// Estatísticas do sistema
Route::get('/stats', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'total_spots' => ParkingSpot::count(),
            'available_spots' => ParkingSpot::where('status', 'Disponível')->count(),
            'occupied_spots' => ParkingSpot::where('status', 'Ocupado')->count(),
            'maintenance_spots' => ParkingSpot::where('status', 'Manutenção')->count(),
            'active_tickets' => Ticket::whereIn('status', ['Aberto', 'Pagamento pendente', 'Pago'])->count(),
            'total_tickets' => Ticket::count(),
        ],
    ]);
});
