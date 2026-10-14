<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function showLogin()
    {
        return view('auth.login', ['view' => 'login']);
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.login', [
            'view'  => 'reset',
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ], $this->messages());

        $key = Str::lower($data['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message'     => "Demasiados intentos. Vuelve a intentarlo en {$seconds} segundos.",
                'retry_after' => $seconds,
            ], 429);
        }

        $credentials = ['email' => $data['email'], 'password' => $data['password']];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son correctos.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => redirect()->intended('/')->getTargetUrl(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ], $this->messages());

        Password::sendResetLink(['email' => $data['email']]);

        return response()->json([
            'message' => 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $this->messages());

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password'       => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Tu contraseña se actualizó. Ya puedes iniciar sesión.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => match ($status) {
                Password::INVALID_TOKEN => 'El enlace expiró o ya fue utilizado. Solicita uno nuevo.',
                Password::INVALID_USER  => 'No pudimos validar este enlace. Solicita uno nuevo.',
                default                 => 'No se pudo restablecer la contraseña. Inténtalo de nuevo.',
            },
        ]);
    }

    /* ------------------------------------------------------------------ */

    private function messages(): array
    {
        return [
            'email.required'     => 'Escribe tu correo electrónico.',
            'email.email'        => 'Escribe un correo válido.',
            'password.required'  => 'Escribe tu contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'token.required'     => 'El enlace no es válido. Solicita uno nuevo.',
        ];
    }
}
