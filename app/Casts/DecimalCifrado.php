<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Cast para campos numéricos sensibles (peso, altura).
 *
 * En la base de datos se guarda el texto cifrado con Crypt (AES-256-CBC + HMAC,
 * usando APP_KEY). En PHP el atributo se ve como float.
 *
 * @implements CastsAttributes<float|null, float|int|string|null>
 */
class DecimalCifrado implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) Crypt::decryptString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString((string) $value);
    }
}
