<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
       
        // Public → seulement "demandeur"
        $roles = Role::where('name', 'demandeur')->get();
    
        return view('auth.register',compact('roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
             'role' => [
            'required',
            function ($attribute, $value, $fail) {
                if (!Auth::check() || !Auth::user()->hasRole('admin')) {
                    if ($value !== 'demandeur') {
                        $fail('Rôle non autorisé.');
                    }
                }
            }
        ]
        ]);

        $user = User::create([
            'name' => $request->name,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole($request->role);

        event(new Registered($user));

        Auth::login($user);

       return redirect()->route('dashboard.demandeur')
        ->with('success', 'Inscription réussie ! Bienvenue.');

        // Après la création de l'utilisateur (role user)
        //$user->roles()->attach(1);
        // Tous les inscrits deviennent "demandeur" par défau
    }
}
