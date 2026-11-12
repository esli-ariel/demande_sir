<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UserAccountStatus
{
    public function deactivate(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            $locked = User::lockForUpdate()->findOrFail($user->id);
            if ($locked->is($actor) && $locked->hasRole('admin')) {
                throw ValidationException::withMessages(['account' => 'Votre compte administrateur doit être désactivé par un autre administrateur.']);
            }
            $locked->forceFill(['deactivated_at' => $locked->deactivated_at ?? now(), 'remember_token' => null])->save();
            if (config('session.driver') === 'database') {
                DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->where('user_id', $locked->id)->delete();
            }
            $broker = config('auth.defaults.passwords', 'users');
            $settings = config('auth.passwords.'.$broker);
            if (($settings['driver'] ?? 'database') === 'database') {
                DB::connection($settings['connection'] ?? null)->table($settings['table'])->where('email', $locked->email)->delete();
            }
        });
    }

    public function reactivate(User $user): void
    {
        DB::transaction(function () use ($user) {
            User::lockForUpdate()->findOrFail($user->id)->forceFill(['deactivated_at' => null, 'remember_token' => null])->save();
        });
    }
}
