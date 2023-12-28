<?php

return [
    'required'             => "The :attribute field is required.",
    'max'                  => [
        'array'   => 'The :attribute may not have more than :max items.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'numeric' => 'The :attribute may not be greater than :max.',
        'string'  => 'The :attribute may not be greater than :max characters.',
    ],
    'min'                  => [
        'array'   => 'The :attribute must have at least :min items.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'numeric' => 'The :attribute must be at least :min.',
        'string'  => 'The :attribute must be at least :min characters.',
    ],
    'max_length'           => [
        'array'   => 'The :attribute may not have more than :max_length items.',
        'file'    => 'The :attribute may not be greater than :max_length kilobytes.',
        'numeric' => 'The :attribute may not be greater than :max_length.',
        'string'  => 'The :attribute may not be greater than :max_length characters.',
    ],
    'min_length'           => [
        'array'   => 'The :attribute must have at least :min_length items.',
        'file'    => 'The :attribute must be at least :min_length kilobytes.',
        'numeric' => 'The :attribute must be at least :min_length.',
        'string'  => 'The :attribute must be at least :min_length characters.',
    ],
    'image'                => 'The :attribute must be an image.',
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :in_array.',
    'integer'              => 'The :attribute must be an integer.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_if_not'      => 'The :attribute field is required when :other is not:value.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_before'          => 'The :attribute must be a date before :date.',
    'date_after'           => 'The :attribute must be a date after :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
];
