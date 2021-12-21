@if(request()->is('admin/*') || request()->is('dashboard') || request()->is('websites/*'))
    <a href="#" class="btn d-flex align-items-center btn-sm text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.horizontal-menu', ['class' => 'fill-current width-4 height-4'])&#8203;</a>
@endif

<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    @if(request()->is('admin/*') || Auth::check() && Auth::user()->role == 1 || Auth::check() && $website->user_id == Auth::user()->id)
        <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') || (Auth::user()->role == 1 && $website->user_id != Auth::user()->id) ? route('admin.websites.edit', $website->id) : route('websites.edit', $website->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Edit') }}</a>
    @endif

    <a class="dropdown-item d-flex align-items-center" href="{{ route('stats.overview', ['id' => $website->url, 'from' => $range['from'] ?? null, 'to' => $range['to'] ?? null]) }}">@include('icons.eye', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('View') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ 'http://' . $website->url }}" target="_blank" rel="nofollow noreferrer noopener">@include('icons.open-new', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Open') }}</a>

    @if(request()->is('admin/*') || Auth::check() && Auth::user()->role == 1 || Auth::check() && $website->user_id == Auth::user()->id)
        <div class="dropdown-divider"></div>

        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') || (Auth::user()->role == 1 && $website->user_id != Auth::user()->id) ? route('admin.websites.destroy', $website->id) : route('websites.destroy', $website->id) }}" data-button="btn btn-danger" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :name?', ['name' => $website->url]) }}">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
    @endif
</div>