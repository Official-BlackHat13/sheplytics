@section('site_title', formatTitle([$website->url, __('Pages'), config('settings.title')]))

<!-- Trending card -->
<div class="card border-0 shadow-sm p-0 mb-3">
    <div class="px-3">
        <div class="row">
            <!-- Title -->
            <div class="col-12 col-md-auto d-none d-xl-flex align-items-center border-bottom border-md-bottom-0 {{ (__('lang_dir') == 'rtl' ? 'border-md-left' : 'border-md-right') }}">
                <div class="px-2 py-4 d-flex">
                    <div class="d-flex position-relative text-primary width-10 height-10 align-items-center justify-content-center flex-shrink-0">
                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-35"></div>
                        @include('icons.page', ['class' => 'fill-current width-5 height-5'])
                    </div>
                </div>
            </div>

            <div class="col-12 col-md text-truncate">
                <div class="row">
                    <!-- Trending up -->
                    <div class="col-12 col-md-6 border-bottom border-md-bottom-0 {{ (__('lang_dir') == 'rtl' ? 'border-md-left' : 'border-md-right')  }}">
                        <div class="px-2 py-4">
                            <div class="row">
                                <div class="col">
                                    <div class="d-flex align-items-center text-truncate">
                                        @if(isset($first->value))
                                            <div class="d-flex flex-grow-1 font-weight-bold text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                @if($first->value)
                                                    <div class="text-truncate" dir="ltr">{{ $first->value }}</div> <a href="http://{{ $website->url . $first->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.open-new', ['class' => 'fill-current width-3 height-3'])</a>
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex-grow-1 font-weight-bold text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ __('No data') }}</div>
                                        @endif

                                        <div class="align-self-end">{{ (isset($first->count) ? number_format($first->count, 0, __('.'), __(',')) : '—') }}</div>
                                    </div>

                                    <div class="d-flex align-items-center text-truncate text-success">
                                        <div class="d-flex align-items-center justify-content-center width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">@include('icons.trending-up', ['class' => 'fill-current width-3 height-3'])</div>

                                        <div class="flex-grow-1 text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ mb_strtolower(__('Most popular')) }}</div>

                                        <div>{{ (isset($first->count) ? number_format((($first->count / $total->count) * 100), 1, __('.'), __(',')).'%' : '—') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trending down -->
                    <div class="col-12 col-md-6">
                        <div class="px-2 py-4">
                            <div class="row">
                                <div class="col">
                                    <div class="d-flex align-items-center text-truncate">
                                        @if(isset($last->value))
                                            <div class="d-flex flex-grow-1 font-weight-bold text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                @if($last->value)
                                                    <div class="text-truncate" dir="ltr">{{ $last->value }}</div> <a href="http://{{ $website->url . $last->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.open-new', ['class' => 'fill-current width-3 height-3'])</a>
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex-grow-1 font-weight-bold text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ __('No data') }}</div>
                                        @endif

                                        <div class="align-self-end">{{ (isset($last->count) ? number_format($last->count, 0, __('.'), __(',')) : '—') }}</div>
                                    </div>

                                    <div class="d-flex align-items-center text-truncate text-danger">
                                        <div class="d-flex align-items-center justify-content-center width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">@include('icons.trending-down', ['class' => 'fill-current width-3 height-3'])</div>

                                        <div class="flex-grow-1 text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ mb_strtolower(__('Least popular')) }}</div>

                                        <div>{{ (isset($last->count) ? number_format((($last->count / $total->count) * 100), 1, __('.'), __(',')).'%' : '—') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column">
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Pages') }}</div></div>
                <div class="col-12 col-md-auto">
                    <div class="form-row">
                        @include('stats.filters')
                    </div>
                </div>
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

                    <div class="list-group-item px-0 small text-muted">
                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex text-truncate align-items-center">
                                    <div class="text-truncate">
                                        {{ __('Total') }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                    <span>{{ number_format($total->count, 0, __('.'), __(',')) }}</span>

                                    <div class="width-16 text-muted {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                        {{ number_format((($total->count / $total->count) * 100), 1, __('.'), __(',')) }}%
                                    </div>
                                </div>
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
                                        <span>{{ number_format($page->count, 0, __('.'), __(',')) }}</span>

                                        <div class="width-16 text-muted {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                            {{ number_format((($page->count / $total->count) * 100), 1, __('.'), __(',')) }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="progress chart-progress w-100">
                                    <div class="progress-bar bg-pageview rounded" role="progressbar" style="width: {{ (($page->count / $total->count) * 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3 align-items-center">
                        <div class="row">
                            <div class="col">
                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $pages->firstItem(), 'to' => $pages->lastItem(), 'total' => $pages->total()]) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                {{ $pages->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>