<?php

namespace App\Http\Middleware;

use Closure;

use Config, App;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = Config::get('app.locale');
        if ($request->session()->has('user.language')) {
            $locale = $request->session()->get('user.language');
        }
        App::setLocale($locale);

        return $next($request);
    }
}
