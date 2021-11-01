<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppealPostRequest;
use App\Models\Appeal;
use App\Sanitizers\PhoneSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\Gender;

class AppealController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \App\Http\Requests\AppealPostRequest $request
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('appeal');
    }

    public function save(AppealPostRequest $request)
    {
        $validated = $request->validated();

        $appeal = new Appeal();
        $appeal->name = $validated['name'];
        $appeal->surname = $validated['surname'];
        $appeal->patronymic = $validated['patronymic'];
        $appeal->age = $validated['age'];
        $appeal->gender = $validated['gender'];
        $appeal->phone = PhoneSanitizer::sanitize($validated['phone']);
        $appeal->email = $validated['email'];
        $appeal->message = $validated['message'];
        $appeal->save();

        return redirect()
            ->route('appeal')
            ->with('success', 'Appeal created');
    }
}
