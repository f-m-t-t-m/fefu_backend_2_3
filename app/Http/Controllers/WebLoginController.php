<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebLoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if (Auth::check()) {
            return redirect()
                ->route('profile');
        }
        if ($request->isMethod('post')) {
            $request['login'] = strtolower($request['login']);
            $validated = $request->validate([
                'login' => ['required'],
                'password' => ['required'],
            ]);

            if (Auth::attempt($validated)) {
                $request->session()->regenerate();
                return redirect()
                    ->route('profile');
            }
            return redirect()
                ->route('login')->with('error', 'Wrong login or password');
        }
        return view('login');
    }
}
