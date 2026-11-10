<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'role' => ['nullable', Rule::exists('roles', 'name')->where('guard_name', 'web')]]);
        $search = trim($filters['q'] ?? '');
        $selectedRole = $filters['role'] ?? '';
        $users = User::with('roles')
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('prenom', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->when($selectedRole !== '', fn ($query) => $query->role($selectedRole))
            ->orderBy('name')->orderBy('id')->paginate(10)->withQueryString();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        $totalUsers = User::count();

        return view('admin.index', compact('users', 'roles', 'search', 'selectedRole', 'totalUsers'));
    }

    public function create()
    {
        return view('admin.create', ['roles' => Role::where('guard_name', 'web')->orderBy('name')->get(), 'structures' => \App\Models\StructureSpecialisee::orderBy('nom')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        DB::transaction(function () use ($validated) {
            $user = User::create(collect($validated)->only(['name', 'prenom', 'email', 'password'])->all());
            $user->assignRole($validated['role']);
            if ($validated['role'] === 'structure_specialisee') $user->structuresSpecialisees()->sync($validated['structure_ids']);
        });

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        return view('admin.edit', ['user' => $user, 'roles' => Role::where('guard_name', 'web')->orderBy('name')->get(), 'structures' => \App\Models\StructureSpecialisee::orderBy('nom')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate($this->rules($user));
        if ($request->user()->is($user) && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Conservez votre rôle administrateur pour garder accès à cet espace.'])->withInput();
        }
        DB::transaction(function () use ($validated, $user) {
            $user->update(collect($validated)->only(['name', 'prenom', 'email'])->all());
            $user->syncRoles([$validated['role']]);
            $user->structuresSpecialisees()->sync($validated['role'] === 'structure_specialisee' ? $validated['structure_ids'] : []);
        });

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte depuis cette liste.');
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }

    private function rules(?User $user = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'structure_ids' => ['required_if:role,structure_specialisee', 'array', 'min:1'],
            'structure_ids.*' => ['integer', 'distinct', Rule::exists('structures_specialisees', 'id')],
        ];
        if (! $user) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }
}
