<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class VisitorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        $country = null;
        $city = null;

        try {
            $position = Location::get($ip);

            if ($position) {
                $country = $position->countryName;
                $city = $position->cityName;
            }
        } catch (\Throwable $e) {
            // Silently fail – location is optional
        }

        $visitor = Visitor::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'country' => $country,
            'city' => $city,
        ]);

        return response()->json(['status' => 'ok', 'id' => $visitor->id], 201);
    }
}
