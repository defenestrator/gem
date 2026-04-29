<?php

namespace App\Http\Controllers;

use App\Services\EasyShipLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ShipCenterController extends Controller
{
    public function __construct(private EasyShipLocationService $easyship) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            $location = $this->easyship->getNearestDropOff(
                (float) $validated['lat'],
                (float) $validated['lng'],
            );
        } catch (Throwable) {
            return response()->json(['error' => 'Could not find a nearby FedEx Ship Center.'], 503);
        }

        return response()->json(['location' => $location]);
    }
}
