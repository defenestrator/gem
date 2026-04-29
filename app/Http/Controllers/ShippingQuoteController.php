<?php

namespace App\Http\Controllers;

use App\Services\FedExRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ShippingQuoteController extends Controller
{
    public function __construct(private FedExRateService $fedex) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zip_code' => ['required', 'string', 'regex:/^\d{5}$/'],
        ]);

        try {
            $rates = $this->fedex->getRates($validated['zip_code']);
        } catch (Throwable) {
            return response()->json(['error' => 'Shipping quote unavailable. Please try again.'], 503);
        }

        if (empty($rates)) {
            return response()->json(['error' => 'No rates available for this ZIP code.'], 422);
        }

        return response()->json(['rates' => $rates]);
    }
}
