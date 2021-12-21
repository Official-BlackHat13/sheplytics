@section('menu')
    @php
        /**
         * key => [icon, title, route]
         */
        $menu = [
            'dashboard' => ['dashboard', 'Dashboard', 'dashboard']
        ];
    @endphp

    <div class="nav d-block text-truncate">
        @foreach ($menu as $key => $value)
            <li class="nav-item">
                <a class="nav-link d-flex px-4 @if (request()->segment(1) == $key && isset($value[3]) == false) active @endif" @if(isset($value[3])) data-toggle="collapse" href="#sub-menu-{{ $key }}" role="button" @if (array_key_exists(request()->segment(3), $value[3])) aria-expanded="true" @else aria-expanded="false" @endif aria-controls="collapse-{{ $key }}" @else href="{{ (Route::has($value[2]) ? route($value[2]) : $value[2]) }}" @endif>
                    <span class="sidebar-icon d-flex align-items-center">@include('icons.' . $value[0], ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')])</span>
                    <span class="flex-grow-1 text-truncate">{{ __($value[1]) }}</span>
                    @if (isset($value[3])) <span class="d-flex align-items-center ml-auto sidebar-expand">@include('icons.expand', ['class' => 'fill-current text-muted width-3 height-3'])</span> @endif
                </a>
            </li>

            @if (isset($value[3]))
                <div class="collapse sub-menu @if (request()->segment(2) == $key) show @endif" id="sub-menu-{{ $key }}">
                    @foreach ($value[3] as $subKey => $subValue)
                        <a href="{{ (Route::has($subValue[1]) ? route($subValue[1]) : $subValue[1]) }}" class="nav-link text-truncate @if (request()->segment(3) == $subKey) active @endif">{{ __($subValue[0]) }}</a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>

    <hr>

    @auth
    <div class="nav d-block text-truncate">
        @foreach($websites as $website)
            <li class="nav-item">
                <a class="nav-link d-flex px-4 @if(request()->route()->parameter('id') == $website->url) active @endif" href="{{ route('stats.overview', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">
                    <span class="sidebar-icon d-flex align-items-center"><img src="https://icons.duckduckgo.com/ip3/{{ $website->url }}.ico" rel="noreferrer" class="width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}"></span>
                    <span class="flex-grow-1 text-truncate" dir="ltr">{{ $website->url }}</span>
                </a>
            </li>
        @endforeach
    </div>
    @endauth
@endsection