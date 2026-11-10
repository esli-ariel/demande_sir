<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('structure_specialisee_user', function (Blueprint $table) {
            $table->foreignId('structure_id')->constrained('structures_specialisees')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['structure_id', 'user_id']);
        });
        Schema::table('demandes_validations', function (Blueprint $table) {
            $table->foreignId('structure_id')->nullable()->constrained('structures_specialisees')->restrictOnDelete();
            $table->unique(['demande_id', 'structure_id'], 'demandes_validations_demande_structure_unique');
        });
        // The legacy seeder deliberately used the same id and name for account and structure.
        // Only exact existing pairs are recovered; no fuzzy name matching grants access.
        $pairs = DB::table('users')->join('model_has_roles', function ($join) {
            $join->on('model_has_roles.model_id', '=', 'users.id')->where('model_has_roles.model_type', \App\Models\User::class);
        })->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->join('structures_specialisees', function ($join) {
                $join->on('structures_specialisees.id', '=', 'users.id')->on('structures_specialisees.nom', '=', 'users.name');
            })->where('roles.name', 'structure_specialisee')->where('roles.guard_name', 'web')
            ->select('users.id as user_id', 'structures_specialisees.id as structure_id')->distinct()->get();
        foreach ($pairs as $pair) {
            DB::table('structure_specialisee_user')->insertOrIgnore(['user_id'=>$pair->user_id,'structure_id'=>$pair->structure_id,'created_at'=>now(),'updated_at'=>now()]);
        }
        foreach (DB::table('demandes_validations')->where('role', 'structure_specialisee')->orderBy('id')->get() as $decision) {
            $ids = DB::table('demande_structure_specialisee')->join('structure_specialisee_user', 'structure_specialisee_user.structure_id', '=', 'demande_structure_specialisee.structure_id')
                ->where('demande_structure_specialisee.demande_id', $decision->demande_id)->where('structure_specialisee_user.user_id', $decision->user_id)
                ->pluck('demande_structure_specialisee.structure_id')->unique();
            if ($ids->count() === 1 && ! DB::table('demandes_validations')->where('demande_id',$decision->demande_id)->where('structure_id',$ids->first())->exists()) {
                DB::table('demandes_validations')->where('id',$decision->id)->update(['structure_id'=>$ids->first()]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('demandes_validations', function (Blueprint $table) {
            $table->dropUnique('demandes_validations_demande_structure_unique');
            $table->dropConstrainedForeignId('structure_id');
        });
        Schema::dropIfExists('structure_specialisee_user');
    }
};
