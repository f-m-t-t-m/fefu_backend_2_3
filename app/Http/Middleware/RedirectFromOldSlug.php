<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;

class RedirectFromOldSlug
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
        $result = $request->session()->get('result', 'fail');
        if ($result === 'fail') {
            $url = parse_url($request->url(), PHP_URL_PATH);
            $redirect = Redirect::where('old_slug', $url)
                ->orderByDesc('created_at')
                ->orderByDesc('id')->first();
            $newSlug = null;

            while($redirect !== null) {
                $newSlug = $redirect->new_slug;
                $redirect = Redirect::query()->where('old_slug', $newSlug)
                    ->where('id', '>', $redirect->id)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')->first();
            }
            if ($newSlug) {
                return redirect($newSlug)->with('result', 'success');
            }
        }
        return $next($request);
    }
}
