<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->guard('api')->user()->technician_id == null && auth()->guard('api')->user()->client_id == null) {

            return $next($request);
        } else {
            
            return response(['error' => 'فقط ادمین ها اجازه ورود را دارند']);
        }
    }
}
