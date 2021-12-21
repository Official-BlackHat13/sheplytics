@extends('layouts.app')

@section('site_title', formatTitle([__('Dashboard'), config('settings.title')]))

@section('content')
<div class="bg-base-1 flex-fill">
    @include('dashboard.header')

    <div class="bg-base-1">
        <div class="container py-3 my-3">
            <div class="row">
                <div class="col-12 col-lg">
                    <h4 class="mb-0">{{ __('Overview') }}</h4>
                </div>
                <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                    <ul class="nav nav-pills small">
                        <li class="nav-item">
                            <a href="{{ route('dashboard', ['from' => \Carbon\Carbon::now()->format('Y-m-d'), 'to' => \Carbon\Carbon::now()->format('Y-m-d')]) }}" class="nav-link py-1 px-2 @if(\Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->isToday()) active @endif" href="#">{{ __('Today') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard', ['from' => \Carbon\Carbon::now()->subDays(6)->format('Y-m-d'), 'to' => \Carbon\Carbon::now()->format('Y-m-d')]) }}" class="nav-link py-1 px-2 @if(\Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->format('Y-m-d') == \Carbon\Carbon::now()->subDays(6)->format('Y-m-d') && \Carbon\Carbon::createFromFormat('Y-m-d', $range['to'])->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d')) active @endif" href="#">{{ __('Last :days days', ['days' => 7]) }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard', ['from' => \Carbon\Carbon::now()->subDays(29)->format('Y-m-d'), 'to' => \Carbon\Carbon::now()->format('Y-m-d')]) }}" class="nav-link py-1 px-2 @if(\Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->format('Y-m-d') == \Carbon\Carbon::now()->subDays(29)->format('Y-m-d') && \Carbon\Carbon::createFromFormat('Y-m-d', $range['to'])->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d')) active @endif" href="#">{{ __('Last :days days', ['days' => 30]) }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard', ['from' => Auth::user()->created_at->format('Y-m-d'), 'to' => \Carbon\Carbon::now()->format('Y-m-d')]) }}" class="nav-link py-1 px-2 @if(\Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->format('Y-m-d') == Auth::user()->created_at->format('Y-m-d') && \Carbon\Carbon::createFromFormat('Y-m-d', $range['to'])->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d')) active @endif" href="#">{{ __('Total') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 rounded-top shadow-sm my-3 overflow-hidden">
                <div class="px-3">
                    <div class="row">
                        <!-- Title -->
                        <div class="col-12 col-md-auto d-none d-md-flex  align-items-center border-bottom border-md-bottom-0 {{ (__('lang_dir') == 'rtl' ? 'border-md-left' : 'border-md-right') }}">
                            <div class="px-2 py-4 d-flex">
                                <div class="d-flex position-relative text-primary width-10 height-10 align-items-center justify-content-center flex-shrink-0">
                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-35"></div>
                                    @include('icons.overview', ['class' => 'fill-current width-5 height-5'])
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md">
                            <div class="row">
                                <!-- Visitors -->
                                <div class="col-12 col-md-6 border-bottom border-md-bottom-0 {{ (__('lang_dir') == 'rtl' ? 'border-md-left' : 'border-md-right') }}">
                                    <div class="px-2 py-4">
                                        <div class="d-flex">
                                            <div class="text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                <div class="d-flex align-items-center text-truncate">
                                                    <div class="d-flex align-items-center justify-content-center bg-visitor border-radius-25 width-4 height-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"></div>

                                                    <div class="flex-grow-1 d-flex font-weight-bold text-truncate">
                                                        <div class="text-truncate">{{ __('Visitors') }}</div>
                                                        <div class="flex-shrink-0 d-flex align-items-center mx-2" data-enable="tooltip" title="{{ __('A visitor represents a page load of your website through direct access, or through a referrer.') }}">
                                                            @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                                        </div>
                                                    </div>
                                                </div>

                                                @include('stats.growth', ['growthCurrent' => $visitors, 'growthPrevious' => $visitorsOld])
                                            </div>

                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                                                <div class="h2 font-weight-bold mb-0">{{ number_format($visitors, 0, __('.'), __(',')) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pageviews -->
                                <div class="col-12 col-md-6">
                                    <div class="px-2 py-4">
                                        <div class="d-flex">
                                            <div class="text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                <div class="d-flex align-items-center text-truncate">
                                                    <div class="d-flex align-items-center justify-content-center bg-pageview border-radius-25 width-4 height-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"></div>

                                                    <div class="flex-grow-1 d-flex font-weight-bold text-truncate">
                                                        <div class="text-truncate">{{ __('Pageviews') }}</div>
                                                        <div class="flex-shrink-0 d-flex align-items-center mx-2" data-enable="tooltip" title="{{ __('A pageview represents a page load of your website.') }}">
                                                            @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                                        </div>
                                                    </div>
                                                </div>

                                                @include('stats.growth', ['growthCurrent' => $pageviews, 'growthPrevious' => $pageviewsOld])
                                            </div>

                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                                                <div class="h2 font-weight-bold mb-0">{{ number_format($pageviews, 0, __('.'), __(',')) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mt-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <div class="row">
                                <div class="col"><div class="font-weight-medium py-1">{{ __('Websites') }}</div></div>
                                <div class="col-auto">
                                    <form method="GET" action="{{ route('dashboard') }}">
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" name="search" placeholder="{{ __('Search') }}" value="{{ app('request')->input('search') }}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn {{ request()->input('sort') ? 'btn-primary' : 'btn-outline-primary' }} d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-enable="tooltip" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current width-4 height-4'])&#8203;</button>
                                                <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow width-64" id="search-filters">
                                                    <div class="dropdown-header py-1">
                                                        <div class="row">
                                                            <div class="col"><div class="font-weight-medium m-0 text-dark text-truncate">{{ __('Filters') }}</div></div>
                                                            <div class="col-auto">
                                                                @if(request()->input('sort'))
                                                                    <a href="{{ route('dashboard') }}" class="text-secondary">{{ __('Reset') }}</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="dropdown-divider"></div>

                                                    <div class="form-group px-4">
                                                        <label for="i-sort" class="small">{{ __('Sort') }}</label>
                                                        <select name="sort" id="i-sort" class="custom-select custom-select-sm">
                                                            @foreach(['asc' => __('A-Z'), 'desc' => __('Z-A')] as $key => $value)
                                                                <option value="{{ $key }}" @if(request()->input('sort') == $key) selected @endif>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group px-4 mb-2">
                                                        <button type="submit" class="btn btn-primary btn-sm btn-block">{{ __('Search') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            @if(count($websites) == 0)
                                {{ __('No data') }}.
                            @else
                                <div class="list-group list-group-flush my-n3">
                                    <div class="list-group-item px-0 text-muted">
                                        <div class="row d-flex align-items-center">
                                            <div class="col">
                                                <div class="row align-items-center">
                                                    <div class="col-12 col-lg-4 text-truncate">
                                                        {{ __('Name') }}
                                                    </div>

                                                    <div class="col-12 col-lg-4 text-truncate">
                                                        {{ __('Visitors') }}
                                                    </div>

                                                    <div class="col-12 col-lg-4 text-truncate">
                                                        {{ __('Pageviews') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="form-row">
                                                    <div class="col">
                                                        <div class="invisible btn d-flex align-items-center btn-sm text-primary">@include('icons.horizontal-menu', ['class' => 'fill-current width-4 height-4'])&#8203;</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach($websites as $website)
                                        <div class="list-group-item px-0">
                                            <div class="row d-flex align-items-center">
                                                <div class="col text-truncate">
                                                    <div class="row text-truncate">
                                                        <div class="col-12 col-lg-4 d-flex align-items-center text-truncate">
                                                            <img src="https://icons.duckduckgo.com/ip3/{{ $website->url }}.ico" rel="noreferrer" class="width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}"> <div class="text-truncate" dir="ltr"><a href="{{ route('stats.overview', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ $website->url }}</a></div>
                                                        </div>

                                                        <div class="col-12 col-lg-4 d-flex align-items-center font-weight-medium">
                                                            <div class="d-flex align-items-center text-truncate">
                                                                <div class="d-flex align-items-center justify-content-center bg-visitor border-radius-25 width-4 height-4 flex-shrink-0 text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"></div>
                                                                <div class="text-truncate">{{ number_format($website->visitors->sum('count') ?? 0, 0, __('.'), __(',')) }}</div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-lg-4 d-flex align-items-center font-weight-medium">
                                                            <div class="d-flex align-items-center text-truncate">
                                                                <div class="d-flex align-items-center justify-content-center bg-pageview border-radius-25 width-4 height-4 flex-shrink-0 text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"></div>
                                                                <div class="text-truncate">{{ number_format($website->pageviews->sum('count') ?? 0, 0, __('.'), __(',')) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="form-row">
                                                        <div class="col">
                                                            @include('websites.partials.menu')
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-3 align-items-center">
                                        <div class="row">
                                            <div class="col">
                                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $websites->firstItem(), 'to' => $websites->lastItem(), 'total' => $websites->total()]) }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                {{ $websites->onEachSide(1)->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.sidebars.user')
