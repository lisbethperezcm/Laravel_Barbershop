<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

  'accepted' => 'El campo :attribute debe ser aceptado.',
'accepted_if' => 'El campo :attribute debe ser aceptado cuando :other es :value.',
'active_url' => 'El campo :attribute debe ser una URL válida.',
'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
'alpha' => 'El campo :attribute solo debe contener letras.',
'alpha_dash' => 'El campo :attribute solo debe contener letras, números, guiones y guiones bajos.',
'alpha_num' => 'El campo :attribute solo debe contener letras y números.',
'array' => 'El campo :attribute debe ser un arreglo.',
'ascii' => 'El campo :attribute solo debe contener caracteres alfanuméricos de un solo byte y símbolos.',
'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
'between' => [
    'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
    'file' => 'El campo :attribute debe tener entre :min y :max kilobytes.',
    'numeric' => 'El campo :attribute debe estar entre :min y :max.',
    'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
],

    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
'can' => 'El campo :attribute contiene un valor no autorizado.',
'confirmed' => 'La confirmación del campo :attribute no coincide.',
'current_password' => 'La contraseña es incorrecta.',
'date' => 'El campo :attribute debe ser una fecha válida.',
'date_equals' => 'El campo :attribute debe ser una fecha igual a :date.',
'date_format' => 'El campo :attribute debe coincidir con el formato :format.',
'decimal' => 'El campo :attribute debe tener :decimal lugares decimales.',
'declined' => 'El campo :attribute debe ser rechazado.',
'declined_if' => 'El campo :attribute debe ser rechazado cuando :other es :value.',
'different' => 'El campo :attribute y :other deben ser diferentes.',
'digits' => 'El campo :attribute debe tener :digits dígitos.',
'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
'dimensions' => 'El campo :attribute tiene dimensiones de imagen no válidas.',
'distinct' => 'El campo :attribute tiene un valor duplicado.',
'doesnt_end_with' => 'El campo :attribute no debe terminar con uno de los siguientes valores: :values.',
'doesnt_start_with' => 'El campo :attribute no debe comenzar con uno de los siguientes valores: :values.',
'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
'ends_with' => 'El campo :attribute debe terminar con uno de los siguientes valores: :values.',
'enum' => 'El :attribute seleccionado no es válido.',
'exists' => 'El :attribute seleccionado no es válido.',

    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
    'array' => 'El campo :attribute debe tener más de :value elementos.',
    'file' => 'El campo :attribute debe ser mayor que :value kilobytes.',
    'numeric' => 'El campo :attribute debe ser mayor que :value.',
    'string' => 'El campo :attribute debe tener más de :value caracteres.',
],
'gte' => [
    'array' => 'El campo :attribute debe tener :value elementos o más.',
    'file' => 'El campo :attribute debe ser mayor o igual a :value kilobytes.',
    'numeric' => 'El campo :attribute debe ser mayor o igual a :value.',
    'string' => 'El campo :attribute debe tener al menos :value caracteres.',
],
'hex_color' => 'El campo :attribute debe ser un color hexadecimal válido.',
'image' => 'El campo :attribute debe ser una imagen.',
'in' => 'El :attribute seleccionado no es válido.',
'in_array' => 'El campo :attribute debe existir en :other.',
'integer' => 'El campo :attribute debe ser un número entero.',
'ip' => 'El campo :attribute debe ser una dirección IP válida.',
'ipv4' => 'El campo :attribute debe ser una dirección IPv4 válida.',
'ipv6' => 'El campo :attribute debe ser una dirección IPv6 válida.',
'json' => 'El campo :attribute debe ser una cadena JSON válida.',
'lowercase' => 'El campo :attribute debe estar en minúsculas.',
'lt' => [
    'array' => 'El campo :attribute debe tener menos de :value elementos.',
    'file' => 'El campo :attribute debe ser menor que :value kilobytes.',
    'numeric' => 'El campo :attribute debe ser menor que :value.',
    'string' => 'El campo :attribute debe tener menos de :value caracteres.',
],

    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'El campo :attribute debe estar presente.',
'present_if' => 'El campo :attribute debe estar presente cuando :other sea :value.',
'present_unless' => 'El campo :attribute debe estar presente a menos que :other sea :value.',
'present_with' => 'El campo :attribute debe estar presente cuando :values esté presente.',
'present_with_all' => 'El campo :attribute debe estar presente cuando :values estén presentes.',
'prohibited' => 'El campo :attribute está prohibido.',
'prohibited_if' => 'El campo :attribute está prohibido cuando :other sea :value.',
'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
'prohibits' => 'El campo :attribute prohíbe que :other esté presente.',
'regex' => 'El formato del campo :attribute no es válido.',
'required' => 'El campo :attribute es obligatorio.',
'required_array_keys' => 'El campo :attribute debe contener entradas para: :values.',
'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
'required_if_accepted' => 'El campo :attribute es obligatorio cuando :other es aceptado.',
'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
'required_with_all' => 'El campo :attribute es obligatorio cuando :values están presentes.',
'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
'same' => 'El campo :attribute debe coincidir con :other.',
'size' => [
    'array' => 'El campo :attribute debe contener :size elementos.',
    'file' => 'El campo :attribute debe tener :size kilobytes.',
    'numeric' => 'El campo :attribute debe ser :size.',
    'string' => 'El campo :attribute debe tener :size caracteres.',
],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'The :attribute field must be a string.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute field must be a valid URL.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
