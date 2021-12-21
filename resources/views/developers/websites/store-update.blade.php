@php
    if($type) {
        $parameters[] = [
            'name' => 'url',
            'type' => $type,
            'format' => 'string',
            'description' => __('The website\'s URL.')
        ];
    }

    $parameters[] = [
        'name' => 'privacy',
        'type' => 0,
        'format' => 'integer',
        'description' => __('Stats page privacy.') . ' ' . __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="font-weight-medium">'.__('Public').'</span>']),
                    __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="font-weight-medium">'.__('Private').'</span>']),
                    __(':value for :name', ['value' => '<code>2</code>', 'name' => '<span class="font-weight-medium">'.__('Password').'</span>'])
                    ])
                ]) . ($type ? ' ' . __('Defaults to: :value.', ['value' => '<code>1</code>']) : '')
    ];

    $parameters[] = [
        'name' => 'password',
        'type' => 0,
        'format' => 'string',
        'description' => __('The password for the statistics page.') . ' ' . __('Only works with :field set to :value.', ['field' => '<code>privacy</code>', 'value' => '<code>2</code>'])
    ];

    $parameters[] = [
        'name' => 'email',
        'type' => 0,
        'format' => 'integer',
        'description' => __('Periodic email reports.') . ' ' . __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="font-weight-medium">'.__('Disabled').'</span>']),
                    __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="font-weight-medium">'.__('Enabled').'</span>'])
                    ])
                ]) . ($type ? ' ' . __('Defaults to: :value.', ['value' => '<code>0</code>']) : '')
    ];

    $parameters[] = [
            'name' => 'exclude_bots',
            'type' => 0,
            'format' => 'integer',
            'description' => __('Exclude common bots from being tracked.') . ' ' . __('Possible values are: :values.', [
                    'values' => implode(', ', [
                        __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="font-weight-medium">'.__('Disabled').'</span>']),
                        __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="font-weight-medium">'.__('Enabled').'</span>'])
                        ])
                    ]) . ($type ? ' ' . __('Defaults to: :value.', ['value' => '<code>1</code>']) : '')
        ];

    $parameters[] = [
            'name' => 'exclude_params',
            'type' => 0,
            'format' => 'string',
            'description' => __('Exclude URL query parameters from being tracked.') . ' ' . __('One per line.')
        ];

    $parameters[] = [
            'name' => 'exclude_ips',
            'type' => 0,
            'format' => 'string',
            'description' => __('Exclude IPs from being tracked.') . ' ' . __('One per line.')
        ];
@endphp

@include('developers.parameters')