<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiAuthController extends Controller {
    
    function login(Request $request) {
        $key = 'example_key';
        $payload = [
            'email' => $request->email,
            'password' => $request->password,
            'expires' => Carbon::now()->format('Y-m-d')
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');
        
        return response()->json([
            'access_token' => $jwt
        ], 200);
    }

    function logout(Request $request) {
        return response()->json(['message' => 'Logged out']);
    }
    
    function register(Request $request) {
        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
        } catch(\Exception $e) {
            return response()->json(['message' => 'User not created'], 418);
        }
        return response()->json(['message' => 'User created'], 201);
    }

    function consulta() {
        $lat = '37.16147109102704';
        $lng = '-3.5912354132361344';
        $date = Carbon::now()->format('Y-m-d');
        
        $url = sprintf("https://api.sunrise-sunset.org/json?lat=%s&lng=%s&date=%s", $lat, $lng, $date);
        $response = Http::get($url);

        $sunData = $response->json();
        
        $sunrise = new Carbon(date('H:i:s', strtotime($sunData['results']['sunrise'])));
        $sunset = new Carbon(date('H:i:s', strtotime($sunData['results']['sunset'])));

        $carbonSunrise = $sunrise->hour + ($sunrise->minute / 60);
        $carbonSunset = $sunset->hour + ($sunset->minute / 60);

        $currentHour = Carbon::now()->hour;
        $currentMinutes = Carbon::now()->minute;
        $currentTime = $currentHour + $currentMinutes/60;

        $valoresInterpolados = ($currentTime - $carbonSunrise) / ($carbonSunset - $carbonSunrise);

        return response()->json([
            "interpolados" => $valoresInterpolados,
            "sunrise" => $sunrise,
            "sunset" => $sunset
        ]);
    }
}