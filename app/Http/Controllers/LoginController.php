<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function view(Request $request): View
    {
        $deviceName = $request->get('device_name');
        $redirect = $request->get('redirect');

        return view('login', compact('deviceName', 'redirect'));
    }

    /**
     * Handle user login and token generation
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string',
            'redirect' => 'required|string',
        ], [
            'device_name.required' => 'The device_name parameter is required.',
            'redirect.required' => 'The redirect URL parameter is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        if (! $this->validateRedirect($request)) {
            return redirect()->back()
                ->withErrors(['redirect' => 'Invalid redirect URL. It is not whitelisted.'])
                ->withInput($request->except('password'));
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            return redirect()->back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withInput($request->except('password'));
        }

        $user = Auth::user();

        $token = $user->createToken($request->get('device_name'))->plainTextToken;

        $redirectLink = $request->get('redirect');

        return redirect()
            ->route('login.success', [
                'redirect' => $redirectLink,
            ])
            ->withCookie(
                cookie(
                    'token',
                    $token,
                    1,
                    '/',
                    null,
                    $request->secure(), // Use the current request's protocol
                    false,
                    false,
                    'Lax'
                )
            );
    }

    private function validateRedirect(Request $request): bool
    {
        $redirectUrl = $request->input('redirect');

        Log::debug('Validate redirect URL', [
            'redirect_url' => $redirectUrl,
            'allowed_hosts' => config('app.redirect_urls_whitelist'),
        ]);

        foreach (config('app.redirect_urls_whitelist') as $host) {
            if (str_starts_with($redirectUrl, $host)) {
                return true;
            }
        }

        return false;
    }
}
