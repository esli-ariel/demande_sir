<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

final class CreateAdministrator extends Command
{
    protected $signature = 'dma:create-admin';
    protected $description = 'Créer un administrateur avec un mot de passe saisi de manière confidentielle';

    public function handle(): int
    {
        $data = [
            'name'=>$this->ask('Nom'),
            'prenom'=>$this->ask('Prénom'),
            'email'=>$this->ask('Adresse e-mail'),
            'password'=>$this->secret('Mot de passe'),
            'password_confirmation'=>$this->secret('Confirmer le mot de passe'),
        ];
        $validator = Validator::make($data,[
            'name'=>'required|string|max:255',
            'prenom'=>'required|string|max:255',
            'email'=>'required|string|email|max:255|unique:users,email',
            'password'=>['required','string','max:4096','not_regex:/\x00/','confirmed',Password::min(12)->mixedCase()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) $this->error($message);
            return self::FAILURE;
        }
        DB::transaction(function () use ($validator) {
            $data = $validator->validated();
            unset($data['password_confirmation']);
            $user = User::create($data);
            $user->assignRole(Role::firstOrCreate(['name'=>'admin','guard_name'=>'web']));
        });
        $this->info('Administrateur créé. Aucun mot de passe affiché ni enregistré dans le journal.');
        return self::SUCCESS;
    }
}
