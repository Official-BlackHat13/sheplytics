@section('site_title', formatTitle([__('Edit'), __('Language'), config('settings.title')]))

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('admin.dashboard'), 'title' => __('Admin')],
    ['url' => route('admin.languages'), 'title' => __('Languages')],
    ['title' => __('Edit')],
]])

<h2 class="mb-3 d-inline-block">{{ __('Edit') }}</h2>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header align-items-center">
        <div class="row">
            <div class="col">
                <div class="font-weight-medium py-1">
                    {{ __('Languages') }}
                </div>
            </div>
            <div class="col-auto">
                <div class="form-row">
                    <div class="col">
                        @include('admin.languages.partials.menu')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">

        @include('shared.message')

        <div class="row">
            <div class="col-12 col-lg-6 mb-3">
                <div class="text-muted">{{ __('Name') }}</div>
                <div>{{ $language->name }}</div>
            </div>
            <div class="col-12 col-lg-6 mb-3">
                <div class="text-muted">{{ __('Code') }}</div>
                <div>{{ $language->code }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <form action="{{ route('admin.languages.edit', $language->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="default" id="i-default" @if($language->default) checked disabled @endif>
                            <label class="custom-control-label" for="i-default">{{ __('Default') }}</label>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>