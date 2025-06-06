<?php
// app/Providers/EncryptedUserProvider.php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\Helpers\EncryptHelper;

class EncryptedUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return;
        }
        $query = $this->createModel()->newQuery();
        foreach ($credentials as $key => $value) {
            if ($key === 'password') {
                continue;
            }
            if ($key === 'email') {
                $value = EncryptHelper::encrypt($value);
            }
            $query->where($key, $value);
        }
        return $query->first();
    }
}
