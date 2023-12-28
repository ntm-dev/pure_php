<?php

return [
    'required'             => "Trường :attribute là bắt buộc.",
    'max'                  => [
        'array'   => ':attribute phải nhỏ hơn hoặc bằng :max phần tử.',
        'file'    => ':attribute phải là một tệp nhỏ hơn :max kilobytes.',
        'numeric' => ':attribute phải là một số nhỏ hơn hoặc bằng :max.',
        'string'  => ':attribute phải nhỏ hơn hoặc bằng :max ký tự.',
    ],
    'min'                  => [
        'array'   => ':attribute phải lớn hơn hoặc bằng :min phần tử.',
        'file'    => ':attribute phải là một tệp lớn hơn :min kilobytes.',
        'numeric' => ':attribute phải là một số lớn hơn hoặc bằng :min.',
        'string'  => ':attribute phải lớn hơn hoặc bằng :min ký tự.',
    ],
    'max_length'           => [
        'array'   => ':attribute phải nhỏ hơn hoặc bằng :max_length phần tử.',
        'file'    => ':attribute phải là một tệp nhỏ hơn :max_length kilobytes.',
        'numeric' => ':attribute phải là một số nhỏ hơn hoặc bằng :max_length.',
        'string'  => ':attribute phải nhỏ hơn hoặc bằng :max_length ký tự.',
    ],
    'min_length'           => [
        'array'   => ':attribute phải lớn hơn hoặc bằng :min_length phần tử.',
        'file'    => ':attribute phải là một tệp lớn hơn :min_length kilobytes.',
        'numeric' => ':attribute phải là một số lớn hơn hoặc bằng :min_length.',
        'string'  => ':attribute phải lớn hơn hoặc bằng :min_length ký tự.',
    ],
    'image'                => ':attribute phải là một hình ảnh hợp lệ.',
    'mimes'                => 'Trường :attribute phải là một tệp có định dạng: :values.',
    'mimetypes'            => 'Trường :attribute phải là một tệp có định dạng: :values.',
    'in'                   => ':attribute đã chọn không hợp lệ.',
    'in_array'             => ':attribute không tồn tại trong :in_array.',
    'integer'              => 'Vui lòng chỉ định một số nguyên cho :attribute.',
    'required_if'          => 'Trường :attribute là bắt buộc khi :other là :value.',
    'required_if_not'      => 'Trường :attribute là bắt buộc khi :other không phải là :value.',
    'date'                 => 'Trường :attribute không phải là một ngày hợp lệ.',
    'date_before'          => 'Trường :attribute phải là ngày trước :date.',
    'date_after'           => 'Trường :attribute phải là ngày sau :date.',
    'before_or_equal'      => 'Trường :attribute phải bằng hoặc trước ngày :date.',
    'after_or_equal'       => 'Trường :attribute phải bằng hoặc sau ngày :date.',
];
