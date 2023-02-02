<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class ValidateJWT
{
    public function handle(Request $request, Closure $next) {
        //Comprueba si esta el token en la cabecera
        if( $request->headers->has('Authorization' ) ){
            try {
                //Quitamos el "Bearer" del token
                $token = substr($request->headers->get('Authorization'), 7);

                //Decodifica el token
                $decoded = JWT::decode($token, new Key("example_key", 'HS256'));
                $decoded = (array) $decoded;

                //Comprueba si ha expirado con la fecha del payload
                if( !$decoded['expires'] > Carbon::now() ) {
                    return response()->json(['error' => 'Tu Token ha Expirado'], 401);
                }

            } catch (\Exception $e) {
                return response()->json(['error' => 'Tu token es inválido'], 401);
            }
        } else {
            return response()->json(['error' => 'Debes aportar una API Key para acceder'], 401);
        }

        return $next($request);
    }
}