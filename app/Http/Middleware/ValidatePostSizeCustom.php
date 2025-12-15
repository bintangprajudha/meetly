<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePostSizeCustom
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxSize = 70 * 1024 * 1024; // 70MB in bytes

        if ($request->server('CONTENT_LENGTH') > $maxSize) {
            return response()->json([
                'message' => 'The uploaded file exceeds the maximum allowed size of 70MB.'
            ], 413);
        }

        return $next($request);
    }
}
