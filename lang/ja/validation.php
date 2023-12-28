<?php

return [
    'required'             => ":attributeは必須項目です。",
    'max'                  => [
        'array'   => ':attributeの項目数は、:max個以下でなければいけません。',
        'file'    => ':attributeは、:max KB以下のファイルでなければいけません。',
        'numeric' => ':attributeは、:max以下の数字でなければいけません。',
        'string'  => ':attributeの文字数は、:max文字以下でなければいけません。',
    ],
    'min'                  => [
        'array'   => ':Attributeの項目数は、:min個以上にしてください。',
        'file'    => ':Attributeには、:min KB以上のファイルを指定してください。',
        'numeric' => ':Attributeには、:min以上の数字を指定してください。',
        'string'  => ':Attributeの文字数は、:min文字以上でなければいけません。',
    ],
    'max_length'           => [
        'array'   => ':attributeの項目数は、:max_length個以下でなければいけません。',
        'file'    => ':attributeは、:max_length KB以下のファイルでなければいけません。',
        'numeric' => ':attributeは、:max_length以下の数字でなければいけません。',
        'string'  => ':attributeの文字数は、:max_length文字以下でなければいけません。',
    ],
    'min_length'           => [
        'array'   => ':Attributeの項目数は、:min_length個以上にしてください。',
        'file'    => ':Attributeには、:min_length KB以上のファイルを指定してください。',
        'numeric' => ':Attributeには、:min_length以上の数字を指定してください。',
        'string'  => ':Attributeの文字数は、:min_length文字以上でなければいけません。',
    ],
    'image'                => ':Attributeには、画像を指定してください。',
    'mimes'                => ':Attributeには、以下のファイルタイプを指定してください。:values',
    'mimetypes'            => ':Attributeには、以下のファイルタイプを指定してください。:values',
    'min_digits'           => ':Attributeは、:min桁以上の数字でなければいけません。',
    'in'                   => '選択された:attributeは、有効ではありません。',
    'in_array'             => ':Attributeが:in_arrayに存在しません。',
    'integer'              => ':Attributeには、整数を指定してください。',
    'required_if'          => ':Otherが:valueの場合、:attributeを指定してください。',
    'required_if_not'      => ':Otherが:valueではない場合、:attributeが必須になります。',
    'date'                 => ':Attributeは、正しい日付ではありません。',
    'date_before'          => ':Attributeには、:dateより前の日付を指定してください。',
    'date_after'           => ':Attributeには、:dateより後の日付を指定してください。',
    'before_or_equal'      => ':Attributeには、:date以前の日付を指定してください。',
    'after_or_equal'       => ':Attributeには、:date以降の日付を指定してください。',
];
