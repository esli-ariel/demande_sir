<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

final class ActiveUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null)
    {
        return parent::newModelQuery($model)->whereNull('deactivated_at');
    }
}
