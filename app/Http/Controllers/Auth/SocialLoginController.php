<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\TwoFactorAuthenticatable;

class SocialLoginController extends Controller
{
    protected SocialAuthService $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     */
    public function callback(Request $request, string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Failed to authenticate with ' . ucfirst($provider)]);
        }

        // If a user is already logged in, they are linking an account
        if (Auth::check()) {
            $result = $this->socialAuthService->linkIdentity(Auth::user(), $provider, $socialUser);
            
            if ($result !== true) {
                return redirect()->route('profile.edit')->withErrors(['social' => $result]);
            }
            
            return redirect()->route('profile.edit')->with('status', ucfirst($provider) . ' account linked successfully.');
        }

        // Otherwise, it's a login/registration attempt
        $result = $this->socialAuthService->handleProviderCallback($provider, $socialUser);

        if (is_string($result)) {
            // Authentication failed with a specific message
            return redirect('/login')->withErrors(['email' => $result]);
        }

        $user = $result;

        // Check if user has 2FA enabled
        if (
            optional($user)->two_factor_secret &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))
        ) {
            // Initiate Fortify 2FA Challenge
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => false,
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('two-factor.login');
        }

        // Complete authentication
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Unlink a social identity from the authenticated user.
     */
    public function unlink(Request $request, string $provider)
    {
        $result = $this->socialAuthService->unlinkIdentity($request->user(), $provider);

        if ($result !== true) {
            return redirect()->route('profile.edit')->withErrors(['social' => $result]);
        }

        return redirect()->route('profile.edit')->with('status', ucfirst($provider) . ' account unlinked successfully.');
    }
}
