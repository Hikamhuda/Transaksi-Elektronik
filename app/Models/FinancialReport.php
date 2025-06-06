<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FinancialReport extends Model
{
    /**
     * Encrypt a given string.
     */
    private static function encryptValue(string $value): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', config('app.key'), OPENSSL_RAW_DATA, $iv);
        return bin2hex($iv) . '||' . base64_encode($encrypted);
    }

    /**
     * Decrypt a given stored value.
     */
    private static function decryptValue(?string $value): ?string
    {
        if (!$value) return null;
        $parts = explode('||', $value, 2);
        if (count($parts) !== 2) return null;
        $iv = hex2bin($parts[0]);
        $encrypted = base64_decode($parts[1]);
        return openssl_decrypt($encrypted, 'AES-256-CBC', config('app.key'), OPENSSL_RAW_DATA, $iv);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::decryptValue($value),
            set: fn ($value) => self::encryptValue($value)
        );
    }
}
