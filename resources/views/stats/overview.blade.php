@section('site_title', formatTitle([$website->url, __('Overview'), config('settings.title')]))

<div class="card border-0 rounded-top shadow-sm mb-3 overflow-hidden">
    <div class="px-3 border-bottom">
        <div class="row">
            <!-- Title -->
            <div class="col-12 col-md-auto d-none d-xl-flex align-items-center border-bottom border-md-bottom-0 {{ (__('lang_dir') == 'rtl' ? 'border-md-left' : 'border-md-right') }}">
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
                    <div class="col-12 col-md-6 border-bottom border-md-bottom-0 {{ (__('lang_dir') == 'rtl' ? 'border-md-left' : 'border-md-right')  }}">
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

                                    @include('stats.growth', ['growthCurrent' => $totalVisitors, 'growthPrevious' => $totalVisitorsOld])
                                </div>

                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                                    <div class="h2 font-weight-bold mb-0">{{ number_format($totalVisitors, 0, __('.'), __(',')) }}</div>
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

                                    @include('stats.growth', ['growthCurrent' => $totalPageviews, 'growthPrevious' => $totalPageviewsOld])
                                </div>

                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                                    <div class="h2 font-weight-bold mb-0">{{ number_format($totalPageviews, 0, __('.'), __(',')) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div style="height: 230px">
            <canvas id="trend-chart"></canvas>
        </div>
        <script>
            'use strict';

            document.addEventListener("DOMContentLoaded", function() {
                Chart.defaults.global.defaultFontFamily = "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'";

                const ctx = document.querySelector('#trend-chart').getContext('2d');
                const gradient1 = ctx.createLinearGradient(0, 0, 0, 300);
                gradient1.addColorStop(0, 'rgba(0, 132, 255, 0.35)');
                gradient1.addColorStop(1, 'rgba(0, 123, 255, 0.01)');

                const gradient2 = ctx.createLinearGradient(0, 0, 0, 300);
                gradient2.addColorStop(0, 'rgba(220, 53, 69, 0.35)');
                gradient2.addColorStop(1, 'rgba(220, 53, 69, 0.01)');

                let tooltipTitles = [
                    @foreach($visitorsMap as $date => $value)
                        @if($range['unit'] == 'hour')
                            '{{ \Carbon\Carbon::createFromFormat('H', $date)->format(__('H:i')) }}',
                        @elseif($range['unit'] == 'day')
                            '{{ \Carbon\Carbon::parse($date)->format(__('Y-m-d')) }}',
                        @elseif($range['unit'] == 'month')
                            '{{ \Carbon\Carbon::parse($date)->format(__('Y-m')) }}',
                        @else
                            '{{ $date }}',
                        @endif
                    @endforeach
                ];

                const uniqueColor = '#3584ff';
                const pageViewsColor = '#dc3545';

                const lineOptions = {
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    hitRadius: 5,
                    pointHoverBorderWidth: 3,
                    lineTension: 0,
                }

                let trendChart = new Chart(ctx, {
                    type: 'line',

                    data: {
                        labels: [
                            @foreach($pageviewsMap as $date => $value)
                                @if($range['unit'] == 'hour')
                                    '{{ \Carbon\Carbon::createFromFormat('H', $date)->format(__('H:i')) }}',
                                @elseif($range['unit'] == 'day')
                                    '{{ __(':month :day', ['month' => mb_substr(__(\Carbon\Carbon::parse($date)->format('F')), 0, 3), 'day' => __(\Carbon\Carbon::parse($date)->format('j'))]) }}',
                                @elseif($range['unit'] == 'month')
                                    '{{ __(':year :month', ['year' => \Carbon\Carbon::parse($date)->format('Y'), 'month' => mb_substr(__(\Carbon\Carbon::parse($date)->format('F')), 0, 3)]) }}',
                                @else
                                    '{{ $date }}',
                                @endif
                            @endforeach
                        ],
                        datasets: [{
                            label: '{{ __('Visitors') }}',
                            data: [
                                @foreach($visitorsMap as $date => $value)
                                    {{ $value }},
                                @endforeach
                            ],
                            backgroundColor : gradient1,
                            borderColor: uniqueColor,
                            pointBorderColor: uniqueColor,
                            pointBackgroundColor: uniqueColor,
                            pointHoverBackgroundColor: '#addfff',
                            pointHoverBorderColor: uniqueColor,
                            ...lineOptions
                        }, {
                            label: '{{ __('Pageviews') }}',
                            data: [
                                @foreach($pageviewsMap as $date => $value)
                                    {{ $value }},
                                @endforeach
                            ],
                            backgroundColor : gradient2,
                            borderColor: pageViewsColor,
                            pointBorderColor: pageViewsColor,
                            pointBackgroundColor: pageViewsColor,
                            pointHoverBackgroundColor: '#ffc2c2',
                            pointHoverBorderColor: pageViewsColor,
                            ...lineOptions
                        }]
                    },
                    options: {
                        legend: {
                            rtl: {{ (__('lang_dir') == 'rtl' ? 'true' : 'false') }},
                            display: false,
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'round',
                            }
                        },
                        tooltips: {
                            rtl: {{ (__('lang_dir') == 'rtl' ? 'true' : 'false') }},
                            mode: 'index',
                            intersect: false,
                            reverse: true,
                            backgroundColor: '{{ (request()->cookie('dark_mode') == 1 ? '#FFF' : '#000') }}',

                            xPadding: 16,
                            yPadding: 16,

                            titleFontColor: '{{ (request()->cookie('dark_mode') == 1 ? '#000' : '#FFF') }}',
                            titleSpacing: 30,
                            titleFontSize: 16,
                            titleFontStyle: 'normal',
                            titleMarginBottom: 10,

                            bodyFontColor: '{{ (request()->cookie('dark_mode') == 1 ? '#000' : '#FFF') }}',
                            bodyFontSize: 14,
                            bodySpacing: 10,

                            footerMarginTop: 10,
                            footerFontStyle: 'normal',
                            footerFontSize: 12,

                            cornerRadius: 4,
                            caretSize: 7,

                            callbacks: {
                                label: function (tooltipItem, data) {
                                    return ' ' + data.datasets[tooltipItem.datasetIndex].label + ': ' + parseFloat(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]).format(0, 3, '{{ __(',') }}').toString();
                                },
                                title: function (tooltipItem) {
                                    return tooltipTitles[tooltipItem[0].index];
                                }
                            }
                        },
                        hover: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    lineWidth: 0,
                                    zeroLineWidth: 1,
                                    tickMarkLength: 0
                                },
                                display: true,
                                ticks: {
                                    maxTicksLimit: @if($range['unit'] == 'day') 12 @else 15 @endif,
                                    padding: 10,
                                }
                            }],
                            yAxes: [{
                                gridLines: {
                                    tickMarkLength: 0
                                },
                                display: true,
                                ticks: {
                                    beginAtZero: true,
                                    maxTicksLimit: 8,
                                    padding: 10,
                                    callback: function(value) {
                                        return commarize(value, 1000);
                                    }
                                }
                            }],
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Update the tooltip color
                document.querySelector('#dark-mode').addEventListener('click', function(e) {
                    e.preventDefault();

                    trendChart.options.tooltips.backgroundColor = (this.dataset.darkMode == 0 ? '#FFF' : '#000');
                    trendChart.options.tooltips.titleFontColor = (this.dataset.darkMode == 0 ? '#000' : '#FFF');
                    trendChart.options.tooltips.bodyFontColor = (this.dataset.darkMode == 0 ? '#000' : '#FFF');
                    trendChart.update();
                });
            });
        </script>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-6 my-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Pages') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($pages) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('URL') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Pageviews') }}
                                </div>
                            </div>
                        </div>

                        @foreach($pages as $page)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex text-truncate">
                                                <div class="text-truncate" dir="ltr">{{ $page->value }}</div> <a href="http://{{ $website->url . $page->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.open-new', ['class' => 'fill-current width-3 height-3'])</a>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">

                                            <div>
                                                {{ number_format($page->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress chart-progress w-100">
                                        <div class="progress-bar bg-pageview rounded" role="progressbar" style="width: {{ (($page->count / $totalPageviews) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($pages) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.pages', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-6 my-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Referrers') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($referrers) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Website') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($referrers as $referrer)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            @if($referrer->value)
                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                    <img src="https://icons.duckduckgo.com/ip3/{{ $referrer->value }}.ico" rel="noreferrer" class="width-4 height-4">
                                                </div>

                                                <div class="d-flex text-truncate">
                                                    <div class="text-truncate" dir="ltr">{{ $referrer->value }}</div> <a href="http://{{ $referrer->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.open-new', ['class' => 'fill-current width-3 height-3'])</a>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                    <img src="{{ asset('/images/icons/referrers/unknown.svg') }}" rel="noreferrer" class="width-4 height-4">
                                                </div>

                                                <div class="text-truncate">
                                                    {{ __('Direct, Email, SMS') }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($referrer->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress chart-progress w-100">
                                        <div class="progress-bar bg-visitor rounded" role="progressbar" style="width: {{ (($referrer->count / $totalReferrers) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($referrers) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.referrers', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-4 my-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Countries') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($countries) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($countries as $country)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"><img src="{{ asset('/images/icons/countries/'. formatFlag($country->value)) }}.svg" class="width-4 height-4"></div>
                                            <div class="text-truncate">
                                                @if(!empty(explode(':', $country->value)[1]))
                                                    <a href="{{ route('stats.cities', ['id' => $website->url, 'search' => explode(':', $country->value)[0].':', 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-body">{{ explode(':', $country->value)[1] }}</a>
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($country->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress chart-progress w-100">
                                        <div class="progress-bar bg-visitor rounded" role="progressbar" style="width: {{ (($country->count / $totalVisitors) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($countries) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.countries', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-4 my-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Browsers') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($browsers) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($browsers as $browser)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"><img src="{{ asset('/images/icons/browsers/'.formatBrowser($browser->value)) }}.svg" class="width-4 height-4"></div>
                                            <div class="text-truncate">
                                                @if($browser->value)
                                                    {{ $browser->value }}
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($browser->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress chart-progress w-100">
                                        <div class="progress-bar bg-visitor rounded" role="progressbar" style="width: {{ (($browser->count / $totalVisitors) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($browsers) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.browsers', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-4 my-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Operating systems') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($operatingSystems) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($operatingSystems as $operatingSystem)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"><img src="{{ asset('/images/icons/os/'.formatOperatingSystem($operatingSystem->value)) }}.svg" class="width-4 height-4"></div>
                                            <div class="text-truncate">
                                                @if($operatingSystem->value)
                                                    {{ $operatingSystem->value }}
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($operatingSystem->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress chart-progress w-100">
                                        <div class="progress-bar bg-visitor rounded" role="progressbar" style="width: {{ (($operatingSystem->count / $totalVisitors) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($operatingSystems) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.operating_systems', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-12 mt-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Events') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($events) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Completions') }}
                                </div>
                            </div>
                        </div>

                        @foreach($events as $event)
                            <div class="list-group-item px-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="text-truncate">
                                                {{ explode(':', $event->value)[0] }}
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div class="d-flex align-items-center justify-content-end {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                                @if(!empty(explode(':', $event->value)[1]) || !empty(explode(':', $event->value)[2]))
                                                    <span class="badge badge-secondary {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                        @if(!empty(explode(':', $event->value)[1]))
                                                            {{ number_format((explode(':', $event->value)[1] * $event->count), 2, __('.'), __(',')) }}
                                                        @endif

                                                        @if(!empty(explode(':', $event->value)[2]))
                                                            {{ explode(':', $event->value)[2] }}
                                                        @endif
                                                    </span>
                                                @endif

                                                {{ number_format($event->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($events) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.events', ['id' => $website->url, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>
</div>