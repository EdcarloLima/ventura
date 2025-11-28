<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Parking\Actions\PerformCheckoutAction;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function store(string $ticketId, PerformCheckoutAction $action): JsonResponse
    {
        try {
            $data = $action->execute($ticketId);

            return response()->json([
                'message' => 'Checkout calculado. Aguardando pagamento.',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}