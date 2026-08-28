<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $origin = $request->headers->get('Origin');

        $allowedOrigins = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'https://enhs-leave-management-system-woad.vercel.app',
            'https://enhs-leave-management-system-git-leave-ebc156-enhs-leave-system.vercel.app',
        ];

        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set(
                'Access-Control-Allow-Origin',
                $origin
            );

            $response->headers->set(
                'Access-Control-Allow-Credentials',
                'true'
            );

            $response->headers->set(
                'Access-Control-Allow-Methods',
                'GET, POST, PUT, PATCH, DELETE, OPTIONS'
            );

            $response->headers->set(
                'Access-Control-Allow-Headers',
                'Origin, Content-Type, Accept, Authorization, X-Requested-With'
            );
        }

        return $response;
    }
}