<?php

namespace App\Helpers;

class EncryptHelper
{
    private const IV_DELIMITER = '||';

    public static function encrypt(string $value): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', config('app.key'), OPENSSL_RAW_DATA, $iv);
        return bin2hex($iv) . self::IV_DELIMITER . base64_encode($encrypted);
    }

    public static function decrypt(?string $value): ?string
    {
        if (!$value) return null;
        $parts = explode(self::IV_DELIMITER, $value, 2);
        if (count($parts) !== 2) return null;
        $iv = hex2bin($parts[0]);
        $encrypted = base64_decode($parts[1]);
        return openssl_decrypt($encrypted, 'AES-256-CBC', config('app.key'), OPENSSL_RAW_DATA, $iv);
    }
}
