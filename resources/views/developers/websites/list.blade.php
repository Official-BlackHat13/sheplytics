@php
    $parameters = [
        [
            'name' => 'search',
            'type' => 0,
            'format' => 'string',
            'description' => __('The search query.')
        ], [
            'name' => 'sort',
            'type' => 0,
            'format' => 'string',
            'description' => __('Sort') . '. ' . __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':value for :name', ['value' => '<code>desc</code>', 'name' => '<span class="font-weight-medium">'.__('Z-A').'</span>']),
                    __(':value for :name', ['value' => '<code>asc</code>', 'name' => '<span class="font-weight-medium">'.__('A-Z').'</span>'])
                    ])
                ])
        ], [
            'name' => 'per_page',
            'type' => 0,
            'format' => 'int',
            'description' => __('Results per page') . '. '. __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':from to :to', ['from' => '<code>10</code>', 'to' => '<code>100</code>'])
                    ])
                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>'.config('settings.paginate').'</code>'])
        ]
    ];
@endphp

@include('developers.parameters')