<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right') }} border-0 shadow">
    @if(isset($options['edit']))
        @if(Auth::check() && (Auth::user()->id == $website->user_id || Auth::user()->role == 1))
                <a class="dropdown-item d-flex align-items-center" href="{{ ((isset($admin) || ($website->user_id != Auth::user()->id && Auth::user()->role == 1)) ? route('admin.websites.edit', $website->id) : route('websites.edit', ['id' => $website->id])) }}">@include('icons.edit', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Edit') }}</a>
        @endif
    @endif

    @if(isset($options['stats']))
        <a class="dropdown-item d-flex align-items-center" href="{{ route('stats.overview', ['id' => $website->url, 'from' => $range['from'] ?? null, 'to' => $range['to'] ?? null]) }}">@include('icons.stats', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Stats') }}</a>
    @endif

    @if(isset($options['open']))
        <a class="dropdown-item d-flex align-items-center" href="{{ 'http://' . $website->url }}" target="_blank" rel="nofollow noreferrer noopener">@include('icons.open-new', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Open') }}</a>
    @endif

    @if(isset($options['delete']))
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#delete-website-modal" data-action="{{ isset($admin) ? route('admin.websites.destroy', $website->id) : route('websites.destroy', $website->id) }}" data-text="{{ __('Are you sure you want to delete :name?', ['name' => str_replace(['http://', 'https://'], '', $website->url)]) }}">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
    @endif
</div>