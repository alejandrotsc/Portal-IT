<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;


class RolMiddleware
{


    public function handle(
        Request $request,
        Closure $next,
        $rol
    )
    {


        if(
            !auth()->check()
        )
        {

            return redirect()
                ->route('login');

        }



        if(
            auth()
            ->user()
            ->rol
            ->nombre !== $rol
        )
        {

            abort(403);

        }



        return $next($request);

    }


}