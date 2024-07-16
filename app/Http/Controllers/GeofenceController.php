<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Geofence; // Import the Geofence model

class GeofenceController extends Controller
{
    public function verify(Request $request)
    {
        $userLocation = $request->input('location');
        $geofences = Geofence::all(); // Now this should work

        foreach ($geofences as $geofence) {
            if ($this->isLocationWithinGeofence($userLocation, $geofence)) {
                return response()->json(['allowed' => true]);
            }
        }

        return response()->json(['allowed' => false]);
    }

    private function isLocationWithinGeofence($userLocation, $geofence)
    {
        // implement your geofence logic here, e.g. using the Haversine formula
        // to calculate the distance between the user's location and the geofence
        // if the distance is within the geofence radius, return true
        // otherwise, return false
    }
}