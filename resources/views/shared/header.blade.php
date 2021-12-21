@guest
    <div id="header" class="header sticky-top shadow bg-base-0 z-1025">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light px-0 py-3">
                <a href="{{ route('home') }}" aria-label="{{ config('settings.title') }}" class="navbar-brand p-0">
                    <div class="logo">
                        <img src="{{ url('/') }}/uploads/brand/{{ config('settings.logo') }}">
                    </div>
                </a>
                <button class="navbar-toggler border-0 p-0" type="button" data-toggle="collapse" data-target="#header-navbar" aria-controls="header-navbar" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="header-navbar">
                    <ul class="navbar-nav pt-2 p-lg-0 {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                        @if(paymentProcessors())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('pricing') }}" role="button">{{ __('Pricing') }}</a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}" role="button">{{ __('Login') }}</a>
                        </li>

                        @if(config('settings.registration'))
                            <li class="nav-item d-flex align-items-center">
                                <a class="btn btn-outline-primary" href="{{ route('register') }}" role="button">{{ __('Register') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>
    </div>
@else
    <div id="header" class="header sticky-top shadow bg-base-0 z-1025 d-lg-none">
        <div class="container-fluid">
            <nav class="navbar navbar-light px-0 py-3">
                <a href="{{ route('dashboard') }}" aria-label="{{ config('settings.title') }}" class="navbar-brand p-0">
                    <div class="logo">
                        <img src="{{ url('/') }}/uploads/brand/{{ config('settings.logo') }}">
                    </div>
                </a>
                <button class="slide-menu-toggle navbar-toggler border-0 p-0" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </nav>
        </div>
    </div>

    <nav class="slide-menu shadow bg-base-0 ct navbar navbar-light p-0 d-flex flex-column z-1030" id="slide-menu">
        <div class="sidebar-section flex-grow-1 d-flex flex-column w-100">
            <div>
                <div class="{{ (__('lang_dir') == 'rtl' ? 'pr-4' : 'pl-4') }} py-3 d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" aria-label="{{ config('settings.title') }}" class="navbar-brand p-0">
                        <div class="logo">
                            <img src="{{ url('/') }}/uploads/brand/{{ config('settings.logo') }}">
                        </div>
                    </a>
                    <div class="close slide-menu-toggle cursor-pointer d-lg-none d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }} px-4 py-2">
                        @include('icons.close', ['class' => 'fill-current width-4 height-4'])
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="py-3 {{ (__('lang_dir') == 'rtl' ? 'pr-4 pl-0' : 'pl-4 pr-0') }} font-weight-medium text-muted text-uppercase flex-grow-1">{{ __('Menu') }}</div>

                @if(Auth::user()->role == 1)
                    @if (request()->segment(1) == 'admin')
                        <a class="px-4 py-2 text-decoration-none text-secondary" href="{{ route('dashboard') }}" data-enable="tooltip" title="{{ __('User') }}" role="button"><span class="d-flex align-items-center">@include('icons.user', ['class' => 'width-4 height-4 fill-current'])</span></a>
                    @else
                        <a class="px-4 py-2 text-decoration-none text-secondary" href="{{ route('admin.dashboard') }}" data-enable="tooltip" title="{{ __('Admin') }}" role="button"><span class="d-flex align-items-center">@include('icons.admin', ['class' => 'width-4 height-4 fill-current'])</span></a>
                    @endif
                @endif
            </div>

            <div class="sidebar-section flex-grow-1 overflow-auto sidebar">
                @yield('menu')
            </div>

            <div class="py-3 px-4">
                @if(Auth::user()->plan->features->pageviews >= 0)
                    @if($stats['pageviews'] >= Auth::user()->plan->features->pageviews)
                        @if(Auth::user()->can_track)
                            <div class="alert alert-warning mb-3" role="alert">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center small">
                                        {{ __('Your account will be limited.') }} {{ __('Upgrade your account to continue tracking your visitors.') }}
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('pricing') }}" class="btn btn-sm btn-block btn-warning">{{ __('Upgrade') }}</a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-danger mb-3" role="alert">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center small">
                                        {{ __('Your account has been limited.') }} {{ __('Upgrade your account to continue tracking your visitors.') }}
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('pricing') }}" class="btn btn-sm btn-block btn-danger">{{ __('Upgrade') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif

                <div class="row no-gutters">
                    <div class="col">
                        <div class="small text-muted">
                            {{ __(':number of :total pageviews used.', ['number' => shortenNumber($stats['pageviews']), 'total' => (Auth::user()->plan->features->pageviews < 0 ? '∞' : shortenNumber(Auth::user()->plan->features->pageviews))]) }}
                        </div>
                    </div>
                </div>

                <div class="progress w-100 my-2 chart-progress">
                    <div class="progress-bar bg-pageview rounded" role="progressbar" style="width: {{ (Auth::user()->plan->features->pageviews == 0 ? 100 : (($stats['pageviews'] / Auth::user()->plan->features->pageviews) * 100)) }}%"></div>
                </div>
            </div>

            <div class="sidebar sidebar-footer">
                <div class="py-3 {{ (__('lang_dir') == 'rtl' ? 'pr-4 pl-0' : 'pl-4 pr-0') }} d-flex align-items-center" aria-expanded="true">
                    <a href="{{ route('account') }}" class="d-flex align-items-center overflow-hidden text-secondary text-decoration-none flex-grow-1">
                        <img src="{{ gravatar(Auth::user()->email, 72) }}" class="flex-shrink-0 rounded-circle width-9 height-9 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">

                        <div class="d-flex flex-column text-truncate">
                            <div class="font-weight-medium text-dark text-truncate">
                                {{ Auth::user()->name }}
                            </div>

                            <div class="small font-weight-medium">
                                {{ __('Account') }}
                            </div>
                        </div>
                    </a>

                    <a class="py-2 px-4 d-flex flex-shrink-0 align-items-center text-secondary" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" data-enable="tooltip" title="{{ __('Logout') }}">@include('icons.logout', ['class' => 'fill-current width-4 height-4'])</a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </nav>
@endguest