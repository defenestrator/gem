<?php

namespace App\Http\Controllers;

use App\Services\EasyShipRateService;
use App\Services\EasyShipLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShippingQuoteController extends Controller
{
    public function __construct(
        private EasyShipRateService $easyship,
        private EasyShipLocationService $locationService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zip_code' => ['required', 'string', 'regex:/^\d{5}$/'],
            'city'     => ['sometimes', 'string', 'max:100'],
            'state'    => ['sometimes', 'string', 'max:50'],
        ]);

        try {
            // Find the nearest FedEx Ship Center for the destination ZIP code
            $shipCenter = $this->locationService->getNearestShipCenterByZip($validated['zip_code']);
            
            // Get rates using the ship center as the destination
            $rates = $this->easyship->getRates(
                $shipCenter['postal_code'],
                $shipCenter['city'],
                $shipCenter['state'],
                $shipCenter,
            );
        } catch (Throwable $e) {
            Log::error('ShippingQuoteController error: ' . $e->getMessage(), [
                'zip_code' => $validated['zip_code'],
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Shipping quote unavailable. Please try again.'], 503);
        }

        if (empty($rates)) {
            return response()->json(['error' => 'No rates available for this ZIP code.'], 422);
        }

        return response()->json([
            'rates'      => $rates,
            'ship_center' => $shipCenter,
        ]);
    }
}
