<div class="d-flex">
    <nav class="navbar navbar-expand-xl navbar-light w-100 p-0 mt-3 bg-base-0 rounded shadow-sm">
        <div class="d-flex align-items-center d-xl-none px-3 py-3 font-weight-medium text-muted">
            @php
                $menu = [
                    'stats.realtime' => ['Realtime', 'live'],
                    'stats.overview' => ['Overview', 'overview'],
                    'stats.pages' => ['Pages', 'page'],
                    'stats.landing_pages' => ['Landing pages', 'landing'],
                    'stats.referrers' => ['Referrers', 'link'],
                    'stats.search_engines' => ['Search engines', 'search'],
                    'stats.social_networks' => ['Social networks', 'social'],
                    'stats.campaigns' => ['Campaigns', 'campaign'],
                    'stats.continents' => ['Continents', 'continents'],
                    'stats.countries' => ['Countries', 'flag'],
                    'stats.cities' => ['Cities', 'city'],
                    'stats.operating_systems' => ['Operating systems', 'os'],
                    'stats.browsers' => ['Browsers', 'browser'],
                    'stats.screen_resolutions' => ['Screen resolutions', 'screen-resolution'],
                    'stats.devices' => ['Devices', 'devices'],
                    'stats.events' => ['Events', 'event'],
                ];
            @endphp

            @if(isset($menu[Route::currentRouteName()]))
                @include('icons.'.$menu[Route::currentRouteName()][1], ['class' => 'fill-current width-4 height-4 ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
                {{ __($menu[Route::currentRouteName()][0]) }}
            @endif
        </div>
        <button class="navbar-toggler border-0 py-2 collapsed {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}" type="button" data-toggle="collapse" data-target="#stats-navbar" aria-controls="stats-navbar" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon my-1"></span>
        </button>

        <div class="collapse navbar-collapse border-top border-xl-top-0" id="stats-navbar">
            <ul class="navbar-nav flex-wrap justify-content-between w-100">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3" href="{{ route('stats.realtime', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">
                        <span class="d-flex align-items-center position-relative width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"><div class="pulsating-circle position-absolute width-2 height-2"></div></span>
                        <span class="{{ Route::currentRouteName() == 'stats.realtime' ? 'text-success' : '' }}">{{ __('Realtime') }}</span>
                    </a>
                </li>

                <li class="{{ (__('lang_dir') == 'rtl' ? 'border-lg-left' : 'border-lg-right') }} "></li>

                <li class="nav-item {{ Route::currentRouteName() == 'stats.overview' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3" href="{{ route('stats.overview', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.overview', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])</span>
                        <span>{{ __('Overview') }}</span>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3 {{ in_array(Route::currentRouteName(), ['stats.pages', 'stats.landing_pages']) ? 'active' : '' }}" href="#" id="behaviorDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @include('icons.behavior', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
                        <div>{{ __('Behavior') }}</div>
                        @include('icons.expand', ['class' => 'fill-current width-3 height-3 '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])
                    </a>
                    <div class="dropdown-menu border-0 shadow {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu-right' : 'dropdown-menu') }}" aria-labelledby="behaviorDropdown">
                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.pages' ? 'active' : '' }}" href="{{ route('stats.pages', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Pages') }}</a>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.landing_pages' ? 'active' : '' }}" href="{{ route('stats.landing_pages', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Landing pages') }}</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3 {{ in_array(Route::currentRouteName(), ['stats.referrers', 'stats.campaigns', 'stats.search_engines', 'stats.social_networks']) ? 'active' : '' }}" href="#" id="acquisitionsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @include('icons.acquisition', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
                        <div>{{ __('Acquisitions') }}</div>
                        @include('icons.expand', ['class' => 'fill-current width-3 height-3 '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])
                    </a>
                    <div class="dropdown-menu border-0 shadow {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu-right' : 'dropdown-menu') }}" aria-labelledby="acquisitionsDropdown">
                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.referrers' ? 'active' : '' }}" href="{{ route('stats.referrers', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Referrers') }}</a>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.search_engines' ? 'active' : '' }}" href="{{ route('stats.search_engines', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Search engines') }}</a>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.social_networks' ? 'active' : '' }}" href="{{ route('stats.social_networks', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Social networks') }}</a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.campaigns' ? 'active' : '' }}" href="{{ route('stats.campaigns', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Campaigns') }}</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3 {{ in_array(Route::currentRouteName(), ['stats.continents', 'stats.countries', 'stats.cities', 'stats.languages', 'stats.cities']) ? 'active' : '' }}" href="#" id="geographicDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @include('icons.geographic', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
                        <div>{{ __('Geographic') }}</div>
                        @include('icons.expand', ['class' => 'fill-current width-3 height-3 '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])
                    </a>
                    <div class="dropdown-menu border-0 shadow {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu-right' : 'dropdown-menu') }}" aria-labelledby="geographicDropdown">
                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.continents' ? 'active' : '' }}" href="{{ route('stats.continents', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Continents') }}</a>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.countries' ? 'active' : '' }}" href="{{ route('stats.countries', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Countries') }}</a>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.cities' ? 'active' : '' }}" href="{{ route('stats.cities', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Cities') }}</a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.languages' ? 'active' : '' }}" href="{{ route('stats.languages', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Languages') }}</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3 {{ in_array(Route::currentRouteName(), ['stats.browsers', 'stats.operating_systems', 'stats.screen_resolutions', 'stats.devices']) ? 'active' : '' }}" href="#" id="geographicDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @include('icons.platforms', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
                        <div>{{ __('Technology') }}</div>
                        @include('icons.expand', ['class' => 'fill-current width-3 height-3 '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])
                    </a>
                    <div class="dropdown-menu border-0 shadow {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu-right' : 'dropdown-menu') }}" aria-labelledby="geographicDropdown">
                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.operating_systems' ? 'active' : '' }}" href="{{ route('stats.operating_systems', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Operating systems') }}</a>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.browsers' ? 'active' : '' }}" href="{{ route('stats.browsers', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Browsers') }}</a>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.screen_resolutions' ? 'active' : '' }}" href="{{ route('stats.screen_resolutions', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Screen resolutions') }}</a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item {{ Route::currentRouteName() == 'stats.devices' ? 'active' : '' }}" href="{{ route('stats.devices', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ __('Devices') }}</a>
                    </div>
                </li>

                <li class="nav-item {{ Route::currentRouteName() == 'stats.events' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3" href="{{ route('stats.events', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.event', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])</span>
                        <span>{{ __('Events') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>