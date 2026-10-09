<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderPhotoType;
use App\Models\Payment;
use App\Models\Quote;
use App\Observers\PaymentObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
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
    }
}