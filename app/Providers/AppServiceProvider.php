<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderPhotoType;
use App\Models\Payment;
use App\Models\Quote;
use App\Observers\PaymentObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);

        // Alias estables para serviceables.serviceable_type (no se rompe si se renombra una clase).
        Relation::morphMap([
            'order' => Order::class,
            'quote' => Quote::class,
            'order_photo_type' => OrderPhotoType::class,
        ]);

        // Correo de recuperación de contraseña en español.
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $minutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

            return (new MailMessage)
                ->subject('Restablece tu contraseña · Creatifoto')
                ->greeting('Hola')
                ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta en Creatifoto.')
                ->action('Restablecer contraseña', $url)
                ->line("Este enlace caduca en {$minutes} minutos.")
                ->line('Si tú no hiciste esta solicitud, puedes ignorar este correo.')
                ->salutation('Equipo Creatifoto');
        });
    }
}