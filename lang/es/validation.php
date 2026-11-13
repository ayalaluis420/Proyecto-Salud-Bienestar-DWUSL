<?php

/*
| Mensajes de validación en español (los que no estén aquí caen al inglés del framework).
| Los nombres amigables de los campos se definen en attributes() de cada Form Request.
*/

return [
    'accepted'   => 'Debes aceptar :attribute.',
    'array'      => 'El campo :attribute debe ser una lista.',
    'between'    => [
        'array'   => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file'    => 'El archivo :attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string'  => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean'    => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'  => 'La confirmación de :attribute no coincide.',
    'email'      => 'El campo :attribute debe ser un correo electrónico válido.',
    'in'         => 'El valor seleccionado en :attribute no es válido.',
    'integer'    => 'El campo :attribute debe ser un número entero.',
    'max'        => [
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
        'file'    => 'El archivo :attribute no debe pesar más de :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string'  => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min'        => [
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
        'file'    => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric'    => 'El campo :attribute debe ser un número.',
    'required'   => 'El campo :attribute es obligatorio.',
    'string'     => 'El campo :attribute debe ser texto.',
    'unique'     => 'Ya existe un registro con ese :attribute.',

    'password' => [
        'letters'       => 'La :attribute debe contener al menos una letra.',
        'mixed'         => 'La :attribute debe contener al menos una mayúscula y una minúscula.',
        'numbers'       => 'La :attribute debe contener al menos un número.',
        'symbols'       => 'La :attribute debe contener al menos un símbolo.',
        'uncompromised' => 'Esa :attribute apareció en una filtración de datos. Elige otra distinta.',
    ],

    'custom' => [],

    'attributes' => [
        'name'                  => 'nombre',
        'email'                 => 'correo electrónico',
        'password'              => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
    ],
];
