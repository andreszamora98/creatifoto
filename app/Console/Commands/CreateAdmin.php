<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'creatifoto:admin {--name= : Nombre} {--email= : Correo}';

    protected $description = 'Crea (o actualiza la contraseña de) el usuario administrador de Creatifoto';

    public function handle(): int
    {
        $name  = $this->option('name')  ?: $this->ask('Nombre');
        $email = $this->option('email') ?: $this->ask('Correo electrónico');

        $password     = $this->secret('Contraseña (mínimo 8 caracteres)');
        $confirmation = $this->secret('Confirma la contraseña');

        $validator = Validator::make(
            compact('name', 'email', 'password') + ['password_confirmation' => $confirmation],
            [
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->exists();

        User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password]
        );

        $this->info($existing
            ? "Usuario {$email} actualizado."
            : "Administrador {$email} creado.");

        return self::SUCCESS;
    }
}
