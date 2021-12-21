@section('site_title', formatTitle([__('Edit'), __('Website'), config('settings.title')]))

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => isset($admin) ? route('admin.dashboard') : route('dashboard'), 'title' => isset($admin) ? __('Admin') : __('Home')],
    ['title' => __('Edit')],
]])

<div class="d-flex">
    <h2 class="mb-3 text-break">{{ __('Edit') }}</h2>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header align-items-center">
        <div class="row">
            <div class="col">
                <div class="font-weight-medium py-1">{{ __('Website') }}</div>
            </div>
            <div class="col-auto">
                <div class="form-row flex-nowrap">
                    <div class="col">
                        <a href="{{ route('stats.overview', ['id' => $website->url, 'from' => $range['from'] ?? null, 'to' => $range['to'] ?? null]) }}" class="btn btn-sm text-primary" data-enable="tooltip" title="{{ __('Stats') }}">@include('icons.stats', ['class' => 'fill-current width-4 height-4'])&#8203;</a>
                    </div>

                    <div class="col">
                        <a href="#" class="btn btn-sm text-primary d-flex align-items-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.horizontal-menu', ['class' => 'fill-current width-4 height-4'])&#8203;</a>

                        @include('shared.dropdowns.website', ['class' => 'text-secondary', 'options' => ['stats' => true, 'open' => true, 'delete' => true]])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        @include('shared.message')

        <form action="{{ isset($admin) ? route('admin.websites.edit', $website->id) : route('websites.edit', $website->id) }}" method="post" enctype="multipart/form-data">
            @csrf

            @if(isset($admin))
                <input type="hidden" name="user_id" value="{{ $website->user->id }}">
            @endif

            <div class="form-group">
                <label for="i-url">{{ __('URL') }}</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><img src="https://icons.duckduckgo.com/ip3/{{ $website->url }}.ico" rel="noreferrer" class="width-4 height-4"></span>
                    </div>
                    <input type="text" dir="ltr" name="url" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}" id="i-url" value="{{ $website->url }}" placeholder="https://example.com" disabled>
                    @if ($errors->has('url'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('url') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <hr>

            <div class="form-group">
                <label>{{ __('Privacy') }}</label>
                <div class="form-group mb-0">
                    <div class="form-row">
                        <div class="col-12 col-lg-4">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy1" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="1" @if($website->privacy == 1 && old('privacy') == null || old('privacy') == 1) checked @endif>
                                <label class="custom-control-label cursor-pointer w-100" for="i-privacy1">
                                    <div>{{ __('Private') }}</div>
                                    <div class="small text-muted">{{ __('Stats accessible only by you.') }}</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy0" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="0" @if($website->privacy == 0 && old('privacy') == null || old('privacy') == 0 && old('privacy') != null) checked @endif>
                                <label class="custom-control-label cursor-pointer w-100" for="i-privacy0">
                                    <div>{{ __('Public') }}</div>
                                    <div class="small text-muted">{{ __('Stats accessible by anyone.') }}</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy2" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="2" @if($website->privacy == 2 && old('privacy') == null || old('privacy') == 2) checked @endif>
                                <label class="custom-control-label cursor-pointer w-100" for="i-privacy2">
                                    <div>{{ __('Password') }}</div>
                                    <div class="small text-muted">{{ __('Stats accessible by password.') }}</div>
                                </label>

                                <div id="input-password" class="{{ (((old('privacy') == 2) || (old('privacy') == null && $website->privacy == 2)) ? '' : 'd-none')}}">
                                    <div class="input-group mt-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text cursor-pointer" data-enable="tooltip" data-title="{{ __('Show password') }}" data-password="i-password" data-password-show="{{ __('Show password') }}" data-password-hide="{{ __('Hide password') }}">@include('icons.security', ['class' => 'width-4 height-4 fill-current text-muted'])</div>
                                        </div>
                                        <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" value="{{ isset($admin) ? '' : (old('password') ?? $website->password) }}" autocomplete="new-password" @if(isset($admin)) disabled @endif>
                                    </div>
                                    @if ($errors->has('password'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($errors->has('privacy'))
                        <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('privacy') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <hr>

            <div class="form-group">
                <label>{{ __('Notifications') }}</label>
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="email" value="0">
                    <input type="checkbox" name="email" value="1" class="custom-control-input {{ $errors->has('email') ? ' is-invalid' : '' }}" id="customCheckbox2" @if($website->email && old('email') == null || old('email')) checked @endif>
                    <label class="custom-control-label cursor-pointer" for="customCheckbox2">
                        <div>{{ __('Email') }}</div>
                        <div class="small text-muted">{{ __('Periodic email reports.') }}</div>
                        @if ($errors->has('email'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </label>
                </div>
            </div>

            <hr>

            <div class="form-row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="i-exclude-ips">{{ __('Exclude IPs') }}</label>
                        <textarea name="exclude_ips" id="i-exclude-ips" class="form-control{{ $errors->has('exclude_ips') ? ' is-invalid' : '' }}" placeholder="{{ __('One per line.') }}">{{ old('exclude_ips') ?? $website->exclude_ips }}</textarea>
                        @if ($errors->has('exclude_ips'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('exclude_ips') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="i-exclude-params">{{ __('Exclude URL query parameters') }}</label>
                        <textarea name="exclude_params" id="i-exclude-params" class="form-control{{ $errors->has('exclude_params') ? ' is-invalid' : '' }}" placeholder="{{ __('One per line.') }}">{{ old('exclude_params') ?? $website->exclude_params }}</textarea>
                        @if ($errors->has('exclude_params'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('exclude_params') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="exclude_bots" value="0">
                    <input type="checkbox" name="exclude_bots" value="1" class="custom-control-input {{ $errors->has('exclude_bots') ? ' is-invalid' : '' }}" id="customCheckbox3" @if($website->exclude_bots && old('exclude_bots') == null || old('exclude_bots')) checked @endif>
                    <label class="custom-control-label cursor-pointer" for="customCheckbox3">
                        <div>{{ __('Exclude bots') }}</div>
                        <div class="small text-muted">{{ __('Exclude common bots from being tracked.') }}</div>
                        @if ($errors->has('exclude_bots'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('exclude_bots') }}</strong>
                            </span>
                        @endif
                    </label>
                </div>
            </div>

            <hr>

            <div class="form-group">
                @include('shared.tracking-code')
            </div>

            <div class="row mt-3">
                <div class="col">
                    <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#delete-website-modal" data-action="{{ isset($admin) ? route('admin.websites.destroy', $website->id) : route('websites.destroy', $website->id) }}" data-text="{{ __('Are you sure you want to delete :name?', ['name' => $website->url]) }}">{{ __('Delete') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(isset($admin))
    @if(isset($website->user))
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <div class="row"><div class="col"><div class="font-weight-medium py-1">{{ __('User') }}</div></div><div class="col-auto"><a href="{{ route('admin.users.edit', $website->user->id) }}" class="btn btn-outline-primary btn-sm">{{ __('Edit') }}</a></div></div>
            </div>
            <div class="card-body mb-n3">
                <div class="row">
                    <div class="col-12 col-lg-6 mb-3">
                        <div class="text-muted">{{ __('Name') }}</div>
                        <div>{{ $website->user->name }}</div>
                    </div>

                    <div class="col-12 col-lg-6 mb-3">
                        <div class="text-muted">{{ __('Email') }}</div>
                        <div>{{ $website->user->email }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

@include('shared.modals.delete-website', ['admin' => isset($admin) ? true : false])