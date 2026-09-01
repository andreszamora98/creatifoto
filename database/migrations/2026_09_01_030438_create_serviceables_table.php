<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivote polimórfico: una indicación adicional puede aplicar
        // a TODA la orden (serviceable_type = Order) o a UN tipo de foto
        // específico dentro de la orden (serviceable_type = OrderPhotoType).
        // La regla de "si es global se bloquean las individuales" se valida
        // en el form request / frontend, no aquí.
        Schema::create('serviceables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('additional_service_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('serviceable_id');
            $table->string('serviceable_type');

            // precio fijado por el recepcionista; si es null, se usa additional_services.default_price
            $table->decimal('price', 10, 2)->nullable();
            $table->text('notes')->nullable(); // ej. proporciones específicas: "8x10"

            $table->timestamps();

            $table->index(['serviceable_type', 'serviceable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serviceables');
    }
};