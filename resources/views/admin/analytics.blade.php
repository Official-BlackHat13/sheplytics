@section('site_title', formatTitle([__('Analytics'), config('settings.title')]))

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('admin.dashboard'), 'title' => __('Admin')],
    ['title' => __('Analytics')],
]])

<h2 class="mb-3 d-inline-block">{{ __('Analytics') }}</h2>

<div class="card border-0 shadow-sm">
    <div class="card-header"><div class="font-weight-medium py-1">{{ __('Analytics') }}</div></div>
    <div class="card-body">

        @include('shared.message')

        <form action="{{ route('admin.analytics') }}" method="post" enctype="multipart/form-data">

            @csrf

            <div class="form-group">
                <label for="i-demo-url">{{ __(':name URL', ['name' => __('Demo')]) }}</label>
                <input type="text" dir="ltr" name="demo_url" id="i-demo-url" class="form-control{{ $errors->has('demo_url') ? ' is-invalid' : '' }}" value="{{ old('settings.demo_url') ?? config('settings.demo_url') }}">
                @if ($errors->has('demo_url'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('demo_url') }}</strong>
                    </span>
                @endif
            </div>

            <div class="form-group">
                <label for="i-cdn-url" class="d-flex align-items-center">{{ __(':name URL', ['name' => __('CDN')]) }} <span data-enable="tooltip" title="{{ __('The CDN URL where the :name file is hosted.', ['name' => 'script.js']) }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.info', ['class' => 'fill-current text-muted width-4 height-4'])</span></label>
                <input type="text" dir="ltr" name="cdn_url" id="i-cdn-url" class="form-control{{ $errors->has('cdn_url') ? ' is-invalid' : '' }}" value="{{ old('settings.cdn_url') ?? config('settings.cdn_url') }}">
                @if ($errors->has('cdn_url'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('cdn_url') }}</strong>
                    </span>
                @endif
            </div>

            <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </form>

    </div>
</div>