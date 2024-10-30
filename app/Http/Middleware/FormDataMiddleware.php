<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\StringHandle;
use App\Traits\ArrayHandle;

use Illuminate\Support\Facades\Log;

/**
 * Преобразует объект запроса, отправленного в формате `FormData`.
 */
class FormDataMiddleware
{
    use StringHandle;
    use ArrayHandle;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $request->merge($this->deepMap($request->all(), function ($value) {
            return $this->stringNullHandle($value);
        }));

        return $next($request);
    }
}
